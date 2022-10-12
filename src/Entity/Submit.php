<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Submit extends AbstractEntity{
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
	 * @var Form
	 */
	protected $form = null;

	/**
	 * @var User
	 */
	protected $user = null;

	/**
	 * @var CategoryProcess
	 */
	protected $categoryProcess = null;

	/**
	 * @var UserSubmitInstance
	 */
	protected $submitInstance = null;

	/**
	 * @var string
	 */
	protected $token = null;

	/**
	 * @var Language
	 */
	protected $language = null;

	/**
	 * @var Value[]
	 */
	protected $values = null;

	/**
	 * @var Message[]
	 */
	protected $messages = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->values = New ArrayCollection;
		$this->messages = New ArrayCollection;
	}
}