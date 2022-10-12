<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Image;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class ImageStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Image');
	}

	/**
	 * @param array $criteria
	 * @return Image[]
	 */
	public function find(array $criteria = []){
		return $this->repository->findBy($criteria);
	}

	/**
	 * @param  integer $id
	 * @return Image|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}
}