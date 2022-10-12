<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class DirtyWord extends AbstractEntity{
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
	 * @var Translation
	 */
	protected $wordTranslation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->wordTranslation = new Translation;
	}

	/**
	 * @param string $langCode
	 * @param string $value
	 */
	public function setWord($langCode, $value){
		static::$translationService->set($this->wordTranslation, $langCode, $value);
	}

	/**
	 * @param  string $langCode
	 * @return string
	 */
	public function getWord($langCode){
		return static::$translationService->get($this->wordTranslation, $langCode);
	}
}