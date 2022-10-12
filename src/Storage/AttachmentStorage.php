<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Attachment;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class AttachmentStorage{
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
		$this->repository = $entityManager->getRepository('Visitares\Entity\Attachment');
	}

	/**
	 * @param  integer $id
	 * @return Attachment|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}
}