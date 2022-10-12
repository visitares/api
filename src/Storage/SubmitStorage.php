<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Submit;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class SubmitStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Submit');
	}

	/**
	 * @param array $criteria
	 * 
	 * @return Submit[]
	 */
	public function findBy(array $criteria = [], array $orderBy = []){
		return $this->repository->findBy($criteria, $orderBy);
	}
}