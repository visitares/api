<?php

namespace Visitares\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Visitares\Entity\Category;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Form extends AbstractEntity{
	const TYPE_CHECKBOX = 0;
	const TYPE_RADIO = 1;
	const TYPE_SELECT = 2;
	const TYPE_TEXT = 3;
	const TYPE_DOCUMENTS = 4;
	const TYPE_QUESTIONS = 5;
	const TYPE_MEDIA = 6;
	const TYPE_URL = 7;
	const TYPE_HEADLINE = 8;
	const TYPE_HEADLINE_CATALOG = 9;
	const TYPE_HTML_TEXT = 10;

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
	 * @var CatalogEntry
	 */
	protected $catalogEntry = null;

	/**
	 * @var Input[]
	 */
	protected $inputs = null;

	/**
	 * @var boolean
	 */
	protected $isActive = true;

	/**
	 * @var boolean
	 */
	protected $statsLocked = null;

	/**
	 * @var integer
	 */
	protected $type = null;

	/**
	 * @var integer
	 */
	protected $sort = null;

	/**
	 * @var boolean
	 */
	protected $publicStats = true;

	/**
	 * @var float
	 */
	protected $maxScore = null;

	/**
	 * @var boolean
	 */
	protected $singleSubmitOnly = null;

	/**
	 * @var array
	 */
	protected $documents = null;

	/**
	 * @var array
	 */
	protected $media = null;

	/**
	 * @var string
	 */
	protected $url = null;

	/**
	 * @var boolean
	 */
	protected $embedUrl = null;

	/**
	 * @var Translation
	 */
	protected $nameTranslation = null;

	/**
	 * @var Translation
	 */
	protected $descriptionTranslation = null;

	/**
	 * @var Translation
	 */
	protected $shortDescriptionTranslation = null;

	/**
	 * @var Translation
	 */
	protected $htmlTextTranslation = null;

	/**
	 * Initializes the entity.
	 */
	public function __construct(){
		$this->creationDate = new DateTime;
		$this->inputs = new ArrayCollection;
		$this->users = new ArrayCollection;
		$this->nameTranslation = new Translation;
		$this->descriptionTranslation = new Translation;
		$this->shortDescriptionTranslation = new Translation;
		$this->htmlTextTranslation = new Translation;
		$this->documents = new ArrayCollection;
	}

	/**
	 * Clear associations.
	 */
	public function __clone(){
		$this->inputs = [];
	}

	/**
	 * @param Input $input
	 */
	public function addInput(Input $input){
		foreach($this->inputs as $index => $item){
			if($item->getId() === $input->getId()){
				return true;
			}
		}
		$this->inputs[] = $input;
		$input->setForm($this);
		return false;
	}

	/**
	 * @param  Input $input
	 * @return void
	 */
	public function removeInput(Input $input){
		foreach($this->inputs as $index => $item){
			if($item->getId() === $input->getId()){
				unset($this->inputs[$index]);
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

	/**
	 * @param string $langCode
	 * @param string $value
	 */
	public function setShortDescription($langCode, $value){
		if($this->shortDescriptionTranslation === null){
			$this->shortDescriptionTranslation = new Translation;
		}
		static::$translationService->set($this->shortDescriptionTranslation, $langCode, $value);
	}

	/**
	 * @param  string $langCode
	 * @return string
	 */
	public function getShortDescription($langCode){
		return static::$translationService->get($this->shortDescriptionTranslation, $langCode);
	}

	/**
	 * @param string $langCode
	 * @param string $value
	 */
	public function setHtmlText($langCode, $value){
		if($this->htmlTextTranslation === null){
			$this->htmlTextTranslation = new Translation;
		}
		static::$translationService->set($this->htmlTextTranslation, $langCode, $value);
	}

	/**
	 * @param  string $langCode
	 * @return string
	 */
	public function getHtmlText($langCode){
		return static::$translationService->get($this->htmlTextTranslation, $langCode);
	}

	/**
	 * @param array $media
	 */
	public function setMedia($media){
		$this->media = json_encode($media);
	}

	/**
	 * @return array
	 */
	public function getMedia(){
		return $this->media ? json_decode($this->media) : null;
	}
}
