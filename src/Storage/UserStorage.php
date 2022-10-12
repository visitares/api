<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\User;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class UserStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\User');
	}

	/**
	 * @return User[]
	 */
	public function findAll(){
		return $this->repository->findAll();
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
	 * @param array $criteria
	 * 
	 * @return User[]
	 */
	public function findOne(array $criteria = []){
		return $this->repository->findOneBy($criteria);
	}

	/**
	 * @param  integer $id
	 * @return User|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}

	/**
	 * @param  string $username
	 * @return User|null
	 */
	public function findByUsername($username){
		return $this->repository->findOneBy([
			'username' => $username
		]);
	}

	/**
	 * @param  string $email
	 * @return User|null
	 */
	public function findByEmail($email){
		return $this->repository->findOneBy([
			'email' => $email
		]);
	}

	/**
	 * @param  string $username
	 * @param  array $roles
	 * @return User|null
	 */
	public function findByUsernameAndRole($username, array $roles){
		return $this->repository->findOneBy([
			'username' => $username,
			'role' => $roles
		]);
	}

	/**
	 * @param  string $token
	 * @param  array $roles
	 * @return User|null
	 */
	public function findByToken($token, array $roles){
		return $this->repository->findOneBy([
			'anonymousToken' => $token,
			'role' => $roles
		]);
	}
}