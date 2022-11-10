<?php

namespace Visitares\API\Lists;

use PDO;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\User;
use Visitares\Entity\Group;

use Visitares\Util;

class UsersListController{

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var EntityManager
	 */
	protected $em = null;

	/**
	 * @var \Doctrine\DBAL\Driver\Connection
	 */
	protected $pdo = null;

	/**
	 * @param InstanceStorageFacade $storage
	 * @param string $token
	 */
	public function __construct(
		InstanceStorageFacade $storage,
		string $token
	){
		$this->storage = $storage;
		$this->storage->setToken($token);
		$this->em = $storage->getEntityManager();
		$this->pdo = $this->em->getConnection()->getWrappedConnection();
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
	 * @param  array   $filter
	 * @param  array   $sort
	 * @param  integer $language
	 * @param  integer $offset
	 * @param  integer $limit
	 * @return array
	 */
	public function get(array $filter, array $orderBy, $language, $offset=0, $limit=100){

		$sql = file_get_contents(__DIR__ . '/sql/users-list.sql');

		$joins = $this->createJoins($filter);
		list($where, $having, $params) = $this->createWhere($filter);

		$sql = Util::replace($sql, [
			'{columns}' => $this->createColumns($filter),
			'{joins}' => $this->createJoins($filter),
			'{where}' => $where,
			'{having}' => $having,
			'{orderBy}' => 'user.firstname ASC',
			'{offset}' => $offset,
			'{limit}' => $limit
		]);

		$query = $this->pdo->prepare($sql);
		$res = $query->execute($params);
		$rows = $res->fetchAllAssociative();

		$totalSql = $this->createTotalSql($sql);
		$query = $this->pdo->prepare($totalSql);
		$res = $query->execute($params);
		$totalRows = $res->fetchAllAssociative();

		return[
			'rows' => array_map(function($row){
				$row['isActive'] = boolval(intval($row['isActive'] ?? 0));
				return Util::removeKeys($row, ['groupId']);
			}, $rows),
			'total' => (int)$totalRows[0]['total'],
			'offset' => $offset,
			'limit' => $limit,
		];
	}

	/**
	 * @param  string $sql
	 * @return string
	 */
	protected function createTotalSql($sql){
		$totalSql = file_get_contents(__DIR__ . '/sql/total.sql');
		$sql = preg_replace('/LIMIT \d+, \d+/', '', $sql);
		return Util::replace($totalSql, [
			'{query}' => $sql
		]);
	}

	/**
	 * @param  array $filter
	 * @return array
	 */
	protected function createColumns(array $filter){
		$columns = [
			'user.id',
			'user.firstname',
			'user.lastname',
			'user.username',
			'user.role',
			'user.email',
			'user.phone',
			'user.company',
			'user.department',
			'user.isActive',
			'user.anonymousToken',
			'user.activeFrom',
			'user.activeUntil',
			'user.lastLogin',
		];
		if(isset($filter['group.id']) && isset($filter['group.id$']) && $filter['group.id$']){
			$columns[] = 'g.id groupId';
		}
		return implode(', ', $columns);
	}

	/**
	 * @param  array $filter
	 * @return string
	 */
	protected function createJoins(array $filter){
		$joins = [];
		if(isset($filter['group.id'])){
			$joins[] = ' LEFT JOIN group_user gu ON gu.user_id = user.id ';
			$joins[] = ' LEFT JOIN usergroup g ON g.id = gu.group_id ';
		}
		return implode(PHP_EOL, $joins);
	}

	/**
	 * @param  array $filter
	 * @return array
	 */
	protected function createWhere(array $filter){
		$where = [];
		$having = '';
		$params = [];
		$param = 1;

		// create where
		foreach($filter as $field => $value){
			if($field === 'id'){
				$field = 'user.id';
			}
			$pkey = ':p' . $param;

			if(strpos($field, '$') !== false){
				continue;
			}

			switch(true){
				case $field === 'group.id':
					if(isset($filter['group.id$']) && $filter['group.id$']){
						continue 2;
					}
					$where[] = sprintf('g.id = %s', $pkey);
					break;

				case $field === 'user.text':
					$where[] = '(' . implode(' OR ', [
						sprintf('user.username LIKE %s', $pkey),
						sprintf('user.firstname LIKE %s', $pkey),
						sprintf('user.lastname LIKE %s', $pkey),
						sprintf('user.company  LIKE %s', $pkey),
						sprintf('user.department LIKE %s', $pkey),
						sprintf('user.email LIKE %s', $pkey),
						sprintf('user.anonymousToken LIKE %s', $pkey),
					]) . ')';
					$value = '%' . $value . '%';
					break;

				case is_bool($value):
					$value = (int)$value;
					$where[] = sprintf('%s = %s', $field, $pkey);
					break;

				default:
					$where[] = sprintf('%s = %s', $field, $pkey);
			}

			$params[$pkey] = $value;
			$param++;
		}

		// create having
		if(isset($filter['group.id']) && isset($filter['group.id$']) && $filter['group.id$']){
			$pkey = $pkey = ':p' . $param;
			$having = ' HAVING COUNT(g.id) = 1 AND g.id = ' . $pkey;
			$params[$pkey] = $filter['group.id'];
		}

		return[ $where ? implode(' AND ', $where) : ' TRUE ', $having, $params ];
	}






	/**
	 * @param  array   $filter
	 * @param  array   $sort
	 * @param  integer $language
	 * @param  integer $offset
	 * @param  integer $limit
	 * @return array
	 */
	public function _get(array $filter, array $orderBy, $language, $offset=0, $limit=100){
		session_write_close();

		$qb = $this->em->createQueryBuilder();

		$select = [
			'user.id',
			'user.firstname',
			'user.lastname',
			'user.username',
			'user.role',
			'user.email',
			'user.phone',
			'user.company',
			'user.department',
			'user.isActive',
			'user.anonymousToken',
			'user.activeFrom',
			'user.activeUntil',
		];

		$qb->distinct()->from(User::class, 'user');

		if(isset($filter['group.id'])){
			$qb->leftJoin('user.groups', 'g', $qb->expr()->eq('g.id', $filter['group.id']));
			$select[] = 'g.id';
			if(isset($filter['group.id$strict']) && $filter['group.id$strict']){
				$qb->having(sprintf('COUNT(g.id) = 1 AND g.id = %d', $filter['group.id']));
			} else{
				$qb->andWhere(sprintf('g.id = %d', $filter['group.id']));
			}
			unset($filter['group.id']);
		}

		$qb->select(implode(', ', $select));


		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'user.id';
			}
			if($prop === 'group.id$strict'){
				continue;
			}
			switch($prop){
				case 'user.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('user.username', '?' . $i),
						$qb->expr()->like('user.firstname', '?' . $i),
						$qb->expr()->like('user.lastname', '?' . $i),
						$qb->expr()->like('user.company', '?' . $i),
						$qb->expr()->like('user.department', '?' . $i),
						$qb->expr()->like('user.email', '?' . $i),
						$qb->expr()->like('user.anonymousToken', '?' . $i)
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

		foreach($orderBy as $prop => $order){
			switch($prop){
				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		$totalQb = clone $qb;

		if(isset($filter['group.id']) && isset($filter['group.id$strict']) && $filter['group.id$strict']){
			$totalQb->select('count(user) total, g.id');
			$totalQb->groupBy('user.id');
		} else{
			$totalQb->select('count(user) total');
		}

		$total = $totalQb->getQuery()->getArrayResult();

		$qb->groupBy('user.id');
		$qb->setFirstResult($offset);
		$qb->setMaxResults($limit);

		return[
			'rows' => $qb->getQuery()->getArrayResult(),
			'offset' => $offset,
			'limit' => $limit,
			'total' => isset($total[0]) ? (int)$total[0]['total'] : 0,
		];
	}

}
