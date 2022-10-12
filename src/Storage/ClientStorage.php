<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Client;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class ClientStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Client');
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
	 * @return Client[]
	 */
	public function findAll(){
		return $this->repository->findAll();
	}

	/**
	 * @param  integer $id
	 * @return Client|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}
}