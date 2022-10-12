<?php

namespace Visitares\UseCase\Instance;

use Visitares\Storage\Facade\SystemStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class ListInstances{
	/**
	 * @var SystemStorageFacade
	 */
	protected $storage = null;

	/**
	 * @param SystemStorageFacade $systemStorage
	 */
	public function __construct(SystemStorageFacade $storage){
		$this->storage = $storage;
	}

	/**
	 * @return array
	 */
	public function getList(){
		return $this->storage->instance->findAll();
	}
}