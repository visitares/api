<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\Catalog;
use Visitares\Entity\CatalogEntry;
use Visitares\Entity\CatalogEntryAttribute;
use Visitares\Entity\Translated;
use Visitares\Entity\Translation;

class CatalogsEntriesListController{

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
	public function get(array $filter, array $orderBy, $language = 1, $offset=0, $limit=100){
		session_write_close();

		if(!$orderBy){
			$orderBy = [
				't_entry_name.content' => 'ASC',
				'catalog_entry.id' => 'ASC',
			];
		}
		
		$qb = $this->em->createQueryBuilder();

		$qb->select(implode(', ', [
      'catalog.id catalogId',
      't_catalog_name.content catalogName',
      't_catalog_description.content catalogDescription',
      'catalog_entry.id entryId',
			't_entry_name.content entryName',
			't_entry_description.content entryDescription',
		]))
			->from(CatalogEntry::class, 'catalog_entry')

			->leftJoin('catalog_entry.catalog', 'catalog')
			
			->leftJoin('catalog_entry.attributes', 'attribute')
			->leftJoin('attribute.valueTranslation', 'attributeValueTranslation')
			->leftJoin(
				Translated::class,
				't_attr_value',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_attr_value.translationId', 'attributeValueTranslation.id'),
					$qb->expr()->eq('t_attr_value.languageId', $language)
				)
      )
      
			->leftJoin('catalog_entry.nameTranslation', 'catalogEntryNameTranslation')
			->leftJoin(
				Translated::class,
				't_entry_name',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_entry_name.translationId', 'catalogEntryNameTranslation.id'),
					$qb->expr()->eq('t_entry_name.languageId', $language)
				)
      )
      
			->leftJoin('catalog_entry.descriptionTranslation', 'catalogEntryDescriptionTranslation')
			->leftJoin(
				Translated::class,
				't_entry_description',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_entry_description.translationId', 'catalogEntryDescriptionTranslation.id'),
					$qb->expr()->eq('t_entry_description.languageId', $language)
				)
      )
      
			->leftJoin('catalog.nameTranslation', 'catalogNameTranslation')
			->leftJoin(
				Translated::class,
				't_catalog_name',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_catalog_name.translationId', 'catalogNameTranslation.id'),
					$qb->expr()->eq('t_catalog_name.languageId', $language)
				)
      )
      
			->leftJoin('catalog.descriptionTranslation', 'catalogDescriptionTranslation')
			->leftJoin(
				Translated::class,
				't_catalog_description',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_catalog_description.translationId', 'catalogDescriptionTranslation.id'),
					$qb->expr()->eq('t_catalog_description.languageId', $language)
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
				case 'catalog.name':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('t_catalog_name.content', '?' . $i),
						$qb->expr()->like('t_catalog_description.content', '?' . $i)
					));
					$params[$i++] = '%' . $value . '%';
					break;

				case 'entry.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('t_entry_name.content', '?' . $i),
						$qb->expr()->like('t_entry_description.content', '?' . $i),
						$qb->expr()->like('t_attr_value.content', '?' . $i)
					));
					$params[$i++] = '%' . $value . '%';
					break;

        case 'entry.name':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('t_entry_name.content', '?' . $i)
					));
					$params[$i++] = '%' . $value . '%';
          break;

				case 'entry.description':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('t_entry_description.content', '?' . $i)
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
					$qb->addOrderBy('t_catalog_name.content', $order);
					break;

				case 'catalog_entry.name':
					$qb->addOrderBy('t_entry_name.content', $order);
					break;

				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		$qb->groupBy('catalog_entry.id');

		$totalQb = clone $qb;
		$totalQb->select('catalog_entry.id');

		$qb->setFirstResult($offset);
		$qb->setMaxResults($limit);

		$rows = $qb->getQuery()->getArrayResult();

		// add attributes
		$rows = array_map(function($row){
			$row['attributes'] = $this->em
				->getRepository(CatalogEntryAttribute::class)
				->findBy([
					'entry' => $row['entryId'],
				]);
			return $row;
		}, $rows);

		return[
			'rows' => $rows,
			'offset' => $offset,
			'limit' => $limit,
			'total' => count($totalQb->getQuery()->getArrayResult()),
		];
	}

}
