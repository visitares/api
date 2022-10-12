<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class PostMedia extends Media{

	protected $id = null;

	protected $creationDate = null;
	protected $modificationDate = null;

	protected $post = null;

	protected $label = null;
	protected $description = null;
	protected $type = null;
	protected $mime = null;
	protected $filename = null;
	protected $originalFilename = null;
	protected $ext = null;
	protected $filesize = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

}