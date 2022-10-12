<?php

namespace Visitares\API;

use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Master;
use Visitares\Entity\Instance;
use Visitares\Entity\MasterMetaGroup;
use Visitares\Entity\MetaGroup;

class MasterController{

	private $storage = null;
	private $em = null;
	private $masters = null;
	private $mastersInstances = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $this->storage->getEntityManager();
		$this->masters = $this->em->getRepository(Master::class);
		// @ref $this->masterInstances = $this->em->getRepository(MasterInstance::class);
	}

	/**
	 * @param  array $data
	 * @return Master
	 */
	public function getById($id){
		if($master = $this->masters->findOneById($id)){
			$instances = $this->em->getRepository(Instance::class)->findBy([
				'master' => $master
			]);
			$master->instances = array_map(function($instance){
				return $instance->getId();
			}, $instances);

			$masterMetaGroups = $this->em->getRepository(MasterMetaGroup::class)->findBy([
				'master' => $master
			]);
			$master->metaGroups = [];
			foreach($masterMetaGroups as $masterMetaGroup){
				$master->metaGroups[] = $masterMetaGroup->getMetaGroup()->getId();
			}

			return $master;
		}
		return null;
	}

	/**
	 * @param  array $data
	 * @return Master
	 */
	public function store(array $data){
		$master = new Master;
		$master->setIsActive($data['isActive']);
		$master->setName($data['name']);
		$master->setDescription(isset($data['description']) ? $data['description'] : null);
		$master->setShortDescription(isset($data['shortDescription']) ? $data['shortDescription'] : null);

		$this->em->persist($master);
		$this->em->flush();

		$this->updateInstanceJoins($master, $data['instances']);
		$this->updateMetaGroupJoins($master, $data['metaGroups']);

		return $this->getById($master->getId());
	}

	/**
	 * @param  string $id
	 * @param  array  $data
	 * @return Master
	 */
	public function update($id, array $data){
		if($master = $this->masters->findOneById($id)){
			$master->setIsActive($data['isActive']);
			$master->setName($data['name']);
			$master->setDescription(isset($data['description']) ? $data['description'] : null);
			$master->setShortDescription(isset($data['shortDescription']) ? $data['shortDescription'] : null);

			$this->em->flush();
			
			$this->updateInstanceJoins($master, $data['instances']);
			$this->updateMetaGroupJoins($master, $data['metaGroups']);

			return $this->getById($master->getId());
		}
		return null;
	}

	/**
	 * @param  array $ids
	 * @return void
	 */
	protected function updateInstanceJoins(Master $master, array $ids){
		$instances = $this->storage->instance->findBy([
			'master' => $master
		]);

		foreach($instances as $instance){
			if(!in_array($instance->getId(), $ids)){
				$instance->setMaster(null);
			}
		}

		$instances = $this->storage->instance->findBy([
			'id' => $ids
		]);

		foreach($instances as $instance){
			$instance->setMaster($master);
		}

		$this->em->flush();
	}

	/**
	 * @param  Master $master
	 * @param  array  $ids
	 * @return void
	 */
	protected function updateMetaGroupJoins(Master $master, array $ids){
		$masterMetaGroups = $this->em->getRepository(MasterMetaGroup::class)->findBy([
			'master' => $master
		]);

		foreach($masterMetaGroups as $masterMetaGroup){
			if(!in_array($masterMetaGroup->getMetaGroup()->getId(), $ids)){
				unset($ids[ array_search($masterMetaGroup->getMetaGroup()->getId(), $ids) ]);
				$this->em->remove($masterMetaGroup);
			} else{
				unset($ids[ array_search($masterMetaGroup->getMetaGroup()->getId(), $ids) ]);
			}
		}

		$this->em->flush();

		foreach($ids as $id){
			$masterMetaGroup = new MasterMetaGroup;
			$masterMetaGroup->setMaster($master);

			if($id === null){
				echo 'WHAT THE FUCK?!';exit;
			}

			$masterMetaGroup->setMetaGroup($this->em->getReference(MetaGroup::class, $id));
			$this->em->persist($masterMetaGroup);
		}
		$this->em->flush();

	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($id){
		if($master = $this->masters->findOneById($id)){
			$this->em->remove($master);
			$this->em->flush();
			return true;
		}
		return false;
	}

	/**
	 * @param  array $ids
	 * @return boolean
	 */
	public function removeMany(array $ids){
		if($masters = $this->masters->findBy(['id' => $ids])){
			foreach($masters as $master){
				$this->em->remove($master);
			}
			$this->em->flush();
			return true;
		}
		return false;
	}

}