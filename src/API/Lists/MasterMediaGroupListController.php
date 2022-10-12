<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\SystemStorageFacade;

use Visitares\Entity\MediaGroup;

class MasterMediaGroupListController{

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var EntityManager
	 */
	protected $em = null;

	/**
	 * @param InstanceStorageFacade $storage
	 * @param string $token
	 */
	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $storage->getEntityManager();
	}

	/**
	 * @param  array   $filter
	 * @param  array   $sort
	 * @param  integer $language
	 * @param  integer $offset
	 * @param  integer $limit
	 * @return array
	 */
	public function get(array $filter, array $orderBy, $offset=0, $limit=100){
		session_write_close();
		
		$qb = $this->em->createQueryBuilder();

		$qb->select(implode(', ', [
			'mediagroup.id',
			'mediagroup.label',
			'mediagroup.description'
		]))
			->from(MediaGroup::class, 'mediagroup')
			->distinct()

			->join('mediagroup.master', 'master')
		;

		// where
		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'mediagroup.id';
			}
			switch($prop){
				case 'mediagroup.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('mediagroup.label', '?' . $i),
						$qb->expr()->like('mediagroup.description', '?' . $i)
					));
					$params[$i++] = '%' . $value . '%';
					break;

				default:
					$qb->andWhere(sprintf('%s = ?%d', $prop, $i));
					$params[$i] = $value;
					break;
			}
			$i++;
		}
		$qb->setParameters($params);

		// orderBy
		foreach($orderBy as $prop => $order){
			switch($prop){
				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		// total
		$totalQb = clone $qb;
		$totalQb->select('count(mediagroup) total');

		$qb->setFirstResult($offset);
		$qb->setMaxResults($limit);

		return[
			'rows' => $qb->getQuery()->getArrayResult(),
			'offset' => $offset,
			'limit' => $limit,
			'total' => (int)$totalQb->getQuery()->getSingleScalarResult()
		];
	}

}