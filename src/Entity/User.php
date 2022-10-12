<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class User extends AbstractEntity{
	const ROLE_SUPERUSER = 0;
	const ROLE_ADMIN = 1;
	const ROLE_USER = 2;
	const ROLE_APP_ADMIN = 3;
	const ROLE_WEB_INSTRUCTOR = 4;

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
	 * @var DateTime
	 */
	protected $lastLogin = null;

	/**
	 * @var Language
	 */
	protected $language = null;

	/**
	 * @var Group[]
	 */
	protected $groups = null;

	/**
	 * @var Group
	 */
	protected $configGroup = null;

	/**
	 * @var integer
	 */
	protected $isActive = null;

	/**
	 * @var integer
	 */
	protected $role = null;

	/**
	 * @var string
	 */
	protected $instances = null;

	/**
	 * @var string
	 */
	protected $username = null;

	/**
	 * @var string
	 */
	protected $password = null;

	/**
	 * @var int
	 */
	protected $salutation = null;

	/**
	 * @var int
	 */
	protected $title = null;

	/**
	 * @var string
	 */
	protected $firstname = null;

	/**
	 * @var string
	 */
	protected $lastname = null;

	/**
	 * @var string
	 */
	protected $department = null;

	/**
	 * @var string
	 */
	protected $company = null;

	/**
	 * @var string
	 */
	protected $email = null;

	/**
	 * @var string
	 */
	protected $phone = null;

	/**
	 * @var string
	 */
	protected $description = null;

	/**
	 * @var string
	 */
	protected $welcomeText = null;

	/**
	 * @var boolean
	 */
	protected $anonymous = false;

	/**
	 * @var string
	 */
	protected $anonymousToken = null;

	/**
	 * @var DateTime
	 */
	protected $activeFrom = null;

	/**
	 * @var DateTime
	 */
	protected $activeUntil = null;

	/**
	 * @var string
	 */
	protected $resetToken = null;
	
	/**
	 * @var DateTime
	 */
	protected $resetTokenExpire = null;

	/**
	 * @var int|null
	 */
	protected $defaultAppScreen = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->groups = new ArrayCollection;
		$this->forms = new ArrayCollection;
	}

	/**
	 * @param Group $group
	 */
	public function addGroup(Group $group){
		foreach($this->groups as $index => $item){
			if($item->getId() === $group->getId()){
				return true;
			}
		}
		$this->groups[] = $group;
		$group->addUser($this);
		return false;
	}

	/**
	 * @param  Group $group
	 * @return void
	 */
	public function removeGroup(Group $group){
		foreach($this->groups as $index => $item){
			if($item->getId() === $group->getId()){
				unset($this->groups[$index]);
			}
		}
		return $group->removeUser($this);
	}
}