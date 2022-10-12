<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\CategoryProcess;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CategoryProcessStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\CategoryProcess');
	}

	/**
	 * @return DirtyWord[]
	 */
	public function findAll(){
		return $this->repository->findAll();
	}

	/**
	 * @return DirtyWord[]
	 */
	public function find(array $criteria = []){
		return $this->repository->findBy($criteria);
	}

	/**
	 * @return DirtyWord[]
	 */
	public function findOne(array $criteria = []){
		return $this->repository->findOneBy($criteria);
	}

	/**
	 * @param  integer $id
	 * @return DirtyWord|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}
}