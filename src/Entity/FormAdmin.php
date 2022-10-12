<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class FormAdmin extends AbstractEntity{
	const ROLE_MESSAGE_ADMIN = 0;

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
	 * @var Form
	 */
	protected $form = null;

	/**
	 * @var integer
	 */
	protected $role = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}
}