<?php

namespace Visitares\Entity;

use DateTime;
use JsonSerializable;
use ReflectionClass;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Visitares\Entity\Attachment;
use Visitares\Entity\Category;
use Visitares\Entity\Client;
use Visitares\Entity\Form;
use Visitares\Entity\FullResolveInterface;
use Visitares\Entity\ImageGroup;
use Visitares\Entity\Input;
use Visitares\Entity\Config;
use Visitares\Entity\Instance;
use Visitares\Entity\Language;
use Visitares\Entity\Option;
use Visitares\Entity\Translation;
use Visitares\Entity\MetaGroup;
use Visitares\Entity\User;
use Visitares\Entity\UserSubmitInstance;
use Visitares\Entity\DataListItem;
use Visitares\Entity\Media;
use Visitares\Entity\MasterMedia;
use Visitares\Service\Translation\TranslationService;
use Visitares\Storage\Facade\SystemStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
abstract class AbstractEntity implements JsonSerializable{
	/**
	 * @var SystemStorageFacade
	 */
	protected static $systemStorage = null;

	/**
	 * @var TranslationService
	 */
	protected static $translationService = null;

	/**
	 * Simply clone object.
	 */
	public function __clone(){
		$this->id = null;
	}

	/**
	 * @param SystemStorageFacade $systemStorage
	 */
	public static function setSystemStorage(SystemStorageFacade $systemStorage){
		static::$systemStorage = $systemStorage;
	}

	/**
	 * @param TranslationService $translationService
	 */
	public static function setTranslationService(TranslationService $translationService){
		static::$translationService = $translationService;
	}

	/**
	 * @param string $prop
	 * @return mixed
	 */
	public function __get($prop){
		if(property_exists($this, $prop)){
			return $this->$prop;
		}
		return null;
	}

	/**
	 * @param  string $method
	 * @param  array $arguments
	 * @return mixed|null
	 */
	public function __call($method, $arguments){
		if(preg_match('/^is|^has|^get|^set/', $method, $match)){
			$type = $match[0];
			$property = lcfirst(preg_replace('/^is|^has|^get|^set/', '', $method));
			if(in_array($type, ['is', 'has', 'get'])){
				return $this->$property;
			} elseif($type === 'set' && is_array($arguments)){
				$this->$property = $arguments[0];
			}
		}
	}

	/**
	 * @return array
	 */
	public function toArray(){
		return $this->jsonSerialize();
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): mixed {
		$values = [];

		if($this instanceof Translation){
			foreach($this->getTranslations() as $translation){
				$values[$translation->getLanguage()->getCode()] = $translation->getContent();
			}
			return $values;
		} elseif($this instanceof DateTime){
			return $this->format('Y-m-d H:i:s');
		}

		foreach($this as $property => $value){
			if(in_array($property, ['password'])){
				continue;
			}

			if($this instanceof Media && $property === 'language'){
				if($value){
					$values[$property] = $value->getCode();
					$values['lang'] = $value->getLabel();
				} else{
					$values[$property] = null;
					$values['lang'] = null;
				}
				continue;
			}

			if($this instanceof UserSubmitInstance && $property === 'category'){
				try{
					$values[$property] = $this->getCategory()->getId();
				} catch(\Exception $e){
					print_r($e->getMessage());exit;
				}
				continue;
			}

			if($this instanceof UserSubmitInstance && $property === 'user'){
				$values[$property] = $this->getUser()->getId();
				continue;
			}

			if($this instanceof Attachment && $property === 'data'){
				continue;
			}

			#if($this instanceof MetaGroup && ($property === 'description' || $property === 'name')){
			#	$values[$property] = (object)json_decode($value);
			#	continue;
			#}

			if($this instanceof Media && $property === 'description'){
				$values[$property] = (object)json_decode($value);
				continue;
			}

			if($this instanceof DataListItem && $property === 'value'){
				$values[$property] = json_decode($value);
				continue;
			}

			if($this instanceof Form && $property === 'media'){
				$values[$property] = json_decode($value);
				continue;
			}

			if($this instanceof Instance && ($property === 'settings' || $property === 'cmsConfig')){
				$value = json_decode($value);
			}

			if($this instanceof User && $property === 'instances'){
				$value = $value ? explode(',', $value) : [];
				foreach($value as &$entry){
					$entry = (int)$entry;
				}
			}

			if($this instanceof User && $property === 'configGroup' && $this->configGroup){
				$values[$property] = $this->getConfigGroup();
				continue;
			}

			if(substr($property, 0, 2) === '__'){
				continue;
			}

			if($property === 'coefficient' && $value !== null){
				$value = floatval($value);
			}

			/** Avoid recursion */
			if($this instanceof Option && $value instanceof Input){
				continue;
			}


			// Add icon object
			if($this instanceof Category && $property === 'iconId'){
				continue;
			}
			if($this instanceof Category && $property === 'icon'){
				if($value = static::$systemStorage->image->findById($this->getIconId())){
					$values['icon'] = [
						'id' => $value->getId(),
						'filename' => $value->getFilename()
					];
				} else{
					$values['icon'] = null;
				}
				continue;
			}
			if($this instanceof Instance && $property === 'backgroundId'){
				continue;
			}
			if($this instanceof Instance && $property === 'background'){
				if($value = static::$systemStorage->image->findById($this->getBackgroundId())){
					$values['background'] = [
						'id' => $value->getId(),
						'filename' => $value->getFilename()
					];
				} else{
					$values['background'] = null;
				}
				continue;
			}

			if($this instanceof Client && $property === 'iconId'){
				if($value = static::$systemStorage->image->findById($this->getIconId())){
					$values['icon'] = [
						'id' => $value->getId(),
						'filename' => $value->getFilename()
					];
				} else{
					$values['icon'] = null;
				}
				continue;
			}

			// decode json
			if($this instanceof ImageGroup && $property === 'instances' && $value){
				$values[$property] = json_decode($value);
				continue;
			}


			if($value instanceof DateTime){
				$value = $value->format('Y-m-d H:i:s');

			} elseif($value instanceof Language){
				$value = $value->getCode();

			} elseif($value instanceof Translation || strpos($property, 'Translation') !== false){
				if($value instanceof Translation){
					$tmp = [];
					foreach($value->getTranslations() as $translation){
						$tmp[$translation->getLanguage()->getCode()] = $translation->getContent();
					}
					$value = (object)$tmp;
				} else{
					$value = (object)[];
				}
				$property = str_replace('Translation', '', $property);

			} elseif($value instanceof TranslationService){
				continue;

			} elseif($value instanceof FullResolveInterface){
				// skip

			} elseif($value instanceof AbstractEntity){
				$value = $value->getId();

			} elseif($value instanceof ArrayCollection || $value instanceof PersistentCollection){
				if(count($value) > 0 && $value[0] instanceof FullResolveInterface){
					$tmp = [];
					foreach($value as $item){
						$tmp[] = $item;
					}
					$value = $tmp;
				} elseif(count($value) > 0 && $value[0] instanceof Attachment){
					$tmp = [];
					foreach($value as $item){
						$tmp[] = [
							'id' => $item->getId(),
							'date' => $item->getCreationDate()->format('Y-m-d H:i:s'),
							'new' => $item->getCreationDate()->format('Y-m-d H:i:s') >= (new DateTime)->modify('-1 week')->format('Y-m-d H:i:s'),
							'name' => $item->getName(),
							'type' => $item->getMimetype(),
							'size' => $item->getSize(),
							'sort' => $item->getSort(),
						];
					}
					$value = $tmp;
				} else{
					$tmp = [];
					foreach($value as $item){
						$tmp[] = $item->getId();
					}
					$value = $tmp;
				}
			}

			$values[$property] = $value;
		}
		return $values;
	}
}
