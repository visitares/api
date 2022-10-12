<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Input extends AbstractEntity implements FullResolveInterface{
	/**
	 * @var integer
	 */
	protected $id = null;

	/**
	 * @var DateTime
	 */
	protected $creationDate = null;

	/**
	 * @var DateTime
	 */
	protected $modificationDate = null;

	/**
	 * @var Form
	 */
	protected $form = null;

	/**
	 * @var Option[]
	 */
	protected $options = null;

	/**
	 * @var float
	 */
	protected $coefficient = null;

	/**
	 * @var integer
	 */
	protected $unit = null;

	/**
	 * @var integer
	 */
	protected $sort = 1;

	/**
	 * @var bool
	 */
	protected $required = true;

	/**
	 * @var Translation
	 */
	protected $labelTranslation = null;

	/**
	 * @var string
	 */
	protected $type = 'text';

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->options = [];
		$this->labelTranslation = new Translation;
	}

	/**
	 * Clear associations.
	 */
	public function __clone(){
		$this->options = [];
	}

	/**
	 * @param Option $option
	 */
	public function addOption(Option $option){
		if($option->getId()){
			foreach($this->options as $index => $item){
				if($item->getId() === $option->getId()){
					return false;
				}
			}
		}
		$this->options[] = $option;
		$option->setInput($this);
		return true;
	}

	/**
	 * @param  Option $option
	 * @return void
	 */
	public function removeOption(Option $option){
		foreach($this->options as $index => $item){
			if($item->getId() === $option->getId()){
				unset($this->options[$index]);
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $coefficient
	 */
	public function setCoefficient($coefficient){
		if($coefficient !== null){
			if(is_string($coefficient)){
				$coefficient = str_replace(',', '.', $coefficient);
			}
			$this->coefficient = floatval($coefficient);
		} else{
			$this->coefficient = null;
		}
	}

	/**
	 * @param string $langCode
	 * @param string $value
	 */
	public function setLabel($langCode, $value){
		static::$translationService->set($this->labelTranslation, $langCode, $value);
	}

	/**
	 * @param  string $langCode
	 * @return string
	 */
	public function getLabel($langCode){
		return static::$translationService->get($this->labelTranslation, $langCode);
	}
}
