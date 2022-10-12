<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Unit extends AbstractEntity{
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
	 * @var string
	 */
	protected $label = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}
}