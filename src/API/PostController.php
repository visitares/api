<?php

namespace Visitares\API;

use Visitares\Entity\Instance;
use Visitares\Entity\Timeline;
use Visitares\Entity\Post;
use Visitares\Entity\PostMedia;
use Visitares\Entity\MetaGroup;
use Visitares\Entity\Comment;
use Visitares\Entity\Like;
use Visitares\Entity\CachedUser;
use Visitares\Service\Timeline\PublishPostService;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\JobQueue\Queues\NotifyPostSubsQueue;

class PostController{

	private $instanceStorage = null;
	private $em = null;

	private $instances = null;
	private $posts = null;
	private $metaGroups = null;
	private $media = null;
	private $cachedUsers = null;

	private $publishPostService = null;

	public function __construct(
		SystemStorageFacade $storage,
		InstanceStorageFacade $instanceStorage,
		PublishPostService $publishPostService,
		NotifyPostSubsQueue $notifyPostSubsQueue
	){
		$this->instanceStorage = $instanceStorage;
		$this->em = $storage->getEntityManager();
		$this->instances = $this->em->getRepository(Instance::class);
		$this->posts = $this->em->getRepository(Post::class);
		$this->media = $this->em->getRepository(PostMedia::class);
		$this->metaGroups = $this->em->getRepository(MetaGroup::class);
		$this->cachedUsers = $this->em->getRepository(CachedUser::class);
		$this->publishPostService = $publishPostService;
		$this->notifyPostSubsQueue = $notifyPostSubsQueue;
	}

	/**
	 * @param  Post $post
	 * @return array
	 */
	protected function serve(Post $post){

		$array = $post->toArray();

		$array['timeline'] = substr(hash('sha256', $post->getTimeline()->getId()), 0, 10);

		$array['user'] = [
			'salutation' => $post->getUser()->getSalutation(),
			'title' => $post->getUser()->getTitle(),
			'firstname' => $post->getUser()->getFirstname(),
			'lastname' => $post->getUser()->getLastname(),
			'company' => $post->getUser()->getCompany(),
			'department' => $post->getUser()->getDepartment()
		];

		$array['groups'] = array_map(function($group){
			return[
				'id' => $group->getGroupId(),
				'name' => $group->getName()
			];
		}, $this->publishPostService->getGroupsByPost($post));

		$array['media'] = array_map(function($media){
			return[
				'id' => $media->getId(),
				'mime' => $media->getMime(),
				'label' => $media->getOriginalFilename(),
				'filename' => $media->getFilename(),
				'filesize' => $media->getFilesize()
			];
		}, $this->media->findBy(['post' => $post]));

		$array['commentsCount'] = $this->getCommentsCount($post);
		$array['likes'] = $this->getLikesCount($post);

		return $array;
	}

	/**
	 * @param  Post $post
	 * @return int
	 */
	protected function getCommentsCount(Post $post){
		$queryBuilder = $this->em->createQueryBuilder('c');
		return (int)$queryBuilder
			->select('count(c)')
			->from(Comment::class, 'c')
			->join('c.post', 'p')
			->where('p.id = :id')
			->setParameter('id', $post->getId())
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * @param  Post $post
	 * @return int
	 */
	protected function getLikesCount(Post $post){
		$queryBuilder = $this->em->createQueryBuilder('l');
		return (int)$queryBuilder
			->select('count(l)')
			->from(Like::class, 'l')
			->join('l.post', 'p')
			->where('p.id = :id')
			->setParameter('id', $post->getId())
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * @param  integer $id
	 * @return Post
	 */
	public function getById($id){
		if(!$post = $this->posts->findOneById($id)){
			return null;
		}
		return $this->serve($post);
	}

	/**
	 * @param  array $data
	 * @return Post
	 */
	public function store(array $data){
		$instance = $this->instances->findOneById($data['instance']);
		$timeline = $instance->getTimeline();

		$user = $this->cachedUsers->findOneBy([
			'instance' => $instance,
			'userId' => $data['user']
		]);

		$post = new Post;
		$post->setTimeline($timeline);
		$post->setUser($user);
		$post->setTitle($data['title']);
		$post->setContent($data['content']);
		$post->setPublished($data['published']);

		$this->em->persist($post);
		$this->em->flush();

		$this->publishPostService->update($post, $instance, isset($data['groups']) ? $data['groups'] : []);

		if(isset($data['notify'])){
			$this->notifyPostSubsQueue->add($post->getId(), 'published');
		}

		return $this->serve($post);
	}

	/**
	 * @param  integer $id
	 * @param  array   $data
	 * @return Post
	 */
	public function update($id, array $data){
		if(!$post = $this->posts->findOneById($id)){
			return null;
		}

		$instance = $this->instances->findOneById($data['instance']);

		$post->setTitle($data['title']);
		$post->setContent($data['content']);
		$post->setPublished($data['published']);

		$this->em->persist($post);
		$this->em->flush();

		$this->publishPostService->update($post, $instance, isset($data['groups']) ? $data['groups'] : []);

		if(isset($data['notify'])){
			$this->notifyPostSubsQueue->add($post->getId(), 'changed');
		}

		return $this->serve($post);
	}

	public function remove($id){
		if(!$post = $this->posts->findOneById($id)){
			return false;
		}
		$this->em->remove($post);
		$this->em->flush();
		return true;
	}

}
