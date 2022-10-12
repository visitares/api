<?php

namespace Visitares\Entity;

use DateTime;

class ImageGroup extends AbstractEntity{
	// will be resized to 180x180
	const TYPE_ICON = 0;

	// will not be resized at all
	const TYPE_BACKGROUND = 1;

	protected $id = null;
	protected $creationDate = null;
	protected $modificationDate = null;
	protected $type = null;
	protected $label = null;
	protected $instances = null;

	public function __construct(){
		$this->creationDate = new DateTime;
	}

	/**
	 * @param array $instances
	 */
	public function setInstances($instances){
		if(is_array($instances)){
			$this->instances = json_encode($instances);
		} else{
			$this->instances = null;
		}
	}

	/**
	 * @return object
	 */
	public function getInstances(){
		if(!$this->instances){
			return [];
		} else{
			return json_decode($this->instances);
		}
	}
}