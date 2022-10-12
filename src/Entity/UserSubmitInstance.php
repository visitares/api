<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class UserSubmitInstance extends AbstractEntity{

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
	 * @var Category
	 */
	protected $category = null;

	/**
	 * @var User
	 */
	protected $user = null;

	/**
	 * @var User
	 */
	protected $webinstructor = null;

	/**
	 * @var Form
	 */
	protected $instructedForm = null;

	/**
	 * @var boolean
	 */
	protected $isDone = false;

	/**
	 * @var boolean
	 */
	protected $isInstructed = false;

	/**
	 * @var integer
	 */
	protected $score = 0;

	/**
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var string
	 */
	protected $description = null;

	/**
	 * @var string
	 */
	protected $definition = null;

	/**
	 * @var string
	 */
	protected $instructionByName = null;

	/**
	 * @var string
	 */
	protected $instructionCompany = null;

	/**
	 * @var string
	 */
	protected $instructionLocation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}
}
