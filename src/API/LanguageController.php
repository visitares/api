<?php

namespace Visitares\API;

use DateTime;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Factory\LanguageFactory;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class LanguageController{
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
	 * @var LanguageFactory
	 */
	protected $factory = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param LanguageFactory $factory
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		LanguageFactory $factory
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
	 * @return Language[]
	 */
	public function getAll($token){
		session_write_close();
		return $this->instance ? $this->storage->language->findAll() : [];
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return Language
	 */
	public function getById($token, $id){
		return $this->instance ? $this->storage->language->findById($id) : null;
	}

	/**
	 * @param  string $token
	 * @param  array $data
	 * @return Language
	 */
	public function store($token, array $data){
		if($this->instance){
			$language = $this->factory->fromArray($data);
			$this->storage->store($language);
			$this->storage->apply();
			return $language;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @param  array $data
	 * @return Language
	 */
	public function update($token, $id, array $data){
		if($this->instance && $language = $this->storage->language->findById($id)){
			$language->setModificationDate(new DateTime);
			$language->setIsDefault($data['isDefault']);
			$language->setCode(strtolower($data['code']));
			$language->setLabel($data['label']);
			// isDefault
			$this->storage->apply();
			return $language;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($token, $id){
		if($this->instance && $language = $this->storage->language->findById($id)){
			$this->storage->remove($language);
			$this->storage->apply();
			return true;
		}
		return false;
	}
}