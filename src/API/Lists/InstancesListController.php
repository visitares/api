<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\Instance;
use Visitares\Entity\User;

class InstancesListController{

	/**
	 * @var SystemStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $instanceStorage = null;

	/**
	 * @var EntityManager
	 */
	protected $em = null;

	/**
	 * @param SystemStorageFacade $storage
	 * @param InstanceStorageFacade $instanceStorage
	 */
	public function __construct(
		SystemStorageFacade $storage,
		InstanceStorageFacade $instanceStorage
	){
		$this->storage = $storage;
		$this->instanceStorage = $instanceStorage;
		$this->em = $storage->getEntityManager();
	}

	/**
	 * @param  array $array
	 * @return boolean
	 */
	protected function isAssoc(array $array){
		if(array() === $array){
			return false;
		}
		return array_keys($array) !== range(0, count($array) - 1);
	}

	/**
	 * @param  array $filter
	 * @param  array $sort
	 * @return array
	 */
	public function get(array $filter, array $orderBy, $offset=0, $limit=100){
		session_write_close();
		
		$qb = $this->em->createQueryBuilder();

		$qb->select(implode(', ', [
			'i.id',
			'i.customerNumber',
			'i.name',
			'i.shortDescription',
			'i.domain',
			'i.isActive',
			'i.isTemplate',
			'i.customerNumber',
			'i.country',
			'i.postalCode',
			'i.city',
			'i.street',
			'i.sector',
			'i.usersCountByContract'
		]))
			->from(Instance::class, 'i')
		;

		// where
		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			$prop = str_replace('instance.', 'i.', $prop);
			if($prop === 'id'){
				$prop = 'i.id';
			}

			switch($prop){
				case 'i.id' && is_array($value):
					$qb->andWhere('i.id IN (?' . $i . ')');
					$params[$i] = $value;
					break;

				case 'i.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('i.domain', '?' . $i),
						$qb->expr()->like('i.name', '?' . $i),
						$qb->expr()->like('i.shortDescription', '?' . $i)
					));
					$params[$i] = '%' . $value . '%';
					break;

				default:
					$qb->andWhere(sprintf('%s = ?%d', $prop, $i));
					$params[$i] = $value;
					break;
			}
			$i++;
		}
		$qb->setParameters($params);

		$qb->andWhere($qb->expr()->isNotNull('i.domain'));

		// orderBy
		foreach($orderBy as $prop => $order){
			$prop = str_replace('instance.', 'i.', $prop);

			switch($prop){
				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		// total
		$totalQb = clone $qb;
		$totalQb->select('count(i) total');

		$qb->setFirstResult($offset);
		$qb->setMaxResults($limit);

		return[
			'rows' => array_map(function($row){
				$row['usersCount'] = $this->getUsersCount($row['id']);
				return $row;
			}, $qb->getQuery()->getArrayResult()),
			'offset' => $offset,
			'limit' => $limit,
			'total' => (int)$totalQb->getQuery()->getSingleScalarResult()
		];
	}


	public function getUsersCount($id){
		if($instance = $this->storage->instance->findById($id)){
			$em = $this->instanceStorage->getEntityManager($instance->getToken());
			$queryBuilder = $em->createQueryBuilder();
			$queryBuilder->select('count(u.id)');
			$queryBuilder->from(User::class, 'u');
			return (int)$queryBuilder->getQuery()->getSingleScalarResult();
		}
		return 0;
	}

}