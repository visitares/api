<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Category;
use Visitares\Entity\Translation;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CategoryFactory{
	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(InstanceStorageFacade $storage){
		$this->storage = $storage;
	}

	/**
	 * @param  array $values
	 * @return Category
	 */
	public function fromArray(array $values){
		$category = new Category;
		$category->setIsActive($values['isActive'] ?? false);
		$category->setIsCopy(false);
		$category->setBeginDate($this->getDate($values['beginDate'] ?? null));
		$category->setEndDate($this->getDate($values['endDate'] ?? null));
		$category->setInputLockHours($values['inputLockHours'] ?? 24);
		$category->setSort($values['sort'] ?? 0);
		$category->setLineBreak($values['lineBreak'] ?? false);
		$category->setDividingLine($values['dividingLine'] ?? false);
		$category->setMaxScore($values['maxScore'] ?? null);
		$category->setProcessesEnabled($values['processesEnabled'] ?? false);
		$category->setEnableProcessDefinitions($values['enableProcessDefinitions'] ?? false);
		if($values['icon'] ?? null){
			$category->setIconId($values['icon']['id']);
		}
		foreach($values['name'] ?? [] as $langCode => $value){
			$category->setName($langCode, $value);
		}
		foreach($values['description'] ?? [] as $langCode => $value){
			$category->setDescription($langCode, $value);
		}
		if($client = $this->storage->client->findById($values['client'])){
			$category->setClient($client);
		}
		foreach($values['groups'] ?? [] as $id){
			if($group = $this->storage->group->findById($id)){
				$category->addGroup($group);
			}
		}
		return $category;
	}

	/**
	 * @param  string $value
	 * @return DateTime|null
	 */
	protected function getDate($value){
		return is_string($value) ? new DateTime($value) : null;
	}
}
