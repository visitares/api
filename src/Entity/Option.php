<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Option extends AbstractEntity implements FullResolveInterface{
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
	 * @var Input
	 */
	protected $input = null;

	/**
	 * @var float
	 */
	protected $coefficient = null;

	/**
	 * @var int
	 */
	protected $sort = 1;

	/**
	 * @var Translation
	 */
	protected $labelTranslation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->labelTranslation = new Translation;
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