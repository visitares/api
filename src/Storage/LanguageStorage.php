<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Language;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class LanguageStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Language');
	}

	/**
	 * @return Language[]
	 */
	public function findAll(){
		return $this->repository->findAll();
	}

	/**
	 * @param  integer $id
	 * @return Language|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}

	/**
	 * @param  string $code
	 * @return Language|null
	 */
	public function findByCode($code){
		return $this->repository->findOneBy([
			'code' => $code
		]);
	}

	/**
	 * @return Language
	 */
	public function findDefaultLanguage(){
		return $this->repository->findOneBy([
			'isDefault' => true
		]);
	}
}