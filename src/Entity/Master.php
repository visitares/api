<?php

namespace Visitares\Entity;

use DateTime;

class Master extends AbstractEntity{

	protected $id;
	protected $creationDate = null;
	protected $modificationDate = null;

	protected $isActive = false;
	protected $name = null;
	protected $shortDescription = null;
	protected $description = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

}