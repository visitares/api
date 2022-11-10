<?php

namespace Visitares\Service\Timeline;

use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Visitares\Entity\Media;
use Visitares\Entity\Post;
use Visitares\Entity\PostMedia;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Service\Media\VideoPreviewService;

class PostMediaService{

	/**
	 * @var EntityManager
	 */
	private $em = null;

	/**
	 * @var Repository
	 */
	private $media = null;

	/**
	 * @param SystemStorageFacade $storage
	 */
	public function __construct(
		SystemStorageFacade $storage,
		VideoPreviewService $videoPreviewService
	){
		$this->em = $storage->getEntityManager();
		$this->media = $this->em->getRepository(PostMedia::class);
		$this->videoPreviewService = $videoPreviewService;
	}

	/**
	 * @param  Post $post
	 * @return string
	 */
	protected function getDir(Post $post){
		$hash = hash('sha256', $post->getTimeline()->getId());
		$hash = substr($hash, 0, 10);
		return sprintf(APP_DIR_ROOT . '/web/shared/timeline/%s/media', $hash);
	}

	/**
	 * @param  UploadedFile $file
	 * @return Media
	 */
	public function create(Post $post, UploadedFile $file){
		$dir = $this->getDir($post);
		$stored = $this->store($file, $dir);

		$media = new PostMedia;
		$media->setType(Media::TYPE_OTHER);
		$media->setPost($post);
		$media->setMime($file->getClientMimeType());
		$media->setFilename($stored->filename);
		$media->setOriginalFilename($file->getClientOriginalName());
		$media->setExt($file->getClientOriginalExtension());
		$media->setFilesize($file->getSize());

		if(strpos($media->getMime(), 'video/') !== false){
			$media->setType(Media::TYPE_VIDEO);
		} elseif(strpos($media->getMime(), 'image/') !== false){
			$media->setType(Media::TYPE_IMAGE);
		}

		$this->em->persist($media);
		$this->em->flush();

		if(strpos($media->getMime(), 'video/') !== false){
			$this->videoPreviewService->setOverrideDir($dir);
			$this->videoPreviewService->create($media);
		}

		return $media;
	}

	/**
	 * @param  UploadedFile $file
	 * @param  string       $dir
	 * @return string
	 */
	protected function store(UploadedFile $file, $dir){
		$hash = hash('sha256', $file->getClientOriginalName() . (new DateTime)->format('Y-m-d H:i:s') . $file->getSize());

		$filename = sprintf('%s.%s', $hash, $file->getClientOriginalExtension());
		$location = sprintf('%s/%s', $dir, $filename);
		if(!file_exists($dir)){
			@mkdir($dir, 0777, true);
		}

		$file->move($dir, $filename);

		return (object)[
			'location' => $location,
			'filename' => $filename
		];
	}

	/**
	 * @param  Media $media
	 * @return boolean
	 */
	public function remove(Media $media){
		$location = sprintf('%s/%s', $this->getDir($media->getPost()), $media->getFilename());
		if(file_exists($location)){
			@unlink($location);
		}
		$this->em->remove($media);
		$this->em->flush();
		return true;
	}

}
