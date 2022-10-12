<?php

namespace Visitares\API;

use DateTime;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Factory\DirtyWordFactory;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class DirtyWordController{
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
	 * @var DirtyWordFactory
	 */
	protected $factory = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param DirtyWordFactory $factory
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		DirtyWordFactory $factory
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
	 * @return DirtyWord[]
	 */
	public function getAll($token){
		session_write_close();
		return $this->instance ? $this->storage->dirtyWord->findAll() : [];
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return DirtyWord
	 */
	public function getById($token, $id){
		return $this->instance ? $this->storage->dirtyWord->findById($id) : null;
	}

	/**
	 * @param  string $token
	 * @param  array $data
	 * @return DirtyWord
	 */
	public function store($token, array $data){
		if($this->instance){
			$dirtyWord = $this->factory->fromArray($data);
			$this->storage->store($dirtyWord);
			$this->storage->apply();
			return $dirtyWord;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @param  array $data
	 * @return DirtyWord
	 */
	public function update($token, $id, array $data){
		if($this->instance && $dirtyWord = $this->storage->dirtyWord->findById($id)){
			$dirtyWord->setModificationDate(new DateTime);
			foreach($data['word'] as $lang => $word){
				$dirtyWord->setWord($lang, $word);
			}
			$this->storage->apply();
			return $dirtyWord;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($token, $id){
		if($this->instance && $dirtyWord = $this->storage->dirtyWord->findById($id)){
			$this->storage->remove($dirtyWord);
			$this->storage->apply();
			return true;
		}
		return false;
	}
}