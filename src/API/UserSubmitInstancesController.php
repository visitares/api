<?php

namespace Visitares\API;

use DateTime;
use Visitares\Entity\Category;
use Visitares\Entity\Form;
use Visitares\Entity\User;
use Visitares\Entity\Submit;
use Visitares\Entity\UserSubmitInstance;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Service\Export\SubmitInstanceExport;
use Visitares\Service\Export\SubmitInstancePdfExport;

class UserSubmitInstancesController{

	protected $storage = null;
	protected $em = null;
	protected $categories = null;
	protected $forms = null;
	protected $submits = null;
	protected $submitInstances = null;

	/**
	 * @param string                $token
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(
		$token,
		InstanceStorageFacade $storage,
		SubmitInstanceExport $export,
		SubmitInstancePdfExport $pdfExport
	){
		$this->storage = $storage;
		$this->storage->setToken($token);
		$this->em = $this->storage->getEntityManager();
		$this->categories = $this->storage->getEntityManager()->getRepository(Category::class);
		$this->forms = $this->storage->getEntityManager()->getRepository(Form::class);
		$this->submits = $this->storage->getEntityManager()->getRepository(Submit::class);
		$this->submitInstances = $this->storage->getEntityManager()->getRepository(UserSubmitInstance::class);
		$this->export = $export;
		$this->pdfExport = $pdfExport;
	}

	/**
	 * @param  string $userId
	 * @return array
	 */
	public function getAll($userId){
		return $this->submitInstances->findBy([
			'user' => $userId
		]);
	}

	/**
	 * @param  string $userId
	 * @param  string $categoryId
	 * @return array
	 */
	public function getAllByCategory($userId, $categoryId){
		return $this->submitInstances->findBy([
			'user' => $userId,
			'category' => $categoryId
		]);
	}

	/**
	 * @param  string $userId
	 * @param  string $id
	 * @return array
	 */
	public function getSubmits($userId, $id){
		$query = $this->em->createQuery('SELECT s.id, f.id as form FROM Visitares\Entity\Submit s JOIN s.submitInstance si JOIN s.form f WHERE si.id = ?1');
		$query->setParameter(1, $id);
		return $query->getResult();
	}

	/**
	 * @param  string $userId
	 * @param  string $id
	 * @return array
	 */
	public function getById($userId, $id){
		if(!$submitInstance = $this->submitInstances->findOneBy([
			'id' => (int)$id,
			'user' => (int)$userId
		])){
			return null;
		}
		return $submitInstance;
	}

	/**
	 * @param  string $userId
	 * @param  array  $data
	 * @return array
	 */
	public function store($userId, array $data){
		$submitInstance = new UserSubmitInstance;
		$submitInstance->setCategory( $this->storage->getReference(Category::class, $data['category']) );
		$submitInstance->setUser( $this->storage->getReference(User::class, $data['user']) );
		$submitInstance->setIsDone($data['isDone'] ?? false);
		$submitInstance->setScore(0);
		$submitInstance->setName($data['name']);
		if($data['webinstructor'] ?? null){
			$submitInstance->setIsInstructed(true);
			$submitInstance->setInstructedForm($this->storage->getReference(Form::class, $data['form']));
			$submitInstance->setWebinstructor($this->storage->getReference(User::class, $data['webinstructor']));
		}
		$submitInstance->setDescription($data['description'] ?? null);
		$submitInstance->setDefinition($data['definition'] ?? null);

		$submitInstance->setInstructionByName($data['instructionByName'] ?? null);
		$submitInstance->setInstructionCompany($data['instructionCompany'] ?? null);
		$submitInstance->setInstructionLocation($data['instructionLocation'] ?? null);

		$this->storage->store($submitInstance);
		$this->storage->apply();
		return $submitInstance;
	}

	/**
	 * @param  string $userId
	 * @param  string $id
	 * @param  array  $data
	 * @return array
	 */
	public function update($userId, $id, array $data){
		if(!$submitInstance = $this->submitInstances->findOneBy([
			'id' => $id,
			'user' => $userId
		])){
			return null;
		}
		$submitInstance->setModificationDate(new DateTime);
		$submitInstance->setName($data['name']);
		$submitInstance->setDescription($data['description']);
		$submitInstance->setDefinition($data['definition'] ?? null);

		$submitInstance->setInstructionByName($data['instructionByName'] ?? null);
		$submitInstance->setInstructionCompany($data['instructionCompany'] ?? null);
		$submitInstance->setInstructionLocation($data['instructionLocation'] ?? null);

		$this->storage->apply();
		return $submitInstance;
	}

	/**
	 * @param  string $userId
	 * @param  string $id
	 * @return array
	 */
	public function remove($userId, $id){
		if(!$submitInstance = $this->submitInstances->findOneBy([
			'id' => $id,
			'user' => $userId
		])){
			return false;
		}
		$this->storage->remove($submitInstance);
		$this->storage->apply();
		return true;
	}

	/**
	 * @param  string $token
	 * @param  array  $ids
	 * @return boolean
	 */
	public function removeMany($token, array $ids){
		$si = $this->storage->getEntityManager()->getRepository(UserSubmitInstance::class);
		if(!$si){
			return false;
		}
		$sis = $si->findBy([
			'id' => $ids
		]);
		foreach($sis as $entry){
			$this->storage->remove($entry);
		}
		$this->storage->apply();
		return true;
	}

	/**
	 * @param  string $userId
	 * @param  string $id
	 * @return array
	 */
	public function export($userId, $id){
		$data = $this->export->export($id);
		$pdf = $this->pdfExport->create($data);
	}

}
