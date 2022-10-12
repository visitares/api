<?php

namespace Visitares\JobQueue\Workers;

use PHPMailer;
use Visitares\Entity\{ Post, Timeline };
use Visitares\JobQueue\JobStatus;
use Visitares\JobQueue\Queues\SendMailQueue;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Storage\Factory\PDOFactory;

class NotifyPostSubsWorker{

	/**
	 * @var \PDO
	 */
	private $pdo;

	/**
	 * @var InstanceStorageFacade
	 */
	private $instanceStorage;

	/**
	 * @var PDOFactory
	 */
	private $pdoFactory;

	/**
	 * @var SendMailQueue
	 */
	private $sendMailQueue;

	/**
	 * @var Repository
	 */
	private $timelines;

	/**
	 * @var Repository
	 */
	private $posts;

	/**
	 * @param \PDO $pdo
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param PDOFactory $pdoFactory
	 * @param SendMailQueue $sendMailQueue
	 */
	public function __construct(
		\PDO $pdo,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $instanceStorage,
		PDOFactory $pdoFactory,
		SendMailQueue $sendMailQueue
	){
		$this->pdo = $pdo;
		$this->instanceStorage = $instanceStorage;
		$this->pdoFactory = $pdoFactory;
		$this->sendMailQueue = $sendMailQueue;
		$this->posts = $systemStorage->getEntityManager()->getRepository(Post::class);
		$this->timelines = $systemStorage->getEntityManager()->getRepository(Timeline::class);
	}

	/**
	 * @param \stdClass $job
	 * @return int
	 */
	public function run(\stdClass $job){

		$postId = $job->payload->post->id;

		$post = $this->posts->findOneById($postId);
		$timeline = $post->getTimeline();
		$subscriptions = $this->getSubscriptions($post);

		if(!$subscriptions){
			return JobStatus::DONE;
		}

		file_put_contents(APP_DIR_LOG . '/workers/notify-post-subs.log', sprintf('[%s] created "send-mail" jobs for post: %s', date('Y-m-d'), $postId) . PHP_EOL, FILE_APPEND);

		$skip = [];
		foreach($subscriptions as $subscription){

			if(isset($skip[$subscription->user_email])){
				continue;
			}
			$skip[$subscription->user_email] = true;

			if(!trim($subscription->user_email)){
				continue;
			}

			$this->addNotification($subscription, [
				'timeline' => [
					'name' => $timeline->getName(),
				],
				'author' => [
					'fullname' => implode(' ', [
						$post->getUser()->getFirstname(),
						$post->getUser()->getLastname(),
					]),
				],
				'receiver' => [
					'fullname' => implode(' ', [
						$subscription->user_firstname,
						$subscription->user_lastname,
					]),
				],
				'post' => [
					'title' => $post->getTitle(),
					'url' => APP_URL . '/#/timeline/posts/' . $post->getId() . '?domain=' . $subscription->user_domain,
				],
				'sendDeeplink' => $post->getUser()->getInstance()->getAppSendDeeplinks(),
			]);

			file_put_contents(APP_DIR_LOG . '/workers/notify-post-subs.log', sprintf('[%s]   -> %s', date('Y-m-d'), $subscription->user_email) . PHP_EOL, FILE_APPEND);
		}

		return JobStatus::DONE;
	}

	/**
	 * @param Post $post
	 * @return array
	 */
	private function getSubscriptions(Post $post){
		switch($post->getPublished()){
			case Post::PUBLISHED_PUBLIC:
				return []; // $this->getSubscriberForPublic($post);
			case Post::PUBLISHED_INSTANCE:
				return []; // $this->getSubscriberForInstance($post);
			case Post::PUBLISHED_GROUPS:
				return $this->getSubscriptionsForGroups($post);
			case Post::PUBLISHED_TIMELINE:
				return []; // $this->getSubscriberForTimeline($post);
			case Post::PUBLISHED_METAGROUPS:
				return $this->getSubscriptionsForMetaGroups($post);
			default:
				return [];
		}
	}

	/**
	 * @param Post $post
	 * @return array
	 */
	private function getSubscriptionsForGroups(Post $post){
		$groups = $this->pdo
			->query(
				sprintf('
					SELECT
						g.instance_id,
						g.group_id,
						i.token,
						i.domain
					FROM
						post_group pg
					LEFT JOIN
						groupcache g ON g.id = pg.group_id
					LEFT JOIN
						instance i ON i.id = g.instance_id
					WHERE
						pg.post_id = %d
				', $post->getId())
			)
			->fetchAll(\PDO::FETCH_OBJ);

		return array_reduce($groups, function($subscriptions, $group){
			$pdo = $this->pdoFactory->fromToken($group->token);
			$users = $pdo
				->query(
					sprintf('
						SELECT
							u.firstname AS user_firstname,
							u.lastname AS user_lastname,
							u.email AS user_email
						FROM
							user u
						LEFT JOIN
							group_user gu ON gu.user_id = u.id
						WHERE
							u.email IS NOT NULL AND	gu.group_id = %d AND gu.sub = TRUE
						GROUP BY
							u.id
					', $group->group_id)
				)
				->fetchAll(\PDO::FETCH_OBJ);
			return array_merge($subscriptions, array_map(function(\stdClass $user) use($group){
				$user->user_domain = $group->domain;
				return $user;
			}, $users));
		}, []);
	}

	/**
	 * @param Post $post
	 * @return array
	 */
	private function getSubscriptionsForMetaGroups(Post $post){
		$sql = '
			SELECT
				u.firstname AS user_firstname,
				u.lastname AS user_lastname,
				u.email AS user_email,
				ui.domain AS user_domain
			
			FROM
				post p
			
			LEFT JOIN
				timeline t ON t.id = p.timeline_id
			
			LEFT JOIN
				instance i ON i.timeline_id = t.id
			
			LEFT JOIN
				master m ON m.id = i.master_id
			
			LEFT JOIN
				master_metagroup mm ON mm.master_id = m.id
			
			LEFT JOIN
				usercache_metagroup um ON um.metaGroup_id = mm.metaGroup_id
			
			LEFT JOIN
				usercache u ON u.id = um.user_id
			
			LEFT JOIN
				instance ui ON ui.id = u.instance_id

			WHERE
				p.id = :post AND u.email IS NOT NULL
			
			GROUP BY
				um.user_id
		';
		$query = $this->pdo->prepare($sql);
		$query->execute([
			':post' => $post->getId(),
		]);
		return $query->fetchAll(\PDO::FETCH_OBJ);
	}

	/**
	 * @param stdClass $row
	 * @param array $data
	 * @return void
	 */
	private function addNotification($row, $data){

		if(!$row->user_email){
			return;
		}

		$this->sendMailQueue->add(
			// from
			[ 'noreply@visitares.com', 'VISITARES' ],

			// to
			[
				[ $row->user_email, implode(' ', [$row->user_firstname, $row->user_lastname]) ],
			],

			// subject
			'🔔 Es wurde ein neuer Beitrag veröffentlicht',

			// body
			[
				// html
				'html/mails/notify-post-sub-html.html',

				// plain
				'html/mails/notify-post-sub-plain.html',

				// data
				$data
			],

			// attachments
			[]
		);
	}

}
