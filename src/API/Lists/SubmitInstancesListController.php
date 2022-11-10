<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\User;
use Visitares\Entity\UserSubmitInstance;
use Visitares\Entity\Group;
use Visitares\Entity\Translated;

class SubmitInstancesListController{

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
		InstanceStorageFacade $storage,
		$token
	){
		$this->storage = $storage;
		$this->storage->setToken($token);
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
	 * @param  array   $filter
	 * @param  array   $sort
	 * @param  integer $language
	 * @param  integer $offset
	 * @param  integer $limit
	 * @return array
	 */
	public function get(array $filter, array $orderBy, $language, $offset=0, $limit=100){
		session_write_close();
		
		$qb = $this->em->createQueryBuilder();

		$qb->select(implode(', ', [
			'si.id',
			'si.creationDate',
			'si.modificationDate',
			'si.isDone',
			'si.score',
			'si.name',
			'si.description',
			'si.definition',

			'client.id clientId',
			't_clientName.content clientName',

			'category.id categoryId',
			'category.maxScore',
			't_name.content categoryName',
			't_description.content categoryDescription',

			'user.username',
			'user.firstname',
			'user.lastname',
			'user.company',
			'user.department',
			'user.email',
			'user.phone'
		]))
			->from(UserSubmitInstance::class, 'si')

			->join('si.category', 'category')
			->join('category.nameTranslation', 'categoryNameTranslation')
			->leftJoin(
				Translated::class,
				't_name',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_name.translationId', 'categoryNameTranslation.id'),
					$qb->expr()->eq('t_name.languageId', $language)
				)
			)
			->leftJoin('category.descriptionTranslation', 'categoryDescTranslation')
			->leftJoin(
				Translated::class,
				't_description',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_description.translationId', 'categoryDescTranslation.id'),
					$qb->expr()->eq('t_description.languageId', $language)
				)
			)

			->join('category.client', 'client')
			->leftJoin('client.nameTranslation', 'clientNameTranslation')
			->leftJoin(
				Translated::class,
				't_clientName',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_clientName.translationId', 'clientNameTranslation.id'),
					$qb->expr()->eq('t_clientName.languageId', $language)
				)
			)

			->join('si.user', 'user')
		;

		if(isset($filter['group.id'])){
			$qb->leftJoin('user.groups', 'g', $qb->expr()->eq('g.id', $filter['group.id']));
			$qb->andWhere(sprintf('g.id = %d', $filter['group.id']));
			unset($filter['group.id']);
		}

		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'user.id';
			}
			switch($prop){
				case 'text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('user.firstname', '?' . $i),
						$qb->expr()->like('user.lastname', '?' . $i),
						$qb->expr()->like('user.email', '?' . $i),
						$qb->expr()->like('t_name.content', '?' . $i),
						$qb->expr()->like('si.name', '?' . $i),
						$qb->expr()->like('si.description', '?' . $i),
						$qb->expr()->like('si.definition', '?' . $i)
					));
					$params[$i++] = '%' . $value . '%';
					break;

				case 'creationDate.from':
					$qb->andWhere(sprintf('si.creationDate >= ?%d', $i));
					$params[$i] = $value ? (date('Y-m-d', strtotime($value)) . ' 00:00:00') : null;
					break;
				case 'creationDate.to':
					$qb->andWhere(sprintf('si.creationDate <= ?%d', $i));
					$params[$i] = $value ? (date('Y-m-d', strtotime($value)) . ' 66:66:66') : null;
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
				case 'client.name':
					$qb->addOrderBy('t_clientName.content', $order);
					break;
					
				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		$totalQb = clone $qb;
		$totalQb->select('count(si) total');

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
