<?php

namespace Visitares\API;

use RandomLib\Generator;
use Visitares\Entity\Factory\CategoryProcessFactory;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Service\Export\ProcessExport;
use Visitares\Service\Export\ProcessPdfExport;

class CategoryProcessController{

	private $factory = null;
	private $storage = null;
	private $random = null;
	private $processExport = null;

	public function __construct(
		CategoryProcessFactory $factory,
		InstanceStorageFacade $storage,
		Generator $random,
		$token,
		ProcessExport $processExport,
		ProcessPdfExport $processPdfExport
	){
		$this->factory = $factory;
		$this->storage = $storage;
		$this->storage->setToken($token);
		$this->random = $random;
		$this->processExport = $processExport;
		$this->processPdfExport = $processPdfExport;
	}

	public function query(){
		return $this->storage->categoryProcess->find([]);
	}

	public function getSubmits($processId){
		return [];
	}

	public function get($id){
		return $this->storage->categoryProcess->findById($id);
	}

	public function store($categoryId, $data){
		$data['category_id'] = $categoryId;
		$categoryProcess = $this->factory->fromArray($data);

		$token = null;
		while(!$token){
			$token = $this->random->generateString(8, Generator::CHAR_ALNUM);
			if($tmp = $this->storage->categoryProcess->find(['token' => $token])){
				$token = null;
			}
		}
		$categoryProcess->setToken($token);

		$this->storage->store($categoryProcess);
		$this->storage->apply();
		return $this->get($categoryProcess->getId());
	}

	public function update($processId, $data){
		if(!$categoryProcess = $this->storage->categoryProcess->findById($processId)){
			return null;
		}
		$categoryProcess->setName($data['name']);
		$categoryProcess->setDescription($data['description']);
		$categoryProcess->setDefinition($data['definition'] ?? null);
		$this->storage->store($categoryProcess);
		$this->storage->apply();
		return $this->get($categoryProcess->getId());
	}

	public function remove($processId, $archive = true){
		if(!$categoryProcess = $this->storage->categoryProcess->findById($processId)){
			return false;
		}

		if($archive){
			$categoryProcess->setIsArchived(true);
		} else{
			$this->storage->remove($categoryProcess);
		}
		$this->storage->apply();

		return true;
	}

	public function exportAsPdf($processId){
		$data = $this->processExport->export($processId);
		$pdf = $this->processPdfExport->create($data);
	}

}
