<?php

namespace Visitares\Service\Media;

use Visitares\Entity\Form;
use Visitares\Entity\FormMedia;
use Visitares\Entity\Media;
use Visitares\Storage\Facade\InstanceStorageFacade;

class MediaFormService{

	/** @var InstanceStorageFacade */
	private $storage = null;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(
		InstanceStorageFacade $storage
	){
		$this->storage = $storage;
	}

	/**
	 * @param  Form $form
	 * @return FormMedia[]
	 */
	protected function getFormMediaRelations(Form $form){
		$repo = $this->storage->getRepository(FormMedia::class);
		return $repo->findBy([
			'form' => $form
		]);
	}

	/**
	 * @param  Form $form
	 * @return Form
	 */
	public function removeAllMediaRelations(Form $form){
		$relations = $this->getFormMediaRelations($form);
		if($relations){
			foreach($relations as $relation){
				$this->storage->remove($relation);
			}
			$this->storage->apply();
		}
		return $form;
	}

	/**
	 * @param  Form  $form
	 * @param  array $ids
	 * @return Form
	 */
	public function updateMediaRelations(Form $form, array $ids){
		$relations = $this->getFormMediaRelations($form);

		// if no ids given, simply remove all relations
		if(!$ids){
			$this->removeAllMediaRelations($form);
			return $form;
		}

		// 1. remove old relations
		$performApply = false;
		$oldIds = [];
		foreach($relations as $relation){
			if(!in_array($relation->getMedia()->getId(), $ids)){
				$this->storage->remove($relation);
				$performApply = true;
			} else{
				$oldIds[] = $relation->getMedia()->getId();
			}
		}
		if($performApply){
			$this->storage->apply();
		}

		// 2. add new relations
		$performApply = false;
		foreach($ids as $id){
			if(!in_array($id, $oldIds)){
				$formMedia = new FormMedia;
				$formMedia->setForm($form);
				$formMedia->setMedia( $this->storage->getReference(Media::class, $id) );
				$this->storage->store($formMedia);
				$performApply = true;
			}
		}
		if($performApply){
			$this->storage->apply();
		}

		return $form;
	}

}