<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Translation extends AbstractEntity{
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
	 * @var Translated[]
	 */
	protected $translations = null;

	/**
	 * Initializes the creation date.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->translations = new ArrayCollection;
	}

	/**
	 * @param string $lang
	 * @param string $value
	 */
	public function add(Translated $translation){
		$this->translations[] = $translation;
		$translation->setTranslation($this);
	}
}