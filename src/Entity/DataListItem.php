<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class DataListItem extends AbstractEntity{
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
	protected $name = null;

	/**
	 * @var value
	 */
	protected $value = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}

	/**
	 * @return array
	 */
	public function getValue(){
		return json_decode($this->value);
	}

	/**
	 * @param array $value
	 */
	public function setValue($value){
		$this->value = json_encode($value);
	}
}