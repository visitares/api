<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Translated extends AbstractEntity{
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
	 * @var Language
	 */
	protected $language = null;

	/**
	 * @var integer
	 */
	protected $languageId = null;

	/**
	 * @var Translation
	 */
	protected $translation = null;

	/**
	 * @var integer
	 */
	protected $translationId = null;

	/**
	 * @var string
	 */
	protected $content = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return $this->content;
	}
}