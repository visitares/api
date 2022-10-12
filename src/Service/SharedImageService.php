<?php

namespace Visitares\Service;

use Visitares\Storage\Facade\SystemStorageFacade;

class SharedImageService{

	private $storage = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
	}

	public function get($id){
		return $this->storage->image->findById($id);
	}

}