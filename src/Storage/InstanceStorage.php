<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Instance;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class InstanceStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Instance');
	}

	/**
	 * @return Instance[]
	 */
	public function findAll(){
		$qb = $this->entityManager->createQueryBuilder();
		$query = $qb->select('i')
			->from('Visitares\Entity\Instance', 'i')
			->where('i.domain is not null')
			->getQuery();
		return $query->getResult();
	}

	/**
	 * @return Instance[]
	 */
	public function findTemplates(){
		return $this->repository->findBy([
			'isTemplate' => true
		]);
	}

	/**
	 * @param  array $criteria
	 * @param  array $orderBy
	 * @return array
	 */
	public function findBy(array $criteria, array $orderBy = []){
		return $this->repository->findBy($criteria, $orderBy);
	}

	/**
	 * @param  integer $id
	 * @return Instance|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}

	/**
	 * @param  array $id
	 * @return Instance|null
	 */
	public function findByIdRange(array $id){
		return $this->repository->findById($id);
	}

	/**
	 * @param  string $domain
	 * @return Instance|null
	 */
	public function findByDomain($domain){
		return $this->repository->findOneBy([
			'domain' => $domain !== null ? strtoupper($domain) : null
		]);
	}

	/**
	 * @param  string $token
	 * @return Instance|null
	 */
	public function findByToken($token){
		return $this->repository->findOneBy([
			'token' => $token
		]);
	}

	/**
	 * @param  string $token
	 * @return Instance|null
	 */
	public function findByRegToken($token){
		return $this->repository->findOneBy([
			'registrationToken' => $token
		]);
	}
}