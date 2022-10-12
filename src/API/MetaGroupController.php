<?php

namespace Visitares\API;

use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\MetaGroup;
use Visitares\Entity\MasterMetaGroup;
use Visitares\Entity\Instance;

class MetaGroupController{

	private $storage = null;
	private $em = null;
	private $metaGroups = null;
	private $instances = null;
	private $masterMetaGroups = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $this->storage->getEntityManager();
		$this->metaGroups = $this->em->getRepository(MetaGroup::class);
		$this->instances = $this->em->getRepository(Instance::class);
		$this->masterMetaGroups = $this->em->getRepository(MasterMetaGroup::class);
	}

	/**
	 * @param  array $data
	 * @return MetaGroup
	 */
	public function getById($id){
		if(!$metaGroup = $this->metaGroups->findOneById($id)){
			return null;	
		}
		return $metaGroup;
	}

	/**
	 * @param  string $token
	 * @return array
	 */
	public function getByInstanceToken($token){
		if(!$instance = $this->instances->findOneBy(['token' => $token])){
			return [];
		}
		if(!$instance->getMaster()){
			return [];
		}
		return array_map(function($masterMetaGroup){
			return $masterMetaGroup->getMetaGroup();
		}, $this->masterMetaGroups->findBy([
			'master' => $instance->getMaster()
		]));
	}

	/**
	 * @param  array $data
	 * @return MetaGroup
	 */
	public function store(array $data){
		$metaGroup = new MetaGroup;
		$metaGroup->setName($data['name']);
		$metaGroup->setDescription(isset($data['description']) ? $data['description'] : null);

		$this->em->persist($metaGroup);
		$this->em->flush();

		return $this->getById($metaGroup->getId());
	}

	/**
	 * @param  string $id
	 * @param  array  $data
	 * @return MetaGroup
	 */
	public function update($id, array $data){
		if(!$metaGroup = $this->metaGroups->findOneById($id)){
			return null;
		}
		$metaGroup->setName($data['name']);
		$metaGroup->setDescription(isset($data['description']) ? $data['description'] : null);
		$this->em->flush();
		return $this->getById($metaGroup->getId());
	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($id){
		if($metaGroup = $this->metaGroups->findOneById($id)){
			$this->em->remove($metaGroup);
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
		if($metaGroups = $this->metaGroups->findBy(['id' => $ids])){
			foreach($metaGroups as $metaGroup){
				$this->em->remove($metaGroup);
			}
			$this->em->flush();
			return true;
		}
		return false;
	}

}