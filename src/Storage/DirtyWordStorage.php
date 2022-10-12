<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\DirtyWord;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class DirtyWordStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\DirtyWord');
	}

	/**
	 * @return DirtyWord[]
	 */
	public function findAll(){
		return $this->repository->findAll();
	}

	/**
	 * @param  integer $id
	 * @return DirtyWord|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}

	/**
	 * @param  string $code
	 * @return DirtyWord|null
	 */
	public function findByCode($code){
		return $this->repository->findOneBy([
			'code' => $code
		]);
	}
}