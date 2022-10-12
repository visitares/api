<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CatalogAttribute extends AbstractEntity{

  const TYPE_TEXT = 0;
  const TYPE_NUMBER = 1;
  const TYPE_DATE = 2;
  const TYPE_DATETIME = 3;
  const TYPE_HREF = 4;

	/** @var integer */
	protected $id = null;

	/** @var DateTime */
	protected $creationDate = null;

	/** @var DateTime */
	protected $modificationDate = null;

  /** @var Catalog */
  protected $catalog = null;

  /** @var int */
  protected $position = 0;

  /** @var int */
  protected $type = self::TYPE_TEXT;

	/** @var Translation */
	protected $nameTranslation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->nameTranslation = new Translation;
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

}