<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Config;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class ConfigStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Config');
	}

	/**
	 * @param  string $name
	 * @return Config|null
	 */
	public function findOneByName($name){
		return $this->repository->findOneBy([
			'name' => $name
		]);
	}
}