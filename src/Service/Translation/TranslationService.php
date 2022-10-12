<?php

namespace Visitares\Service\Translation;

use DateTime;
use Visitares\Entity\Language;
use Visitares\Entity\Translation;
use Visitares\Entity\Translated;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class TranslationService{
	/**
	 * @var Language[]
	 */
	protected $languages = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @param SytemStorageFacade $storage
	 */
	public function __construct(InstanceStorageFacade $storage){
		$this->storage = $storage;
	}

	/**
	 * @return Language[]
	 */
	protected function getLanauges(){
		if(!$this->languages){
			$this->languages = $this->storage->language->findAll();
			$tmp = [];
			foreach($this->languages as $language){
				$tmp[$language->getCode()] = $language;
			}
			$this->languages = $tmp;
		}
		return $this->languages;
	}

	/**
	 * @param Translation $container
	 * @param string      $langCode
	 * @param string      $value
	 */
	public function set($container, $langCode, $value){
		foreach($container->getTranslations() as $translation){
			if($translation->getLanguage()->getCode() === $langCode){
				$translation->setContent($value);
				return true;
			}
		}

		$languages = $this->getLanauges();
		if(array_key_exists($langCode, $languages)){
			$translation = new Translated;
			$translation->setCreationDate(new DateTime);
			$translation->setLanguage($languages[$langCode]);
			$translation->setContent($value);
			$container->add($translation);
			return true;
		}

		return false;
	}

	/**
	 * @param  Translation $container
	 * @param  string      $langCode
	 * @return string|null
	 */
	public function get(Translation $container, $langCode){
		foreach($container->getTranslations() as $translation){
			if($translation->getLanguage()->getCode() == $langCode){
				return $translation->getContent();
			}
		}
		return null;
	}
}