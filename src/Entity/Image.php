<?php

namespace Visitares\Entity;

use DateTime;

class Image extends AbstractEntity{
	protected $id = null;
	protected $creationDate = null;
	protected $modificationDate = null;
	protected $groupId = null;
	protected $filename = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}
}