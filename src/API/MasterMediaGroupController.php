<?php

namespace Visitares\API;

use Visitares\Entity\Master;
use Visitares\Entity\MediaGroup;
use Visitares\Storage\Facade\SystemStorageFacade;

class MasterMediaGroupController{

	private $storage = null;
	private $em = null;
	private $groups = null;

	/**
	 * @param SystemStorageFacade $storage
	 */
	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $storage->getEntityManager();
		$this->groups = $this->em->getRepository(MediaGroup::class);
	}

	/**
	 * @param  integer $mid
	 * @param  integer $id
	 * @return MediaGroup
	 */
	public function getById($mid, $id){
		return $this->groups->findOneBy(['master' => $mid, 'id' => $id]);
	}

	/**
	 * @param  int   $mid
	 * @param  array $data
	 * @return MediaGroup
	 */
	public function store($mid, array $data){
		$mediaGroup = new MediaGroup;
		$mediaGroup->setMaster($this->em->getReference(Master::class, $mid));
		$mediaGroup->setLabel($data['label']);
		$mediaGroup->setDescription($data['description']);

		$this->em->persist($mediaGroup);
		$this->em->flush();
		return $mediaGroup;
	}

	/**
	 * @param  int   $mid
	 * @param  int   $id
	 * @param  array $data
	 * @return MediaGroup
	 */
	public function update($mid, $id, array $data){
		if(!$mediaGroup = $this->groups->findOneById($id)){
			return null;
		}
		$mediaGroup->setLabel($data['label']);
		$mediaGroup->setDescription($data['description']);

		$this->em->flush();
		return $mediaGroup;
	}

	/**
	 * @param  int $mid
	 * @param  int $id
	 * @return boolean
	 */
	public function remove($mid, $id){
		if($mediaGroup = $this->groups->findOneBy(['master' => $mid, 'id' => $id])){
			$this->em->remove($mediaGroup);
			$this->em->flush();
			return true;
		}
		return false;
	}
}