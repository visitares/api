<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Category extends AbstractEntity{
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
	 * @var Client
	 */
	protected $client = null;

	/**
	 * @var Group[]
	 */
	protected $groups = null;

	/**
	 * @var boolean
	 */
	protected $isActive = null;

	/**
	 * @var integer
	 */
	protected $isCopy = null;

	/**
	 * @var DateTime
	 */
	protected $beginDate = null;

	/**
	 * @var DateTime
	 */
	protected $endDate = null;

	/**
	 * @var integer
	 */
	protected $inputLockHours = 24;

	/**
	 * @var integer
	 */
	protected $sort = null;

	/**
	 * @var boolean
	 */
	protected $lineBreak = false;

	/**
	 * @var boolean
	 */
	protected $dividingLine = false;

	/**
	 * @var float
	 */
	protected $maxScore = null;

	/**
	 * @var boolean
	 */
	protected $processesEnabled = false;

	/**
	 * @var boolean
	 */
	protected $enableProcessDefinitions = false;

	/**
	 * @var string
	 */
	protected $icon = null;

	/**
	 * @var integer
	 */
	protected $iconId = null;

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
		$this->groups = new ArrayCollection;
		$this->nameTranslation = new Translation;
		$this->descriptionTranslation = new Translation;
	}

	/**
	 * Prepare for cloning.
	 */
	public function __clone(){
		parent::__clone();
		$this->groups = [];
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
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $maxScore
	 */
	public function setMaxScore($maxScore){
		if($maxScore !== null && $maxScore !== ''){
			if(is_string($maxScore)){
				$maxScore = str_replace(',', '.', $maxScore);
			}
			$this->maxScore = floatval($maxScore);
		} else{
			$this->maxScore = null;
		}
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
