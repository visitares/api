<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Input;
use Visitares\Entity\Translation;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class InputFactory{

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var OptionFactory
	 */
	protected $optionFactory = null;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(
		InstanceStorageFacade $storage,
		OptionFactory $optionFactory
	){
		$this->storage = $storage;
		$this->optionFactory = $optionFactory;
	}

	/**
	 * @param  array $values
	 * @return Input
	 */
	public function fromArray(array $values){
		$input = new Input;
		if($form = $this->storage->form->findById($values['form'])){
			$input->setForm($form);
		}
		$input->setRequired($values['required']);
		$input->setSort($values['sort']);
		$input->setCoefficient($values['coefficient']);
		if($values['unit']){
			$unit = $this->storage->getReference('Visitares\Entity\Unit', $values['unit']);
			$input->setUnit($unit);
		}
		foreach($values['label'] as $langCode => $value){
			$input->setLabel($langCode, $value);
		}
		$input->setType($values['type'] ?? 'text');
		return $input;
	}

}
