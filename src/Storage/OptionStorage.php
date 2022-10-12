<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Option;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class OptionStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Option');
	}

	/**
	 * @return Option[]
	 */
	public function findAll(){
		return $this->repository->findAll();
	}

	/**
	 * @param  integer $id
	 * @return Option|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}
}