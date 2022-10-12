<?php

namespace Visitares\API\Lists;

use Doctrine\ORM\EntityManager;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\Client;
use Visitares\Entity\Category;
use Visitares\Entity\Form;
use Visitares\Entity\Translated;
use Visitares\Entity\Translation;

class FormsListController{

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
			'client.id clientId',
			't_clientName.content clientName',

			'category.id categoryId',
			't_c_name.content categoryName',
			'form.id id',
			't_name.content name',
			't_shortDescription.content shortDescription',
			't_description.content description',
			'form.isActive',
			'form.sort'
		]))
			->distinct()
			->from(Form::class, 'form')

			->leftJoin('form.category', 'category')
			
			->leftJoin('category.client', 'client')
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

			->leftJoin('form.nameTranslation', 'formNameTranslation')
			->leftJoin(
				Translated::class,
				't_name',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_name.translationId', 'formNameTranslation.id'),
					$qb->expr()->eq('t_name.languageId', $language)
				)
			)

			->leftJoin('form.shortDescriptionTranslation', 'formShortDescTranslation')
			->leftJoin(
				Translated::class,
				't_shortDescription',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_shortDescription.translationId', 'formShortDescTranslation.id'),
					$qb->expr()->eq('t_shortDescription.languageId', $language)
				)
			)

			->leftJoin('form.descriptionTranslation', 'formDescTranslation')
			->leftJoin(
				Translated::class,
				't_description',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_description.translationId', 'formDescTranslation.id'),
					$qb->expr()->eq('t_description.languageId', $language)
				)
			)

			->join('category.nameTranslation', 'categoryNameTranslation')
			->leftJoin(
				Translated::class,
				't_c_name',
				'WITH',
				$qb->expr()->andX(
					$qb->expr()->eq('t_c_name.translationId', 'categoryNameTranslation.id'),
					$qb->expr()->eq('t_c_name.languageId', $language)
				)
			)
		;

		$i = 1;
		$params = [];
		foreach($filter as $prop => $value){
			if($prop === 'id'){
				$prop = 'form.id';
			}
			switch($prop){
				case 'form.text':
					$qb->andWhere($qb->expr()->orX(
						$qb->expr()->like('t_name.content', '?' . $i),
						$qb->expr()->like('t_shortDescription.content', '?' . $i),
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
				case 'category.name':
					$qb->addOrderBy('t_c_name.content', $order);
					break;

				case 'client.name':
					$qb->addOrderBy('t_clientName.content', $order);
					break;
					
				default:
					$qb->addOrderBy($prop, $order);
			}
		}

		$totalQb = clone $qb;
		$totalQb->select('count(form) total');

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
