<?php

namespace Visitares\API;

use DateTime;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Factory\ClientFactory;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class ClientController{
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
	 * @var ClientFactory
	 */
	protected $factory = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param ClientFactory $factory
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		ClientFactory $factory
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
	 * @return Client[]
	 */
	public function getAll($token){
		session_write_close();
		return $this->instance ? $this->storage->client->findAll() : [];
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return Client
	 */
	public function getById($token, $id){
		return $this->instance ? $this->storage->client->findById($id) : null;
	}

	/**
	 * @param  string $token
	 * @param  array $data
	 * @return Client
	 */
	public function store($token, array $data){
		if($this->instance){
			$client = $this->factory->fromArray($data);
			$this->storage->store($client);
			$this->storage->apply();
			return $client;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @param  array $data
	 * @return Client
	 */
	public function update($token, $id, array $data){
		if($this->instance && $client = $this->storage->client->findById($id)){
			$client->setModificationDate(new DateTime);
			foreach($data['name'] as $langCode => $value){
				$client->setName($langCode, $value);
			}
			foreach($data['description'] as $langCode => $value){
				$client->setDescription($langCode, $value);
			}
			//$client->setName($data['name']);
			//$client->setDescription($data['description']);
			$client->setIsActive($data['isActive']);
			if(!is_numeric($data['sort'])){
				$client->setSort(null);
			} else{
				$client->setSort($data['sort']);
			}
			$client->setLineBreak($data['lineBreak'] ?? false);
			$client->setDividingLine($data['dividingLine'] ?? false);
			if($data['icon'] === null){
				$client->setIconId(null);
			} else{
				$client->setIconId($data['icon']['id']);
			}
			$this->storage->apply();
			return $client;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($token, $id){
		if($this->instance && $client = $this->storage->client->findById($id)){
			$this->storage->remove($client);
			$this->storage->apply();
			return true;
		}
		return false;
	}

	/**
	 * @param  string $token
	 * @param  array  $ids
	 * @return boolean
	 */
	public function removeMany($token, array $ids){
		if($this->instance){
			$users = $this->storage->client->find([
				'id' => $ids
			]);
			foreach($users as $client){
				$this->storage->remove($client);
			}
			$this->storage->apply();
			return true;
		}
		return false;
	}
}