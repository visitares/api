<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\SystemStorageFacade;

use Visitares\Entity\MetaGroup;

class MetaGroupListController{

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
			'metaGroup.id',
			'metaGroup.name',
			'metaGroup.description'
		]))
			->from(MetaGroup::class, 'metaGroup')
			->distinct()
		;

		// where
		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'metaGroup.id';
			}
			switch($prop){
				case 'metaGroup.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('metaGroup.name', '?' . $i),
						$qb->expr()->like('metaGroup.shortDescription', '?' . $i)
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
		$totalQb->select('count(metaGroup) total');

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