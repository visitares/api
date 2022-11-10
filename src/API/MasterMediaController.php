<?php

namespace Visitares\API;

use DateTime;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Service\Media\MediaService;
use Visitares\Service\Media\VideoPreviewService;

use Visitares\Entity\Media;

use Visitares\Entity\Master;
use Visitares\Entity\MasterMedia;
use Visitares\Entity\MediaGroup;

class MasterMediaController{

	private $storage = null;
	private $instanceStorage = null;
	private $em = null;
	private $medias = null;
	private $groups = null;
	private $request = null;

	private $mediaService = null;
	private $videoPreviewService = null;

	public function __construct(
		SystemStorageFacade $storage,
		InstanceStorageFacade $instanceStorage,
		Request $request,
		MediaService $mediaService,
		VideoPreviewService $videoPreviewService
	){
		$this->storage = $storage;
		$this->instanceStorage = $instanceStorage;
		$this->em = $storage->getEntityManager();
		$this->medias = $this->em->getRepository(MasterMedia::class);
		$this->groups = $this->em->getRepository(MediaGroup::class);
		$this->request = $request;
		$this->mediaService = $mediaService;
		$this->videoPreviewService = $videoPreviewService;
	}

	/**
	 * @return array
	 */
	protected function getRequestData(){
		$data = $this->request->request->all();
		foreach($data as $key => $value){
			$data[$key] = json_decode($value);
		}
		return $data;
	}

	/**
	 * @param  integer $mid
	 * @param  integer $id
	 * @return MasterMedia
	 */
	public function getById($mid, $id){
		return $this->medias->findOneBy([
			'id' => $id,
			'master' => $mid
		]);
	}

	/**
	 * @param  integer $mid
	 * @param  string  $file
	 * @return MasterMedia
	 */
	public function store($mid, $file = null){
		$media = new MasterMedia;
		$data = $this->getRequestData();
		$file = array_key_exists('file', $data) ? $data['file'] : $file;

		$media->setMaster($this->em->getReference(Master::class, $mid));

		$media->setLabel($data['label']);
		$media->setDescription((array)$data['description']);
		$media->setType($data['type']);
		$media->setLength($data['length']);

		if($data['group']){
			$mediaGroup = $this->groups->findOneBy(['master' => $mid, 'id' => $data['group']]);
			$media->setGroup($mediaGroup);
		}

		if($file instanceof UploadedFile){
			$dir = sprintf(APP_DIR_ROOT . '/web/shared/master/%d/media', $mid);
			$this->mediaService->setOverrideDir($dir);

			$media->setMime($file->getClientMimeType());
			$media->setOriginalFilename($file->getClientOriginalName());
			$media->setExt($file->getClientOriginalExtension());
			$media->setFilesize($file->getSize());

			$storedFile = $this->mediaService->store($file);
			$media->setFilename($storedFile->filename);
			
			if(strpos($media->getMime(), 'video/') !== false){
				$this->videoPreviewService->setOverrideDir($dir);
				$this->videoPreviewService->create($media);
			}
		}

		if($file === false){
			$media->setFilename($data['filename']);
		}

		$this->em->persist($media);
		$this->em->flush();

		return $this->getById($mid, $media->getId());
	}

	/**
	 * @param  integer $mid
	 * @param  integer $id
	 * @param  string  $file
	 * @return MasterMedia
	 */
	public function update($mid, $id, $file = null){
		if(!$media = $this->medias->findOneBy(['master' => $mid, 'id' => $id])){
			return null;
		}

		$data = $this->getRequestData();
		$file = array_key_exists('file', $data) ? $data['file'] : $file;

		$media->setModificationDate(new DateTime);
		$media->setLabel($data['label']);
		$media->setDescription((array)$data['description']);
		$media->setLength($data['length']);

		if($data['group']){
			$mediaGroup = $this->groups->findOneBy(['master' => $mid, 'id' => $data['group']]);
			$media->setGroup($mediaGroup);
		} else{
			$media->setGroup(null);
		}

		$dir = sprintf(APP_DIR_ROOT . '/web/shared/master/%d/media', $mid);
		$this->mediaService->setOverrideDir($dir);

		if($file instanceof UploadedFile){
			$media->setMime($file->getClientMimeType());
			$media->setOriginalFilename($file->getClientOriginalName());
			$media->setExt($file->getClientOriginalExtension());
			$media->setFilesize($file->getSize());
			
			$storedFile = $this->mediaService->store($file);
			$media->setFilename($storedFile->filename);

			if(strpos($media->getMime(), 'video/') !== false){
				$this->videoPreviewService->setOverrideDir($dir);
				$this->videoPreviewService->create($media);
			}
		}

		if($file === null){
			$this->mediaService->removeFile($media);
		}

		if($file === false){
			$media->setFilename($data['filename']);
			$media->setOriginalFilename(null);
			$media->setMime(null);
			$media->setExt(null);
			$media->setFilesize(null);
		}

		$this->em->flush();
		return $this->getById($mid, $media->getId());
	}

	/**
	 * @param  array  $ids
	 * @param  int    $group
	 * @param  int    $instance
	 * @return boolean
	 */
	public function import(array $ids, $group, $instance){

		if(!$instance = $this->storage->instance->findById($instance)){
			return false;
		}
		$this->instanceStorage->setToken($instance->getToken());

		if(!$group = $this->instanceStorage->getEntityManager()->getRepository(MediaGroup::class)->findOneBy(['id' => $group])){
			return false;
		}

		$masterMedias = $this->medias->findBy(['id' => $ids]);

		foreach($masterMedias as $media){
			$copy = new Media;
			$copy->setMasterId($media->getMaster()->getId());
			$copy->setGroup($group);
			$copy->setLabel($media->getLabel());
			$copy->setDescription($media->getDescription() ?? []);
			$copy->setType($media->getType());
			$copy->setMime($media->getMime());
			$copy->setFilename($media->getFilename());
			$copy->setOriginalFilename($media->getOriginalFilename());
			$copy->setFilesize($media->getFilesize());
			$copy->setLength($media->getLength());
			$copy->setExt($media->getExt());
			$this->instanceStorage->store($copy)->apply();
		}

		return true;
	}

	/**
	 * @param  integer $mid
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($mid, $id, $flush = true){
		$dir = sprintf(APP_DIR_ROOT . '/web/shared/master/%d/media', $mid);
		$this->mediaService->setOverrideDir($dir);

		if($media = $this->medias->findOneBy(['master' => $mid, 'id' => $id])){
			if($media->getType() === MasterMedia::TYPE_VIDEO && $media->getFilename()){
				$this->mediaService->removeFile($media);
			}
			$this->em->remove($media);
			if($flush){
				$this->em->flush();
			}
			return true;
		}
		return false;
	}

	/**
	 * @param  integer $mid
	 * @param  integer $id
	 * @return boolean
	 */
	public function removeMany($mid, $ids){
		foreach($ids as $id){
			$this->remove($mid, $id);
		}
		$this->em->flush();
		return true;
	}

}
