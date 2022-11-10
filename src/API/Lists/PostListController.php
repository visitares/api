<?php

namespace Visitares\API\Lists;

use stdClass;
use Doctrine\ORM\EntityManager;
use Visitares\Entity\User;
use Visitares\Entity\CachedUser;
use Visitares\Entity\CachedGroup;
use Visitares\Entity\PostGroup;
use Visitares\Entity\PostMedia;
use Visitares\Entity\Timeline;
use Visitares\Entity\Instance;
use Visitares\Entity\Comment;
use Visitares\Entity\Like;
use Visitares\Entity\MetaGroup;
use Visitares\Entity\Post;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Service\User\UserMetaGroupService;

class PostListController{

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $instanceStorage = null;

	/**
	 * @var EntityManager
	 */
	protected $em = null;

	/**
	 * @var array
	 */
	private $context = null;

	/**
	 * @var User
	 */
	private $user = null;

	/**
	 * @var CachedUser
	 */
	private $cachedUser = null;

	/**
	 * @param SystemStorageFacade   $storage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param UserMetaGroupService  $userMetaGroupsService
	 */
	public function __construct(
		SystemStorageFacade $storage,
		InstanceStorageFacade $instanceStorage,
		UserMetaGroupService $userMetaGroupsService
	){
		$this->storage = $storage;
		$this->instanceStorage = $instanceStorage;
		$this->em = $storage->getEntityManager();
		$this->timelines = $this->em->getRepository(Timeline::class);
		$this->instances = $this->em->getRepository(Instance::class);
		$this->likes = $this->em->getRepository(Like::class);
		$this->cachedGroups = $this->em->getRepository(CachedGroup::class);
		$this->metaGroups = $this->em->getRepository(MetaGroup::class);
		$this->userMetaGroupsService = $userMetaGroupsService;
	}

	/**
	 * @param  array   $context
	 * @param  array   $filter
	 * @param  array   $sort
	 * @param  integer $language
	 * @param  integer $offset
	 * @param  integer $limit
	 * @return array
	 */
	public function get(array $context, array $filter, array $orderBy, $offset=0, $limit=100){
		session_write_close();

		$this->context = $context;

		$instance = $this->instances->findOneById($context['instance']);
		$timeline = $instance->getTimeline();
		$master = $instance->getMaster();

		if(!$instance || !$timeline){
			return [
				'total' => 0,
				'offset' => (int)$offset,
				'limit' => (int)$limit,
				'rows' => []
			];
		}

		// get user
		$this->instance = $instance;
		$this->instanceStorage->setToken($instance->getToken());
		$this->user = $this->instanceStorage->user->findById($context['user']);
		$this->cachedUser = $this->storage->getEntityManager()->getRepository(CachedUser::class)->findOneBy([
			'instance' => $instance,
			'userId' => $this->user->getId()
		]);

		// @todo: get metagroup ids
		$metaGroups = $this->userMetaGroupsService->getMetaGroupsByUser($instance, $this->user);
		if($metaGroups){
			$metaGroupIds = array_map(function($metaGroup){
				return $metaGroup->getId();
			}, $metaGroups);
		} else{
			$metaGroupIds = [-1];
		}

		// get user groups
		$groups = $this->user->getGroups();
		$groupIds = [];
		foreach($groups as $group){
			$groupIds[] = $group->getId();
		}
		if(!$groupIds){
			$cachedGroupIds = [-1];
		} else{
			$cachedGroups = $this->cachedGroups->findBy([
				'instance' => $instance,
				'groupId' => $groupIds
			]);
			$cachedGroupIds = array_map(function(CachedGroup $cachedGroup){
				return $cachedGroup->getId();
			}, $cachedGroups);
			if(!$cachedGroupIds){
				$cachedGroupIds = [-1];
			}
		}

		// create custom where
		$where = [];

		if(isset($filter['search'])){
			$where[] = str_replace('{search}', $this->em->getConnection()->quote('%' . $filter['search'] . '%'), '(p.title LIKE {search} OR p.content LIKE {search})');
		}

		if(isset($filter['group'])){
			$where[] = sprintf('cachedgroup.group_id = %d', $filter['group']);
		}

		if(isset($filter['metaGroup'])){
			$where[] = sprintf('pmg.metaGroup_id = %d', $filter['metaGroup']);
		}

		if($where = implode(' AND ', $where)){
			$where = ' AND ' . $where;
		}

		// create query
		$sql = file_get_contents(APP_DIR_ROOT . '/res/sql/timeline.sql');
		$sql = str_replace([
			':groups',
			':offset',
			':limit',

			':user',
			':master',
			':instance',
			':timeline',
			':metaGroups',

			'{where}'
		], [
			implode(', ', $cachedGroupIds),
			(int)$offset,
			(int)$limit,

			$context['user'],
			$master ? $master->getId() : -1,
			$instance->getId(),
			$timeline->getId(),
			implode(', ', $metaGroupIds),

			$where
		], $sql);

		$query = $this->em->getConnection()->prepare($sql);
		$res = $query->executeQuery();
		$rows = (array)$res->fetchAllAssociative();
		$rows = array_map(fn($arr) => (object)$arr, $rows);

		return [
			'total' => $this->getTotal($sql),
			'offset' => (int)$offset,
			'limit' => (int)$limit,
			'rows' => array_map([$this, 'serve'], $rows)
		];
	}

	/**
	 * @param  string $sql
	 * @return int
	 */
	protected function getTotal($sql){
		$sql = preg_replace('/LIMIT \d+, \d+/m', '', $sql);
		$sql = ' SELECT COUNT(*) AS total FROM (' . $sql . ') query';
		$query = $this->em->getConnection()->prepare($sql);
		$res = $query->executeQuery();
		$rows = (array)$res->fetchAllAssociative();
		$rows = array_map(fn($arr) => (object)$arr, $rows);
		return (int)$rows[0]->total;
	}

	/**
	 * @param  stdClass $row
	 * @return stdClass
	 */
	protected function serve(stdClass $row){
		foreach(['id', 'published', 'instanceId'] as $prop){
			$row->{$prop} = (int)$row->{$prop};
		}

		foreach(['creationDate', 'modificationDate'] as $prop){
			$row->{$prop} = $row->{$prop} ? date('c', strtotime($row->{$prop})) : null;
		}

		$user = $this->em->getRepository(CachedUser::class)->findOneBy([
			'id' => $row->user_id
		]);

		$row->user = [
			'instance' => $row->userInstanceToken,
			'userId' => $user->getUserId(),
			'salutation' => $user->getSalutation(),
			'title' => $user->getTitle(),
			'firstname' => $user->getFirstname(),
			'lastname' => $user->getLastname(),
			'company' => $user->getCompany(),
			'department' => $user->getDepartment()
		];

		$postGroups = $this->em->getRepository(PostGroup::class)->findBy(['post' => $row->id]);
		$row->groups = [];
		foreach($postGroups as $postGroup){
			$row->groups[] = [
				'instanceId' => $postGroup->getGroup()->getInstance()->getId(),
				'groupId' => $postGroup->getGroup()->getGroupId(),
				'name' => $postGroup->getGroup()->getName()
			];
		}

		$postMedia = $this->em->getRepository(PostMedia::class)->findBy(['post' => $row->id]);
		$row->media = [];
		foreach($postMedia as $media){
			$row->media[] = [
				'id' => $media->getId(),
				'type' => $media->getTypeLabel(),
				'mime' => $media->getMime(),
				'label' => $media->getOriginalFilename(),
				'filename' => $media->getFilename()
			];
		}

		$row->commentsCount = $this->getCommentsCount($row->id);
		$row->likes = $this->getLikesCount($row->id);

		$row->liked = false;
		if($likes = $this->likes->findBy(['post' => $row->id, 'user' => $this->cachedUser])){
			$row->liked = $likes ? true : false;
		}
		
		$row->own = false;
		if($user->getUserId() === $this->context['user'] && $user->getInstance()->getId() === $this->context['instance']){
			$row->own = true;
		}

		$row->isAdmin = false;
		if($this->user->getRole() === User::ROLE_ADMIN){
			if($row->instanceId === $this->instance->getId()){
				$row->isAdmin = true;
			}
			if($this->user->getInstances() && in_array($row->instanceId, array_map('intval', explode(',', $this->user->getInstances())))){
				$row->isAdmin = true;
			}
		} elseif($this->user->getRole() === User::ROLE_SUPERUSER){
			$row->isAdmin = true;
		}

		$row->timeline = substr(hash('sha256', $row->timeline_id), 0, 10);
		$row->instance = $row->instanceId;

		foreach(['user_id', 'timeline_id', 'instanceId'] as $prop){
			unset($row->{$prop});
		}

		return $row;
	}

	/**
	 * @param  int $id
	 * @return int
	 */
	protected function getCommentsCount($id){
		$queryBuilder = $this->em->createQueryBuilder('c');
		return (int)$queryBuilder
			->select('count(c)')
			->from(Comment::class, 'c')
			->join('c.post', 'p')
			->where('p.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * @param  int $id
	 * @return int
	 */
	protected function getLikesCount($id){
		$queryBuilder = $this->em->createQueryBuilder('l');
		return (int)$queryBuilder
			->select('count(l)')
			->from(Like::class, 'l')
			->join('l.post', 'p')
			->where('p.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getSingleScalarResult();
	}

}
