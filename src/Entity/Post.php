<?php

namespace Visitares\Entity;

use DateTime;

class Post extends AbstractEntity{

	const PUBLISHED_PRIVATE = 0;
	const PUBLISHED_PUBLIC = 1;
	const PUBLISHED_INSTANCE = 2;
	const PUBLISHED_GROUPS = 3;
	const PUBLISHED_TIMELINE = 4;
	const PUBLISHED_METAGROUPS = 5;

	protected $id;
	protected $creationDate = null;
	protected $modificationDate = null;

	protected $timeline = null;
	protected $user = null;

	protected $published = null;
	protected $title = null;
	protected $content = null;
	protected $likes = 0;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

}