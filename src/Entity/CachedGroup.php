<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CachedGroup extends AbstractEntity{

	protected $id = null;
	protected $creationDate = null;
	protected $modificationDate = null;

	protected $instance = null;
	protected $groupId = null;

	protected $name = null;
	protected $description = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}

	/**
	 * @return array
	 */
	public function getName(){
		return $this->name ? json_decode($this->name, true) : null;
	}

	/**
	 * @param array $name
	 */
	public function setName(array $name){
		$this->name = json_encode($name);
	}

	/**
	 * @return array
	 */
	public function getDescription(){
		return $this->description ? json_decode($this->description, true) : null;
	}

	/**
	 * @param array $description
	 */
	public function setDescription(array $description){
		$this->description = json_encode($description);
	}
}