<?php

namespace Visitares\Entity;

use DateTime;

class Timeline extends AbstractEntity{

	const PUBLISHED_PUBLIC = 0;
	const PUBLISHED_INSTANCE = 0;
	const PUBLISHED_GROUPS = 0;

	protected $id;
	protected $creationDate = null;
	protected $modificationDate = null;

	protected $isActive = false;
	protected $name = null;
	protected $shortDescription = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

	/**
	 * @return string
	 */
	public function getHash(){
		$hash = hash('sha256', $this->getId());
		return substr($hash, 0, 10);
	}

}
