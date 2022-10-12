<?php

namespace Visitares\API;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Service\Timeline\PostMediaService;
use Visitares\Service\Media\VideoPreviewService;
use Visitares\Entity\{Post, Media, PostMedia, MasterMedia, Instance, MediaGroup};

class PostMediaController{

	/**
	 * @var EntityManager
	 */
	private $em = null;

	/**
	 * @var Repository
	 */
	private $posts = null;

	/**
	 * @var Repository
	 */
	private $instances = null;

	/**
	 * @var Repository
	 */
	private $media = null;

	/**
	 * @var PostMediaService
	 */
	private $postMediaService = null;

	/**
	 * @var VideoPreviewService
	 */
	private $videoPreviewService = null;

	/**
	 * @param SystemStorageFacade $storage
	 * @param PostMediaService $postMediaService
	 * @param VideoPreviewService $videoPreviewService
	 */
	public function __construct(
		SystemStorageFacade $storage,
		PostMediaService $postMediaService,
		VideoPreviewService $videoPreviewService
	){
		$this->em = $storage->getEntityManager();
		$this->instances = $this->em->getRepository(Instance::class);
		$this->posts = $this->em->getRepository(Post::class);
		$this->media = $this->em->getRepository(PostMedia::class);
		$this->mediaGroups = $this->em->getRepository(MediaGroup::class);
		$this->postMediaService = $postMediaService;
		$this->videoPreviewService = $videoPreviewService;
	}

	/**
	 * @param  integer      $id
	 * @param  UploadedFile $file
	 * @return array
	 */
	public function upload($id, $file){
		if(!$post = $this->posts->findOneById($id)){
			return null;
		}
		$media = $this->postMediaService->create($post, $file);
		return $media;
	}

	/**
	 * @param  integer $mid
	 * @return array
	 */
	public function remove($mid){
		if(!$media = $this->media->findOneById($mid)){
			return null;
		}
		return $this->postMediaService->remove($media);
	}

	/**
	 * @param integer $id
	 * @return bool
	 */
	public function publish(int $id, int $mid, string $token){
		$instance = $this->instances->findOneBy(['token' => $token]);
		if(!$instance){
			return false;
		}

		$master = $instance->getMaster();
		if(!$master){
			return false;
		}

		$mediaGroup = $this->mediaGroups->findOneBy(['master' => $master]);
		if(!$mediaGroup){
			return false;
		}

		$post = $this->posts->findOneById($id);
		if(!$post){
			return false;
		}

		$media = $this->media->findOneById($mid);
		if(!$media){
			return false;
		}

		if($post->getId() !== $media->getPost()->getId()){
			return false;
		}

		$copy = new MasterMedia();
		$copy->setMaster($master);
		$copy->setGroup($mediaGroup);
		$copy->setLabel($media->getOriginalFilename());
		$copy->setDescription($media->getDescription() ?? []);
		$copy->setType($media->getType());
		$copy->setMime($media->getMime());
		$copy->setFilename(hash('sha256', $media->getOriginalFilename() . (new \DateTime)->format('Y-m-d H:i:s') . $media->getFilesize()) . '.' . $media->getExt());
		$copy->setOriginalFilename($media->getOriginalFilename());
		$copy->setExt($media->getExt());
		$copy->setFilesize($media->getFilesize());
		$copy->setLength($media->getLength());

		$dstDir = APP_DIR_ROOT . sprintf('/web/shared/master/%d/media', $master->getId());
		if(!file_exists($dstDir)){
			@mkdir($dstDir, 0775, true);
		}

		$src = APP_DIR_ROOT . sprintf('/web/shared/timeline/%s/media/%s', $post->getTimeline()->getHash(), $media->getFilename());
		$dst = APP_DIR_ROOT . sprintf('/web/shared/master/%d/media/%s', $master->getId(), $copy->getFilename());
		@copy($src, $dst);

		if($copy->getType() === Media::TYPE_VIDEO){
			$this->videoPreviewService->setOverrideDir($dstDir);
			$this->videoPreviewService->create($copy);
		}

		$this->em->persist($copy);
		$this->em->flush();

		return true;
	}

}
