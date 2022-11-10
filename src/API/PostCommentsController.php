<?php

namespace Visitares\API;

use Visitares\Entity\Post;
use Visitares\Entity\Comment;
use Visitares\Entity\CachedUser;
use Visitares\Storage\Facade\SystemStorageFacade;

class PostCommentsController{

	private $em = null;
	private $posts = null;
	private $comments = null;
	private $cachedUsers = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->em = $storage->getEntityManager();
		$this->posts = $this->em->getRepository(Post::class);
		$this->comments = $this->em->getRepository(Comment::class);
		$this->cachedUsers = $this->em->getRepository(CachedUser::class);
	}

	/**
	 * @param  Comment $comment
	 * @return array
	 */
	protected function serve(Comment $comment){
		$array = $comment->toArray();

		$array['creationDate'] = $array['creationDate'] ? date('c', strtotime($array['creationDate'])) : null;
		$array['modificationDate'] = $array['modificationDate'] ? date('c', strtotime($array['modificationDate'])) : null;

		unset($array['post']);
		$array['user'] = [
			'firstname' => $comment->getUser()->getFirstname(),
			'lastname' => $comment->getUser()->getLastname(),
			'company' => $comment->getUser()->getCompany() ? $comment->getUser()->getCompany() : null
		];
		return $array;
	}

	/**
	 * @param  integer $pid
	 * @return array
	 */
	public function getByPost($pid){
		if(!$post = $this->posts->findOneById($pid)){
			return null;
		}
		$comments = $this->comments->findBy(['post' => $post]);
		return array_map([$this, 'serve'], $comments);
	}

	/**
	 * @param  integer $pid
	 * @param  array   $data
	 * @return Comment
	 */
	public function store($pid, array $data){
		if(!$post = $this->posts->findOneById($pid)){
			return null;
		}

		$cachedUser = $this->cachedUsers->findOneBy([
			'instance' => $data['instance'],
			'userId' => $data['user']
		]);

		$comment = new Comment;
		$comment->setPost($post);
		$comment->setUser($cachedUser);
		$comment->setContent($data['content']);

		$this->em->persist($comment);
		$this->em->flush();

		return $this->serve($comment);
	}

	/**
	 * @param  integer $pid
	 * @param  array   $data
	 * @return Comment
	 */
	public function update($pid, $cid, array $data){
		if(!$comment = $this->comments->findOneBy(['post' => $pid, 'id' => $cid])){
			return null;
		}
		$comment->setContent($data['content']);

		$this->em->persist($comment);
		$this->em->flush();

		return $this->serve($comment);
	}

	/**
	 * @param  integer $pid
	 * @param  integer $cid
	 * @return Comment
	 */
	public function remove($pid, $cid){
		if(!$comment = $this->comments->findOneBy(['post' => $pid, 'id' => $cid])){
			return null;
		}
		$this->em->remove($comment);
		$this->em->flush();
		
		return true;
	}

}