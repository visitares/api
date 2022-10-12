<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Comment extends AbstractEntity{
	protected $id = null;

	protected $creationDate = null;
	protected $modificationDate = null;

	protected $post = null;
	protected $user = null;

	protected $content = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}
}