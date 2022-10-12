<?php

namespace Visitares\API\Media;

use Visitares\Entity\MediaGroup;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

class MediaGroupController{
	/** @var DatabaseFacade */
	protected $db = null;

	/** @var SystemStorageFacade */
	protected $systemStorage = null;

	/** @var InstanceStorageFacade */
	protected $storage = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
	}

	/**
	 * @param  array $filter
	 * @return array
	 */
	public function query(array $filter = []){
		$repo = $this->storage->getRepository(MediaGroup::class);
		return $repo->findBy($filter);
	}

	/**
	 * @param  array $data
	 * @return MediaGroup
	 */
	public function store(array $data){
		$mediaGroup = new MediaGroup;
		$mediaGroup->setLabel($data['label']);
		$mediaGroup->setDescription($data['description']);

		$repo = $this->storage->getRepository(MediaGroup::class);
		$this->storage->store($mediaGroup)->apply();

		return $mediaGroup;
	}

	/**
	 * @param  int   $id
	 * @param  array $data
	 * @return MediaGroup
	 */
	public function update($id, array $data){
		$repo = $this->storage->getRepository(MediaGroup::class);
		if(!$mediaGroup = $repo->findOneById($id)){
			return null;
		}

		$mediaGroup->setLabel($data['label']);
		$mediaGroup->setDescription($data['description']);
		$this->storage->store($mediaGroup)->apply();
		return $mediaGroup;
	}

	/**
	 * @param  string $id
	 * @return boolean
	 */
	public function remove($id){
		$repo = $this->storage->getRepository(MediaGroup::class);
		if($mediaGroup = $repo->findOneById($id)){
			$this->storage->remove($mediaGroup);
			$this->storage->apply();
			return true;
		}
		return false;
	}
}