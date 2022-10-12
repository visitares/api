<?php

namespace Visitares\API;

use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

class DebugController{

	public function __construct(){
		// ..
	}

	public function upload($video){
    print_r($video);
	}
}