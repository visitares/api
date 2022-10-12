<?php

namespace Visitares\Service\Timeline;

use Doctrine\ORM\Query\ResultSetMapping;
use Visitares\Entity\Instance;
use Visitares\Entity\Post;
use Visitares\Entity\PostGroup;
use Visitares\Entity\PostMetaGroup;
use Visitares\Entity\CachedGroup;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Service\User\UserMetaGroupService;

class PublishPostService{

	/**
	 * @var EntityManager
	 */
	private $em = null;

	/**
	 * @var Repository
	 */
	private $postGroups = null;

	/**
	 * @var PostMetaGroup
	 */
	private $postMetaGroups = null;

	/**
	 * @var Repository
	 */
	private $cachedGroups = null;

	/**
	 * @var UserMetaGroupService
	 */
	private $userMetaGroupService = null;

	/**
	 * @param SystemStorageFacade  $storage
	 * @param UserMetaGroupService $userMetaGroupService
	 */
	public function __construct(
		SystemStorageFacade $storage,
		UserMetaGroupService $userMetaGroupService
	){
		$this->em = $storage->getEntityManager();
		$this->postGroups = $this->em->getRepository(PostGroup::class);
		$this->postMetaGroups = $this->em->getRepository(PostMetaGroup::class);
		$this->cachedGroups = $this->em->getRepository(CachedGroup::class);
		$this->userMetaGroupService = $userMetaGroupService;
	}

	/**
	 * @param  Post $post
	 * @return array
	 */
	public function getGroupsByPost(Post $post){
		return array_map(function($postGroup){
			return $postGroup->getGroup();
		}, $this->postGroups->findBy(['post' => $post]));
	}

	/**
	 * @param  Post  $post
	 * @param  array $groups
	 * @return void
	 */
	public function update(Post $post, Instance $instance, array $groups = []){
		$this->syncPostGroups($post, $instance, $post->getPublished() === Post::PUBLISHED_GROUPS ? $groups : []);

		if($post->getPublished() === Post::PUBLISHED_METAGROUPS){
			$metaGroups = $this->userMetaGroupService->getMetaGroupsByCachedUser($post->getUser());
		}
		$this->syncPostMetaGroups($post, $instance, $post->getPublished() === Post::PUBLISHED_METAGROUPS ? $metaGroups : []);
	}

	/**
	 * @param  Post  $post
	 * @param  array $groupIds
	 * @return void
	 */
	protected function syncPostGroups(Post $post, Instance $instance, array $groupIds){
		$existing = $this->removePostGroups($post, $groupIds);

		$cachedGroups = $this->cachedGroups->findBy([
			'instance' => $instance,
			'groupId' => $groupIds
		]);
		$cachedGroupIds = array_map(function(CachedGroup $cachedGroup){
			return $cachedGroup->getId();
		}, $cachedGroups);

		$missing = array_filter($cachedGroupIds, function($id) use($existing){
			return !in_array($id, $existing);
		});

		foreach($missing as $id){
			$postGroup = new PostGroup;
			$postGroup->setPost($post);
			$postGroup->setGroup($this->em->getReference(CachedGroup::class, $id));
			$this->em->persist($postGroup);
		}
		$this->em->flush();
	}

	/**
	 * @param  Post  $post
	 * @param  array $groupIds
	 * @return array Returns the not removed ids.
	 */
	protected function removePostGroups(Post $post, $groupIds){
		$existing = [];
		$postGroups = $this->postGroups->findBy([
			'post' => $post
		]);
		foreach($postGroups as $postGroup){
			if(!in_array($postGroup->getGroup()->getId(), $groupIds)){
				$this->em->remove($postGroup);
			} else{
				$existing[] = $postGroup->getGroup()->getId();
			}
		}
		$this->em->flush();
		return $existing;
	}

	/**
	 * @param  Post     $post
	 * @param  Instance $instance
	 * @param  array    $metaGroups
	 * @return void
	 */
	protected function syncPostMetaGroups(Post $post, Instance $instance, array $metaGroups){
		$postMetaGroups = $this->postMetaGroups->findBy([
			'post' => $post
		]);

		foreach($postMetaGroups as $postMetaGroup){
			$this->em->remove($postMetaGroup);
		}
		$this->em->flush();

		foreach($metaGroups as $metaGroup){
			$postMetaGroup = new PostMetaGroup;
			$postMetaGroup->setPost($post);
			$postMetaGroup->setMetaGroup($metaGroup);
			$this->em->persist($postMetaGroup);
		}
		$this->em->flush();
	}

}