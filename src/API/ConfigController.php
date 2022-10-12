<?php

namespace Visitares\API;

use DateTime;
use Visitares\Entity\Config;
use Visitares\Storage\Facade\SystemStorageFacade;

class ConfigController{
	private $storage = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
	}

	public function get($name){
		return $this->storage->config->findOneByName($name);
	}

	public function store($name, array $data){
		if(!$var = $this->storage->config->findOneByName($name)){
			$var = new Config;
			$this->storage->store($var);
			$var->setName($name);
		} else{
			$var->setModificationDate(new DateTime);
		}
		$var->setValue($data['value']);
		$this->storage->apply();
		return $this->get($name);
	}
}