<?php

namespace Visitares\Storage;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Form;
use Visitares\Entity\Instance;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class FormStorage{
	/**
	 * @var EntityManager
	 */
	protected $entityManager = null;

	/**
	 * @var EntityRepository
	 */
	protected $repository = null;

	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager){
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getRepository('Visitares\Entity\Form');
	}

	/**
	 * @param  array $criteria
	 * @return Form|null
	 */
	public function findBy(array $criteria){
		return $this->repository->findBy($criteria);
	}

	/**
	 * @return Form[]
	 */
	public function findAll(){
		$forms = $this->repository->findAll();
		foreach($forms as $form){
			$form->setStatsLocked(true);
		}
		return $forms;
	}

	/**
	 * @param  integer $id
	 * @return Form|null
	 */
	public function findById($id){
		return $this->repository->findOneById((int)$id);
	}

	/**
	 * @param  Form     $form
	 * @param  Instance $instance
	 * @return Form
	 */
	public function prepareForm(Form $form, Instance $instance){
		$dql = '
			SELECT		COUNT(u) as num
			FROM		Visitares\Entity\Group g
			LEFT JOIN	g.users u
			LEFT JOIN	g.categories c
			WHERE		c.id = :cid
				AND		u.anonymous = false
		';
		$query = $this->entityManager->createQuery($dql);
		$query->setParameter('cid', $form->getCategory()->getId());
		$count = (int)$query->getResult()[0]['num'];
		$locked = $count >= $instance->getStatsMinUserCount() ? false : true;
		$form->setStatsLocked($locked);
	}

	/**
	 * @param  Form[] $forms
	 * @return Form[]
	 */
	public function prepareForms(array $forms, Instance $instance){
		foreach($forms as $form){
			$this->prepareForm($form, $instance);
		}
		return $forms;
	}
}