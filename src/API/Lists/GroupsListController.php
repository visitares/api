<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\Group;
use Visitares\Entity\Translated;
use Visitares\Entity\Translation;

class GroupsListController{

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
	 * @param  array $filter
	 * @param  array $sort
	 * @return array
	 */
	public function get(array $filter, array $orderBy, $language, $offset=0, $limit=100){
		session_write_close();
		
		$qb = $this->em->createQueryBuilder();

		$qb->select(implode(', ', [
			'ugroup.id',
			'ugroup.isDefault',
			'ugroup.isDefaultConfig',
			'ugroup.defaultAppScreen',
			't_name.content name',
			't_description.content description'
		]))
			->from(Group::class, 'ugroup')

			->join('ugroup.nameTranslation', 'groupNameTranslation')
			->leftJoin(
				Translated::class,
				't_name',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_name.translationId', 'groupNameTranslation.id'),
					$qb->expr()->eq('t_name.languageId', $language)
				)
			)

			->leftJoin('ugroup.descriptionTranslation', 'groupDescTranslation')
			->leftJoin(
				Translated::class,
				't_description',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_description.translationId', 'groupDescTranslation.id'),
					$qb->expr()->eq('t_description.languageId', $language)
				)
			)
		;

		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'group.id';
			}
			switch($prop){
				case 'group.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('t_name.content', '?' . $i),
						$qb->expr()->like('t_description.content', '?' . $i)
					));
					$params[$i++] = '%' . $value . '%';
					break;

				default:
					$prop = str_replace('group.', 'ugroup.', $prop);
					$qb->andWhere(sprintf('%s = ?%d', $prop, $i));
					$params[$i] = $value;
					break;
			}
			$i++;
		}
		$qb->setParameters($params);

		foreach($orderBy as $prop => $order){
			switch($prop){
				case 'group.name':
					$qb->addOrderBy('t_name.content', $order);
					break;
					
				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		$totalQb = clone $qb;
		$totalQb->select('count(ugroup) total');

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