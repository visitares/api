<?php

namespace Visitares\API;

use Visitares\Entity\Post;
use Visitares\Entity\Like;
use Visitares\Entity\CachedUser;
use Visitares\Storage\Facade\SystemStorageFacade;

class PostLikesController{

	/**
	 * @var EntityManager
	 */
	private $em = null;

	/**
	 * @var Repository
	 */
	private $likes = null;

	/**
	 * @param SystemStorageFacade $storage
	 */
	public function __construct(
		SystemStorageFacade $storage
	){
		$this->em = $storage->getEntityManager();
		$this->posts = $this->em->getRepository(Post::class);
		$this->likes = $this->em->getRepository(Like::class);
		$this->users = $this->em->getRepository(CachedUser::class);
	}

	/**
	 * @param  integer $pid
	 * @param  integer $iid
	 * @param  integer $uid
	 * @return boolean
	 */
	public function like($pid, $iid, $uid){
		if(!$post = $this->posts->findOneById($pid)){
			return false;
		}
		if(!$user = $this->users->findOneBy(['instance' => $iid, 'userId' => $uid])){
			return false;
		}
		if(!$like = $this->likes->findOneBy(['post' => $pid, 'user' => $user])){
			$like = new Like;
			$like->setPost($this->em->getReference(Post::class, $pid));
			$like->setUser($this->em->getReference(CachedUser::class, $user->getId()));
			$this->em->persist($like);
			$this->em->flush();
			return true;
		}
		return false;
	}

	/**
	 * @param  integer $pid
	 * @param  integer $uid
	 * @param  integer $iid
	 * @return boolean
	 */
	public function unlike($pid, $iid, $uid){
		if(!$user = $this->users->findOneBy(['instance' => $iid, 'userId' => $uid])){
			return false;
		}
		if(!$like = $this->likes->findOneBy(['post' => $pid, 'user' => $user])){
			return false;
		}
		$this->em->remove($like);
		$this->em->flush();
		return true;
	}

}