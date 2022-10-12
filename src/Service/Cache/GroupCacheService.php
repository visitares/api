<?php

namespace Visitares\Service\Cache;

use DateTime;

use Visitares\Storage\Facade\SystemStorageFacade;

use Visitares\Entity\Group;
use Visitares\Entity\Instance;
use Visitares\Entity\CachedGroup;
use Visitares\Entity\Translation;

class GroupCacheService{

	private $storage = null;
	private $em = null;
	private $groupcache = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $storage->getEntityManager();
		$this->groupcache = $this->em->getRepository(CachedGroup::class);
	}

	/**
	 * @param  Instance $instance
	 * @param  Group    $group
	 * @return CachedGroup
	 */
	public function update(Instance $instance, Group $group){
		if(!$cachedGroup = $this->groupcache->findOneBy([
			'instance' => $instance,
			'groupId' => $group->getId()
		])){
			$cachedGroup = new CachedGroup;
			$cachedGroup->setInstance($instance);
			$cachedGroup->setGroupId($group->getId());
		} else{
			$cachedGroup->setModificationDate(new DateTime);
		}

		$cachedGroup->setName($this->translationsToArray($group->getNameTranslation()));
		$cachedGroup->setDescription($this->translationsToArray($group->getDescriptionTranslation()));

		$this->em->persist($cachedGroup);
		$this->em->flush();

		return $cachedGroup;
	}

	/**
	 * @param  Translation $translation
	 * @return array
	 */
	protected function translationsToArray(Translation $translation){
		$result = [];
		foreach($translation->getTranslations() as $t){
			$result[$t->getLanguage()->getCode()] = $t->getContent();
		}
		return $result;
	}

	/**
	 * @param  Instance $instance
	 * @param  Group     $group
	 * @return boolean
	 */
	public function remove(Instance $instance, Group $group){
		if($cachedGroup = $this->groupcache->findOneBy([
			'instance' => $instance,
			'groupId' => $group->getId()
		])){
			$this->em->remove($cachedGroup);
			$this->em->flush();
			return true;
		}
		return false;
	}

}