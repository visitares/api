<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Group;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class GroupStorage{
	/**
	 * @var EntityManager
	 */
	protected $entityManager = null;

	/**
	 * @var EntityRepository
	 */
	protected $repository = null;

	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager){
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getRepository('Visitares\Entity\Group');
	}

	/**
	 * @param array $criteria
	 * 
	 * @return User[]
	 */
	public function find(array $criteria = []){
		return $this->repository->findBy($criteria);
	}

	/**
	 * @return Group[]
	 */
	public function findAll(){
		return $this->repository->findAll();
	}

	/**
	 * @param  integer $id
	 * @return Group|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}

	/**
	 * @return Group[]
	 */
	public function findDefaultGroups(){
		return $this->repository->findBy([
			'isDefault' => true
		]);
	}
}