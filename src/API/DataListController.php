<?php

namespace Visitares\API;

use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\DataListItem;

class DataListController{

	private $systemStorage = null;
	private $storage = null;
	private $em = null;
	private $list = null;

	public function __construct(
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$domain
	){
		$instance = $systemStorage->instance->findByDomain(strtoupper($domain));
		if(!$instance){
			file_put_contents(APP_DIR_ROOT . '/error.log' , PHP_EOL . PHP_EOL . '[%s] DataListController -> COULD NOT FIND $domain: ' . $domain . PHP_EOL . PHP_EOL, FILE_APPEND);
		}
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		$this->storage->setToken($instance->getToken());
		$this->em = $this->storage->getEntityManager();
		$this->list = $this->em->getRepository(DataListItem::class);
	}

	/**
	 * @param  string $name
	 * @return array
	 */
	public function getList($name){
		return $this->list->findBy([
			'name' => $name
		]);
	}

	/**
	 * @param  string $name
	 * @param  mixed  $value
	 * @return DataListItem
	 */
	public function store($name, $value){
		$item = new DataListItem;
		$item->setName($name);
		$item->setValue($value);
		$this->em->persist($item);
		$this->em->flush();
		return $this->list->findOneById($item->getId());
	}

	/**
	 * @param  string $id
	 * @param  string $name
	 * @param  mixed  $value
	 * @return DataListItem
	 */
	public function update($id, $name, $value){
		if($item = $this->list->findOneById($id)){
			$item->setValue($value);
			$this->em->flush();
			return $this->list->findOneById($item->getId());
		}
		return null;
	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($id){
		if($item = $this->list->findOneById($id)){
			$this->em->remove($item);
			$this->em->flush();
			return true;
		}
		return false;
	}

}