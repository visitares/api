<?php

namespace Visitares\API;

use DateTime;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Factory\UnitFactory;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class UnitController{
	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var UnitFactory
	 */
	protected $factory = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param UnitFactory $factory
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		UnitFactory $factory
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
		$this->factory = $factory;
	}

	/**
	 * @param  string $token
	 * @return Unit[]
	 */
	public function getAll($token){
		session_write_close();
		return $this->instance ? $this->storage->unit->findAll() : [];
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return Unit
	 */
	public function getById($token, $id){
		return $this->instance ? $this->storage->unit->findById($id) : null;
	}

	/**
	 * @param  string $token
	 * @param  array $data
	 * @return Unit
	 */
	public function store($token, array $data){
		if($this->instance){
			$unit = $this->factory->fromArray($data);
			$this->storage->store($unit);
			$this->storage->apply();
			return $unit;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @param  array $data
	 * @return Unit
	 */
	public function update($token, $id, array $data){
		if($this->instance && $unit = $this->storage->unit->findById($id)){
			$unit->setModificationDate(new DateTime);
			$unit->setLabel($data['label']);
			$this->storage->apply();
			return $unit;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($token, $id){
		if($this->instance && $unit = $this->storage->unit->findById($id)){
			$this->storage->remove($unit);
			$this->storage->apply();
			return true;
		}
		return false;
	}
}