<?php

namespace Visitares\Service\SubmitInstance;

use Visitares\Entity\Form;
use Visitares\Entity\UserSubmitInstance;
use Visitares\Storage\Facade\InstanceStorageFacade;

class IsDoneService{

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(
		InstanceStorageFacade $storage
	){
		$this->storage = $storage;
	}

	/**
	 * @param  UserSubmitInstance $submitInstance
	 * @return void
	 */
	public function updateIsDone(UserSubmitInstance $submitInstance){
		$isDone = false;
		$submittedForms = [];

		$forms = $this->storage->form->findBy([
			'category' => $submitInstance->getCategory()->getId()
		]);
		foreach($forms as $form){
			if(in_array($form->getType(), [
				Form::TYPE_DOCUMENTS,
				Form::TYPE_MEDIA,
				Form::TYPE_URL,
				Form::TYPE_HEADLINE,
				Form::TYPE_HEADLINE_CATALOG,
				Form::TYPE_HTML_TEXT,
			])){
				$submittedForms[$form->getId()] = true;
			}
			if($form->getType() === Form::TYPE_DOCUMENTS || $form->getType() === Form::TYPE_MEDIA){
				
			}
		}

		$submits = $this->storage->submit->findBy([
			'submitInstance' => $submitInstance->getId()
		]);
		foreach($submits as $submit){
			$submittedForms[$submit->getForm()->getId()] = true;
		}

		if(count($forms) === count(array_keys($submittedForms))){
			$isDone = true;
		}

		$submitInstance->setIsDone($isDone);
		$this->storage->apply();
	}

}