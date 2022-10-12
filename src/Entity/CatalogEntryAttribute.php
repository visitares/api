<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CatalogEntryAttribute extends AbstractEntity{

	/** @var integer */
	protected $id = null;

	/** @var DateTime */
	protected $creationDate = null;

	/** @var DateTime */
	protected $modificationDate = null;

  /** @var Entry */
  protected $entry = null;

  /** @var Attribute */
  protected $attribute = null;

  /** @var boolean */
  protected $isActive = true;

	/** @var Translation */
	protected $valueTranslation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->valueTranslation = new Translation;
	}

	/**
		* @param string $langCode
		* @param string $value
		*/
	public function setValue($langCode, $value){
		static::$translationService->set($this->valueTranslation, $langCode, $value);
	}

  /**
  * @param  string $langCode
  * @return string
  */
  public function getValue($langCode){
    return static::$translationService->get($this->valueTranslation, $langCode);
  }

}