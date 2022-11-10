<?php

namespace Visitares\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Visitares\Entity\Media;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Service\Media\MediaService;

class MediaController{
	/** @var DatabaseFacade */
	protected $db = null;

	/** @var SystemStorageFacade */
	protected $systemStorage = null;

	/** @var InstanceStorageFacade */
	protected $storage = null;

	/** @var MediaService */
	protected $mediaService = null;

	/** @var Request */
	protected $request = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param MediaService $mediaService
	 * @param Request $request
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		MediaService $mediaService,
		Request $request
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
		$this->mediaService = $mediaService;
		$this->request = $request;
	}

	public function query(array $filter){
		session_write_close();
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
		$media->setDescription($data['description']);
		$media->setType($data['type']);

		if($file instanceof UploadedFile){
			$media->setMime($file->getClientMimeType());
			$media->setOriginalFilename($file->getClientOriginalName());
			$media->setExt($file->getClientOriginalExtension());
			$media->setFilesize($file->getSize());
			$media->setLength($data['length']);
			
			$storedFile = $this->mediaService->store($file);
			$media->setFilename($storedFile->filename);
		}

		if($file === false){
			$media->setFilename($data['filename']);
			$media->setLength($data['length']);
		}

		$repo = $this->storage->getRepository(Media::class);
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

		$media->setLabel($data['label']);
		$media->setDescription($data['description']);
		$media->setLength($data['length']);

		echo $media->getLength();exit;

		if($file instanceof UploadedFile){
			$media->setMime($file->getClientMimeType());
			$media->setOriginalFilename($file->getClientOriginalName());
			$media->setExt($file->getClientOriginalExtension());
			$media->setFilesize($file->getSize());
			
			$storedFile = $this->mediaService->store($file);
			$media->setFilename($storedFile->filename);
		}

		if($file === null){
			$this->mediaService->removeFile($media);
		}

		if($file === false){
			$media->setFilename($data['filename']);
		}

		$repo = $this->storage->getRepository(Media::class);
		$this->storage->apply();

		return $media;

	}
}