<?php

namespace Visitares\Service;

use Doctrine\ORM\EntityManager;
use Visitares\Entity\Form;
use Visitares\Entity\FormAdmin;
use Visitares\Entity\User;

class FormAdminService{
	/**
	 * @var EntityManager
	 */
	protected $em = null;

	/**
	 * @var Repository
	 */
	protected $formAdmins = null;

	/**
	 * @var Repository
	 */
	protected $users = null;

	/**
	 * @param EntityManager $em
	 */
	public function setEntityManager(EntityManager $em){
		$this->em = $em;
		$this->formAdmins = $em->getRepository(FormAdmin::class);
		$this->users = $em->getRepository(User::class);
	}

	/**
	 * @param  Form    $form
	 * @param  integer $role
	 * @return FormAdmin[]
	 */
	public function getAdmins(Form $form, $role){
		return $this->formAdmins->findBy([
			'form' => $form,
			'role' => $role
		]);
	}

	/**
	 * @param Form  $form 
	 * @param int[] $userIds
	 * @param int   $role
	 */
	public function setAdmins(Form $form, array $userIds, $role){
		$formAdmins = $this->getAdmins($form, $role);

		// Remove all admins if form type is text
		if($form->getType() !== Form::TYPE_TEXT){
			$userIds = [];
		}

		// Create array with form admin user ids
		$formAdminUserMap = [];
		foreach($formAdmins as $formAdmin){
			$formAdminUserMap[$formAdmin->getUser()->getId()] = $formAdmin;
		}

		// Remove users
		foreach($formAdminUserMap as $id => $formAdmin){
			if(!in_array($id, $userIds)){
				$this->em->remove($formAdmin);
			}
		}

		// Add users
		$userIds = array_unique($userIds);
		foreach($userIds as $id){
			if(!array_key_exists($id, $formAdminUserMap)){
				$formAdmin = new FormAdmin;
				$formAdmin->setUser($this->em->getReference(User::class, $id));
				$formAdmin->setForm($form);
				$formAdmin->setRole($role);
				$this->em->persist($formAdmin);
			}
		}

		// It's done!
		$this->em->flush();
	}

	/**
	 * @param  Form    $form
	 * @param  integer $role
	 * @return void
	 */
	public function clearAdmins(Form $form, $role){
		$formAdmins = $this->getAdmins($form, $role);
		array_map(function($formAdmin){
			$this->em->remove($formAdmin);
		}, $formAdmins);
		$this->em->flush();
	}
}