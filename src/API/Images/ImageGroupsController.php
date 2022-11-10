<?php

namespace Visitares\API\Images;

use DateTime;
use Visitares\Entity\ImageGroup;
use Visitares\Service\ImageGroupRelations;
use Visitares\Storage\Facade\SystemStorageFacade;

class ImageGroupsController{

	private $storage = null;
	private $imageGroupRelations = null;

	public function __construct(
		SystemStorageFacade $storage,
		ImageGroupRelations $imageGroupRelations
	){
		$this->storage = $storage;
		$this->imageGroupRelations = $imageGroupRelations;
	}

	public function getAll(){
		return $this->storage->imageGroup->find();
	}

	public function getByInstance($token){
		$instance = $this->storage->instance->findByToken($token);
		$em = $this->storage->getEntityManager();

		$query = null;
		if($token === 'any'){
			$query = $em->createQuery('
				SELECT	i
				FROM	Visitares\Entity\ImageGroup i
			');
		} else {
			$query = $em->createQuery('
				SELECT	i
				FROM	Visitares\Entity\ImageGroup i
				WHERE	i.id IN (:ids) OR i.instances IS NULL
			');
			$query->setParameter('ids', $instance ? $instance->getImageGroups() : []);
		}

		return $query->getResult();
	}

	public function get($id){
		return $this->storage->imageGroup->findById($id);
	}

	public function store(array $data){
		$imageGroup = new ImageGroup;
		$imageGroup->setCreationDate(new DateTime);
		$imageGroup->setLabel($data['label']);
		$imageGroup->setType($data['type']);
		$imageGroup->setInstances($data['instances']);
		$this->storage->store($imageGroup);
		$this->storage->apply();
		
		$this->imageGroupRelations->sync($imageGroup, $imageGroup->getInstances());

		return $this->get($imageGroup->getId());
	}

	public function update($id, array $data){
		if(!$imageGroup = $this->storage->imageGroup->findById($id)){
			return false;
		}

		$imageGroup->setModificationDate(new DateTime);
		$imageGroup->setLabel($data['label']);
		$imageGroup->setType($data['type']);
		$imageGroup->setInstances($data['instances']);
		$this->storage->apply();

		$this->imageGroupRelations->sync($imageGroup, $imageGroup->getInstances());

		return $this->get($imageGroup->getId());
	}

	public function remove($id){
		if(!$imageGroup = $this->storage->imageGroup->findById($id)){
			return false;
		}

		if($images = $this->storage->image->find(['groupId' => $id])){
			return false;
		}

		$this->imageGroupRelations->sync($imageGroup, []);
		$this->storage->remove($imageGroup);
		$this->storage->apply();

		return true;
	}

}