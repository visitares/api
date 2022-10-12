<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Client extends AbstractEntity{
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
	 * @var Category[]
	 */
	protected $categories = null;

	/**
	 * @var Translation
	 */
	protected $nameTranslation = null;

	/**
	 * @var Translation
	 */
	protected $descriptionTranslation = null;

	/**
	 * @var boolean
	 */
	protected $isActive = true;

	/**
	 * @var integer
	 */
	protected $iconId = null;

	/**
	 * @var integer
	 */
	protected $sort = null;

	/**
	 * @var boolean
	 */
	protected $lineBreak = false;

	/**
	 * @var boolean
	 */
	protected $dividingLine = false;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->categories = new ArrayCollection;
		$this->nameTranslation = new Translation;
		$this->descriptionTranslation = new Translation;
	}

	/**
	 * Clear associations.
	 */
	public function __clone(){
		parent::__clone();
		$this->categories = [];
	}

	/**
		* @param string $langCode
		* @param string $value
		*/
	public function setName($langCode, $value){
		static::$translationService->set($this->nameTranslation, $langCode, $value);
	}

 /**
	* @param  string $langCode
	* @return string
	*/
 public function getName($langCode){
	 return static::$translationService->get($this->nameTranslation, $langCode);
 }

 /**
	* @param string $langCode
	* @param string $value
	*/
 public function setDescription($langCode, $value){
	 static::$translationService->set($this->descriptionTranslation, $langCode, $value);
 }

 /**
	* @param  string $langCode
	* @return string
	*/
 public function getDescription($langCode){
	 return static::$translationService->get($this->descriptionTranslation, $langCode);
 }
}
