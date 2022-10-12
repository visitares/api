<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Option;
use Visitares\Entity\Translation;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class OptionFactory{
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
	 * @return Option
	 */
	public function fromArray(array $values){
		$option = new Option;
		$option->setSort($values['sort']);
		$option->setCoefficient($values['coefficient']);
		foreach($values['label'] as $langCode => $value){
			$option->setLabel($langCode, $value);
		}
		return $option;
	}
}
