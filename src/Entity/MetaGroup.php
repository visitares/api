<?php

namespace Visitares\Entity;

use DateTime;

class MetaGroup extends AbstractEntity{

	protected $id = null;

	protected $creationDate = null;
	protected $modificationDate = null;

	protected $name = null;
	protected $description = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

}