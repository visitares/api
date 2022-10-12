<?php

namespace Visitares\Entity\Factory;

use DateTime;
use Visitares\Entity\CategoryProcess;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CategoryProcessFactory{
	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(
		InstanceStorageFacade $storage
	){
		$this->storage = $storage;
	}

	/**
	 * @param  array $values
	 * @return Language
	 */
	public function fromArray(array $values){
		$categoryProcess = new CategoryProcess;
		$categoryProcess->setCategory(
			$this->storage->category->findById($values['category_id'])
		);
		$categoryProcess->setName($values['name']);
		$categoryProcess->setDescription($values['description']);
		$categoryProcess->setDefinition($values['definition'] ?? null);
		return $categoryProcess;
	}
}
