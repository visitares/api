<?php

namespace Visitares\Entity;

use DateTime;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CachedUser extends AbstractEntity{

	protected $id = null;
	protected $creationDate = null;
	protected $modificationDate = null;

	protected $instance = null;
	protected $userId = null;
	
	protected $salutation = null;
	protected $title = null;
	protected $username = null;
	protected $firstname = null;
	protected $lastname = null;

	protected $company = null;
	protected $department = null;

	protected $email = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
	}
}