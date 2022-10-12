<?php

namespace Visitares\API\Media;

use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Visitares\Entity\Media;
use Visitares\Entity\MediaGroup;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Service\Media\MediaService;
use Visitares\Service\Media\VideoPreviewService;

class MediaController{
	/** @var DatabaseFacade */
	protected $db = null;

	/** @var SystemStorageFacade */
	protected $systemStorage = null;

	/** @var InstanceStorageFacade */
	protected $storage = null;

	/** @var MediaService */
	protected $mediaService = null;

	/** @var VideoPreviewService */
	protected $videoPreviewService = null;

	/** @var Request */
	protected $request = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param MediaService $mediaService
	 * @param Request $request
	 * @param VideoPreviewService $videoPreviewService
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		MediaService $mediaService,
		Request $request,
		VideoPreviewService $videoPreviewService
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
		$this->mediaService = $mediaService;
		$this->request = $request;
		$this->videoPreviewService = $videoPreviewService;
	}

	public function query(array $filter = []){
		$repo = $this->storage->getRepository(Media::class);
		return $repo->findBy($filter);
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
	 * @param  Request $request
	 * @param  array   $file
	 * @return Media
	 */
	public function store(Request $request, $file = null){

		$media = new Media;
		$data = $this->getRequestData();
		$file = array_key_exists('file', $data) ? $data['file'] : $file;

		$media->setLabel($data['label']);
		$media->setDescription((array)$data['description']);
		$media->setType($data['type']);
		$media->setLength($data['length']);

		if(isset($data['language'])){
			$media->setLanguage($this->storage->language->findByCode($data['language']));
		}

		if($data['group']){
			$mediaGroup = $this->storage->getRepository(MediaGroup::class)->findOneById($data['group']);
			$media->setGroup($mediaGroup);
		}

		if($file instanceof UploadedFile){
			$storedFile = $this->mediaService->store($file);
			$media->setMime($file->getClientMimeType());
			$media->setFilename($storedFile->filename);
			$media->setOriginalFilename($file->getClientOriginalName());
			$media->setExt($file->getClientOriginalExtension());
			$media->setFilesize($file->getClientSize());

			if(strpos($media->getMime(), 'video/') !== false){
				$this->videoPreviewService->create($media, $this->storage->getToken());
			}
		}

		if($file === false){
			$media->setFilename($data['filename']);
		}

		$this->storage->store($media)->apply();

		return $media;
	}

	/**
	 * @param  Request      $request 
	 * @param  integer      $id 
	 * @param  UploadedFile $file
	 * @return Media
	 */
	public function update(Request $request, $id, $file = null){

		$repo = $this->storage->getRepository(Media::class);
		if(!$media = $repo->findOneById($id)){
			return null;
		}

		$data = $this->getRequestData();
		$file = array_key_exists('file', $data) ? $data['file'] : $file;

		$media->setModificationDate(new DateTime);
		$media->setLabel($data['label']);
		$media->setDescription((array)$data['description']);
		$media->setType($data['type']);
		$media->setLength($data['length']);

		if(isset($data['language'])){
			$media->setLanguage($this->storage->language->findByCode($data['language']));
		} else{
			$media->setLanguage(null);
		}

		if($data['group']){
			$mediaGroup = $this->storage->getRepository(MediaGroup::class)->findOneById($data['group']);
			$media->setGroup($mediaGroup);
		} else{
			$media->setGroup(null);
		}

		if($file instanceof UploadedFile){
			$storedFile = $this->mediaService->store($file);
			$media->setMime($file->getClientMimeType());
			$media->setFilename($storedFile->filename);
			$media->setOriginalFilename($file->getClientOriginalName());
			$media->setExt($file->getClientOriginalExtension());
			$media->setFilesize($file->getClientSize());

			if(strpos($media->getMime(), 'video/') !== false){
				$this->videoPreviewService->create($media, $this->storage->getToken());
			}

			$media->setInstanceToken(null);
		}

		if($file === null){
			$media->setInstanceToken(null);
			$this->mediaService->removeFile($media);
		}

		if($file === false){
			$media->setInstanceToken(null);
			$media->setFilename($data['filename']);
		}

		$this->storage->apply();

		return $media;

	}

	/**
	 * @param  string $id
	 * @return boolean
	 */
	public function remove($id){
		$repo = $this->storage->getRepository(Media::class);
		if($media = $repo->findOneById($id)){

			if($media->getType() === Media::TYPE_VIDEO && $media->getFilename()){
				$this->mediaService->removeFile($media);
			}

			$this->storage->remove($media);
			$this->storage->apply();
			return true;
		}
		return false;
	}
}