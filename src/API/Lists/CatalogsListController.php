<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\Catalog;
use Visitares\Entity\Translated;
use Visitares\Entity\Translation;

class CatalogsListController{

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
	 * @param array $filter
	 * @param array $orderBy
	 * @param integer $language
	 * @param integer $offset
	 * @param integer $limit
	 * @return array
	 */
	public function get(array $filter = [], array $orderBy = [], $language = 1, $offset=0, $limit=100){
		session_write_close();

		if(!$orderBy){
			$orderBy = [
				't_name.content' => 'ASC',
			];
		}
		
		$qb = $this->em->createQueryBuilder();

		$qb->select(implode(', ', [
			'catalog.id',
			'catalog.creationDate',
			'catalog.modificationDate',
			'catalog.allowInstructions',
			't_name.content name',
			't_description.content description',
		]))
			->from(Catalog::class, 'catalog')

			->leftJoin('catalog.nameTranslation', 'catalogNameTranslation')
			->leftJoin(
				Translated::class,
				't_name',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_name.translationId', 'catalogNameTranslation.id'),
					$qb->expr()->eq('t_name.languageId', $language)
				)
			)
			
			->leftJoin('catalog.descriptionTranslation', 'catalogDescriptionTranslation')
			->leftJoin(
				Translated::class,
				't_description',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_description.translationId', 'catalogDescriptionTranslation.id'),
					$qb->expr()->eq('t_description.languageId', $language)
				)
			)
		;

		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'catalog.id';
			}
			switch($prop){
				case 'catalog.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('t_name.content', '?' . $i),
						$qb->expr()->like('t_description.content', '?' . $i)
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
				case 'catalog.name':
					$qb->addOrderBy('t_name.content', $order);
					break;

				case 'catalog.description':
					$qb->addOrderBy('t_description.content', $order);
					break;

				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		$totalQb = clone $qb;
		$totalQb->select('count(catalog) total');

		$qb->setFirstResult($offset);
		$qb->setMaxResults($limit);

		// append entries count
		$qb
			->leftJoin('catalog.entries', 'entries')
			->addSelect('COUNT(entries) AS countEntries')
			->groupBy('catalog.id');

		return[
			'rows' => $qb->getQuery()->getArrayResult(),
			'offset' => $offset,
			'limit' => $limit,
			'total' => (int)$totalQb->getQuery()->getSingleScalarResult()
		];
	}

}
