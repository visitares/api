<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Instance extends AbstractEntity{
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
	 * @var Master
	 */
	protected $master = null;

	/**
	 * @var Timeline
	 */
	protected $timeline = null;

	/**
	 * @var boolean
	 */
	protected $isActive = null;

	/**
	 * @var boolean
	 */
	protected $isTemplate = null;

	/**
	 * @var string
	 */
	protected $customerNumber = null;

	/**
	 * @var integer
	 */
	protected $statsDayRange = null;

	/**
	 * @var integer
	 */
	protected $statsMinUserCount = null;

	/**
	 * @var integer
	 */
	protected $usersCountByContract = null;

	/**
	 * @var boolean
	 */
	protected $messageAdministration = false;

	/**
	 * @var integer
	 */
	protected $logoffTimer = null;

	/**
	 * @var string
	 */
	protected $token = null;

	/**
	 * @var string
	 */
	protected $registrationToken = null;

	/**
	 * @var string
	 */
	protected $domain = null;

	/**
	 * @var string
	 */
	protected $name = null;

	/**
	 * @var string
	 */
	protected $shortDescription = null;

	/**
	 * @var string
	 */
	protected $description = null;

	/**
	 * @var string
	 */
	protected $country = null;

	/**
	 * @var string
	 */
	protected $postalCode = null;

	/**
	 * @var string
	 */
	protected $city = null;

	/**
	 * @var string
	 */
	protected $street = null;

	/**
	 * @var string
	 */
	protected $sector = null;

	/**
	 * @var string
	 */
	protected $logo = null;

	/**
	 * @var string
	 */
	protected $background = null;

	/**
	 * @var string
	 */
	protected $backgroundId = null;

	/**
	 * @var string
	 */
	protected $imageGroups = null;

	/**
	 * @var string
	 */
	protected $settings = null;

	/**
	 * @var string
	 */
	protected $cmsConfig = null;

	/**
	 * @var boolean
	 */
	protected $messageModule = true;

	/**
	 * @var int
	 */
	protected $defaultRegistrationRole = User::ROLE_APP_ADMIN;

	/**
	 * @var boolean
	 */
	protected $showMyProcesses = true;

	/**
	 * @var boolean
	 */
	protected $showAppAnonymousButton = true;

	/**
	 * @var boolean
	 */
	protected $showAppUserSettings = true;

	/**
	 * @var boolean
	 */
	protected $showAppLogout = true;

	/**
	 * @var string
	 */
	protected $notifyEmail = null;

	/**
	 * @var boolean
	 */
	protected $appSendDeeplinks = false;

	/**
	 * @var string
	 */
	protected $appDefaultUserMode = false;

	/**
	 * @var boolean
	 */
	protected $showFormSearch = true;

	/**
	 * @var boolean
	 */
	protected $showFormSearchShortDescription = true;

	/**
	 * @var boolean
	 */
	protected $showFormSearchDescription = true;

	/**
	 * @var boolean
	 */
	protected $allowInstructions = false;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->appDefaultUserMode = 'anonymous';
	}

	/**
	 * @param array $settings
	 */
	public function setSettings($settings){
		if(is_array($settings)){
			$this->settings = json_encode($settings);
		} else{
			$this->settings = null;
		}
	}

	/**
	 * @return object
	 */
	public function getSettings(){
		if(!$this->settings){
			return (object)[];
		} else{
			return json_decode($this->settings);
		}
	}

	/**
	 * @param array $imageGroups
	 */
	public function setImageGroups($imageGroups){
		if(is_array($imageGroups)){
			$this->imageGroups = json_encode($imageGroups);
		} else{
			$this->imageGroups = null;
		}
	}

	/**
	 * @return object
	 */
	public function getImageGroups(){
		if(!$this->imageGroups){
			return [];
		} else{
			$ids = (array)json_decode($this->imageGroups);
			return array_values($ids);
		}
	}

	/**
	 * @param array $cmsConfig
	 */
	public function setCmsConfig($cmsConfig){
		if(is_array($cmsConfig)){
			$this->cmsConfig = json_encode($cmsConfig);
		} else{
			$this->cmsConfig = null;
		}
	}

	/**
	 * @return object
	 */
	public function getCmsConfig(){
		if(!$this->cmsConfig){
			return null;
		} else{
			return json_decode($this->cmsConfig);
		}
	}
}
