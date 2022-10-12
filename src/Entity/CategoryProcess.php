<?php

namespace Visitares\Entity;

use DateTime;

class CategoryProcess extends AbstractEntity{
	protected $id = null;
	protected $creationDate = null;
	protected $modificationDate = null;

	protected $category = null;

	protected $isArchived = false;
	protected $token = null;
	protected $name = null;
	protected $description = null;
	protected $definition = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}
}
