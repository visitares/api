<?php

namespace Visitares\API;

use DateTime;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Factory\CategoryFactory;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CategoryController{
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
	 * @var CategoryFactory
	 */
	protected $factory = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param CategoryFactory $factory
	 * @param string $token
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		CategoryFactory $factory,
		$token
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		$this->factory = $factory;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
	}

	/**
	 * @param  string $token
	 * @return Category[]
	 */
	public function getAll($token){
		session_write_close();
		return $this->instance ? $this->storage->category->findAll() : [];
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return Category
	 */
	public function getById($token, $id){
		return $this->instance ? $this->storage->category->findById($id) : null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return Category[]
	 */
	public function getByUser($token, $id){
		if($this->instance && $user = $this->storage->user->findById($id)){
			$em = $this->storage->getEntityManager();

			$groups = $user->getGroups();
			$groupIds = [];
			foreach($groups as $group){
				$groupIds[] = $group->getId();
			}

			if(!$groupIds){
				return[];
			}

			$today = (new DateTime)->format('Y-m-d');
			$dateExpr = sprintf("
				(
					(c.beginDate IS NULL AND c.endDate IS NULL) OR
					(c.beginDate IS NULL AND c.endDate >= '%s') OR
					(c.beginDate <= '%s' AND c.endDate IS NULL) OR
					(c.beginDate <= '%s' AND c.endDate >= '%s')
				)
			", $today, $today, $today, $today);

			$dql = $em->createQuery('
				SELECT c
				FROM Visitares\Entity\Category c
				LEFT JOIN c.client cl
				LEFT JOIN c.groups g
				WHERE cl.isActive = true
					AND c.isActive = true
					AND ' . $dateExpr . ' AND g.id IN (' . implode(', ', $groupIds) . ')
				');
			$categories = $dql->getResult();

			$lang = $user->getLanguage()->getCode();

			$clients = [];
			foreach($categories as $category){
				if($category->getName($lang)){
					$clients[$category->getClient()->getId()] = $category->getClient();
				}
			}

			$categories = array_filter($categories, function($category) use($lang){
				return !!$category->getName($lang);
			});

			return[
				'categories' => $categories,
				'clients' => array_values($clients)
			];
		}
		return [];
	}

	/**
	 * @param  string $token
	 * @param  array $data
	 * @return Category
	 */
	public function store($token, array $data){
		if($this->instance){
			$category = $this->factory->fromArray($data);
			$this->storage->store($category);
			$this->storage->apply();
			return $category;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @param  array $data
	 * @return Category
	 */
	public function update($token, $id, array $data){
		if($this->instance && $category = $this->storage->category->findById($id)){
			$category->setModificationDate(new DateTime);

			if($data['icon'] === null){
				$category->setIconId(null);
			} else{
				$category->setIconId($data['icon']['id']);
			}

			$category->setIsActive($data['isActive']);
			$category->setSort($data['sort']);
			$category->setLineBreak($data['lineBreak'] ?? false);
			$category->setDividingLine($data['dividingLine'] ?? false);
			$category->setBeginDate($data['beginDate'] ? new DateTime($data['beginDate']) : null);
			$category->setEndDate($data['endDate'] ? new DateTime($data['endDate']) : null);
			$category->setInputLockHours($data['inputLockHours']);
			$category->setProcessesEnabled($data['processesEnabled']);
			$category->setEnableProcessDefinitions($data['enableProcessDefinitions'] ?? false);
			//$category->setMaxScore($data['maxScore']);
			foreach($data['name'] as $langCode => $value){
				$category->setName($langCode, $value);
			}
			foreach($data['description'] as $langCode => $value){
				$category->setDescription($langCode, $value);
			}
			if($client = $this->storage->client->findById($data['client'])){
				$category->setClient($client);
			}
			foreach($category->getGroups() as $group){
				if(!in_array($group->getId(), $data['groups'])){
					$category->removeGroup($group);
				}
			}
			foreach($data['groups'] as $id){
				if($group = $this->storage->group->findById($id)){
					$category->addGroup($group);
				}
			}
			$this->storage->apply();
			return $category;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($token, $id){
		if($this->instance && $category = $this->storage->category->findById($id)){
			$this->storage->remove($category);
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
			$categories = $this->storage->category->find([
				'id' => $ids
			]);
			foreach($categories as $category){
				$this->storage->remove($category);
			}
			$this->storage->apply();
			return true;
		}
		return false;
	}
}
