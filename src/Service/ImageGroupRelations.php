<?php

namespace Visitares\Service;

use Visitares\Entity\ImageGroup;
use Visitares\Storage\Facade\SystemStorageFacade;

class ImageGroupRelations{
	private $storage = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
	}

	public function sync(ImageGroup $imageGroup, $instances){
		$allInstances = $this->storage->instance->findAll();
		foreach($allInstances as $instance){
			$imageGroups = $instance->getImageGroups();
			if(!in_array($instance->getId(), $instances)){
				if( ($index = array_search($imageGroup->getId(), (array)$imageGroups)) !== false ){
					unset($imageGroups[$index]);
					$instance->setImageGroups($imageGroups);
				}
			} else{
				if(!in_array($imageGroup->getId(), (array)$imageGroups)){
					$imageGroups[] = $imageGroup->getId();
					sort($imageGroups);
					$instance->setImageGroups($imageGroups);
				}
			}

		}
		$this->storage->apply();
	}
}