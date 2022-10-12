<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Master;
use Visitares\Entity\Instance;
use Visitares\Entity\MasterInstance;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class MasterStorage{
	/**
	 * @var EntityManager
	 */
	protected $em = null;

	/**
	 * @var EntityRepository
	 */
	protected $masters = null;

	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em){
		$this->em = $em;
		$this->masters = $em->getRepository(Master::class);
		$this->masterInstances = $em->getRepository(MasterInstance::class);
	}

	/**
	 * @param  array $criteria
	 * @param  array $orderBy
	 * @return array
	 */
	public function findBy(array $criteria, array $orderBy = []){
		return $this->masters->findBy($criteria, $orderBy);
	}

	/**
	 * @param  Instance $instance
	 * @return Master
	 */
	public function findByInstance(Instance $instance){
		$masterInstanceJoin = $this->masterInstances->findOneBy([
			'instance' => $instance
		]);
		return $masterInstanceJoin ? $masterInstanceJoin->getMaster() : null;
	}
}