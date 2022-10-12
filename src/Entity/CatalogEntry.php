<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CatalogEntry extends AbstractEntity{

	/** @var integer */
	protected $id = null;

	/** @var DateTime */
	protected $creationDate = null;

	/** @var DateTime */
	protected $modificationDate = null;

  /** @var Catalog */
	protected $catalog = null;
	
	/** @var CatalogEntryAttribute[] */
	protected $attributes = null;

	/** @var Translation */
	protected $nameTranslation = null;

	/** @var Translation */
	protected $descriptionTranslation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->attributes = new ArrayCollection;
		$this->nameTranslation = new Translation;
		$this->descriptionTranslation = new Translation;
	}

	/**
	 * Clear associations.
	 */
	public function __clone(){
		parent::__clone();
		$this->attributes = [];
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