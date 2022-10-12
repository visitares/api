<?php

namespace Visitares\API;

use DateTime;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Factory\GroupFactory;

use Visitares\Service\Cache\GroupCacheService;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class GroupController{
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
	 * @var GroupFactory
	 */
	protected $factory = null;

	/**
	 * @var Instance
	 */
	protected $instance = null;

	/**
	 * @var GroupCacheService
	 */
	protected $groupCache = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param GroupFactory $factory
	 * @param GroupCacheService $groupCache
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		GroupFactory $factory,
		GroupCacheService $groupCache
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
		$this->factory = $factory;
		$this->groupCache = $groupCache;
	}

	/**
	 * @return Group[]
	 */
	public function getAll(){
		session_write_close();
		return $this->instance ? $this->storage->group->findAll() : [];
	}

	/**
	 * @param  integer $id
	 * @return Group
	 */
	public function getById($id){
		return $this->instance ? $this->storage->group->findById($id) : null;
	}

	/**
	 * @param  array $data
	 * @return Group
	 */
	public function store(array $data){
		if($this->instance){
			if($data['isDefault']){
				$this->storage->apply();
			}

			$group = $this->factory->fromArray($data);

			foreach($data['users'] as $id){
				$user = $this->storage->user->findById($id);
				$group->addUser($user);
			}

			$this->storage->store($group);
			$this->storage->apply();

			$this->groupCache->update($this->instance, $group);

			return $group;
		}
		return null;
	}

	/**
	 * @param  integer $id
	 * @param  array $data
	 * @return Group
	 */
	public function update($id, array $data){
		if($this->instance && $group = $this->storage->group->findById($id)){
			$group->setModificationDate(new DateTime);
			$group->setIsDefault($data['isDefault']);
			$group->setIsDefaultConfig($data['isDefaultConfig']);
			$group->setDefaultAppScreen($data['defaultAppScreen']);
			foreach($data['name'] as $langCode => $value){
				$group->setName($langCode, $value);
			}
			foreach($data['description'] as $langCode => $value){
				$group->setDescription($langCode, $value);
			}
			foreach($group->getUsers() as $user){
				if(!in_array($user->getId(), $data['users'])){
					$group->removeUser($user);
				}
			}
			foreach($data['users'] as $id){
				if($user = $this->storage->user->findById($id)){
					$group->addUser($user);
				}
			}


			$this->storage->apply();

			$this->groupCache->update($this->instance, $group);

			return $group;
		}
		return null;
	}

	/**
	 * @return void
	 */
	protected function resetDefaultProps(){
		$groups = $this->storage->group->findAll();
		foreach($groups as $group){
			$group->setIsDefault(false);
		}
		$this->storage->apply();
	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($id){
		if($this->instance && $group = $this->storage->group->findById($id)){
			$this->groupCache->remove($this->instance, $group);
			$this->storage->remove($group);
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
			$users = $this->storage->group->find([
				'id' => $ids
			]);
			foreach($users as $group){
				$this->groupCache->remove($this->instance, $group);
				$this->storage->remove($group);
			}
			$this->storage->apply();
			return true;
		}
		return false;
	}
}