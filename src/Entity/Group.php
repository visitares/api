<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Group extends AbstractEntity{

	const DEFAULT_APP_SCREEN_CAMPAIGNS = 0;
	const DEFAULT_APP_SCREEN_CAMPAIGNGROUPS = 1;
	const DEFAULT_APP_SCREEN_STREAM = 2;
	const DEFAULT_APP_SCREEN_MESSAGES = 3;

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
	 * @var Category[]
	 */
	protected $categories = null;

	/**
	 * @var User[]
	 */
	protected $users = null;

	/**
	 * @var boolean
	 */
	protected $isDefault = false;

	/**
	 * @var boolean
	 */
	protected $isDefaultConfig = false;

	/**
	 * @var integer
	 */
	protected $defaultAppScreen = false;

	/**
	 * @var Translation
	 */
	protected $nameTranslation = null;

	/**
	 * @var Translation
	 */
	protected $descriptionTranslation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->categories = new ArrayCollection;
		$this->users = new ArrayCollection;
		$this->nameTranslation = new Translation;
		$this->descriptionTranslation = new Translation;
	}

	/**
	 * Prepare for cloning.
	 */
	public function __clone(){
		parent::__clone();
		$this->categories = [];
		$this->users = [];
	}

	/**
	 * @param Category $category
	 */
	public function addCategory(Category $category){
		foreach($this->categories as $index => $item){
			if($item->getId() === $category->getId()){
				return true;
			}
		}
		$this->categories[] = $category;
		$category->addGroup($this);
		return false;
	}

	/**
	 * @param  Category $category
	 * @return void
	 */
	public function removeCategory(Category $category){
		foreach($this->categories as $index => $item){
			if($item->getId() === $category->getId()){
				unset($this->categories[$index]);
			}
		}
		return $category->removeGroup($this);
	}

	/**
	 * @param User $user
	 */
	public function addUser(User $user){
		foreach($this->users as $index => $item){
			if($item->getId() === $user->getId()){
				return true;
			}
		}
		$this->users[] = $user;
		return false;
	}

	/**
	 * @param  User $user
	 * @return void
	 */
	public function removeUser(User $user){
		foreach($this->users as $index => $item){
			if($item->getId() === $user->getId()){
				unset($this->users[$index]);
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $langCode
	 * @param string $value
	 */
	public function setName($langCode, $value){
		static::$translationService->set($this->nameTranslation, $langCode, $value);
	}

	/**
	 * @param  string $langCode
	 * @return string
	 */
	public function getName($langCode){
		return static::$translationService->get($this->nameTranslation, $langCode);
	}

	/**
	 * @param string $langCode
	 * @param string $value
	 */
	public function setDescription($langCode, $value){
		static::$translationService->set($this->descriptionTranslation, $langCode, $value);
	}

	/**
	 * @param  string $langCode
	 * @return string
	 */
	public function getDescription($langCode){
		return static::$translationService->get($this->descriptionTranslation, $langCode);
	}
}