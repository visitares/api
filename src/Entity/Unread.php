<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Unread extends AbstractEntity{
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
	 * @var User
	 */
	protected $user = null;

	/**
	 * @var Message
	 */
	protected $submit = null;

	/**
	 * @var Message
	 */
	protected $message = null;

	/**
	 * @var integer
	 */
	protected $count = null;

	/**
	 * Intialize the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->count = 1;
	}

	/**
	 * @return integer
	 */
	public function incCount(){
		return ++$this->count;
	}
}