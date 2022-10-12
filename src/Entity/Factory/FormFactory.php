<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Form;
use Visitares\Entity\Translation;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class FormFactory{
	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(InstanceStorageFacade $storage){
		$this->storage = $storage;
	}

	/**
	 * @param  array $values
	 * @return Form
	 */
	public function fromArray(array $values){
		$form = new Form;
		if($category = $this->storage->category->findById($values['category'])){
			$form->setCategory($category);
		}
		$form->setIsActive($values['isActive']);
		$form->setType($values['type']);
		$form->setSort($values['sort']);
		$form->setPublicStats($values['publicStats']);
		$form->setMaxScore($values['maxScore']);
		$form->setUrl($values['url']);
		$form->setEmbedUrl($values['embedUrl']);

		if(!isset($values['singleSubmitOnly'])){
			$form->setSingleSubmitOnly(false);
		} else{
			$form->setSingleSubmitOnly($values['singleSubmitOnly']);
		}

		foreach($values['name'] as $langCode => $value){
			$form->setName($langCode, $value);
		}
		foreach($values['description'] as $langCode => $value){
			$form->setDescription($langCode, $value);
		}
		foreach($values['shortDescription'] as $langCode => $value){
			$form->setShortDescription($langCode, $value);
		}
		foreach($values['htmlText'] ?? [] as $langCode => $value){
			$form->setHtmlText($langCode, $value);
		}
		return $form;
	}
}