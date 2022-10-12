<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Message extends AbstractEntity{
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
	 * @var Submit
	 */
	protected $submit = null;

	/**
	 * @var ArrayCollection
	 */
	protected $attachments = null;

	/**
	 * @var boolean
	 */
	protected $published = false;

	/**
	 * @var string
	 */
	protected $message = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->attachments = new ArrayCollection;
	}
}