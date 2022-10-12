<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\SystemStorageFacade;

use Visitares\Entity\MasterMedia;

class MasterMediaListController{

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
			'media.id',
			'master.id as masterId',
			'mediagroup.id as groupId',
			'media.label',
			'media.description',
			'media.type',
			'media.mime',
			'media.filename',
			'media.filesize',
			'media.length',
			'media.originalFilename',
			'media.ext'
		]))
			->from(MasterMedia::class, 'media')
			->distinct()

			->join('media.master', 'master')
			->join('media.group', 'mediagroup')
		;

		// where
		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'media.id';
			}
			switch($prop){
				case 'media.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('media.label', '?' . $i),
						$qb->expr()->like('media.description', '?' . $i)
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
		$totalQb->select('count(media) total');

		$qb->setFirstResult($offset);
		$qb->setMaxResults($limit);

		return[
			'rows' => array_map(function($row){
				if($row['description']){
					$row['description'] = json_decode($row['description']);
				}
				return $row;
			}, $qb->getQuery()->getArrayResult()),
			'offset' => $offset,
			'limit' => $limit,
			'total' => (int)$totalQb->getQuery()->getSingleScalarResult()
		];
	}

}