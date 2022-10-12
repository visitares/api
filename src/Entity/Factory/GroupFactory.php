<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\Group;
use Visitares\Entity\Translation;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class GroupFactory{
	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(InstanceStorageFacade $storage){
		$this->storage = $storage;
	}

	/**
	 * @param  array $values
	 * @return Group
	 */
	public function fromArray(array $values){
		$group = new Group;
		$group->setCreationDate(new DateTime);
		$group->setIsDefault($values['isDefault']);
		$group->setIsDefaultConfig($values['isDefaultConfig']);
		$group->setDefaultAppScreen($values['defaultAppScreen']);
		foreach($values['name'] as $langCode => $value){
			$group->setName($langCode, $value);
		}
		foreach($values['description'] as $langCode => $value){
			$group->setDescription($langCode, $value);
		}
		foreach($values['users'] as $id){
			if($user = $this->storage->user->findById($id)){
				$user->addUser($user);
			}
		}
		return $group;
	}
}