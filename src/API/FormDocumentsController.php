<?php

namespace Visitares\API;

use DateTime;

use Visitares\Entity\Attachment;
use Visitares\Entity\Form;
use Visitares\Entity\Factory\AttachmentFactory;

use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

class FormDocumentsController{
	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var string
	 */
	protected $token = null;

	/**
	 * @var AttachmentFactory
	 */
	protected $factory = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param AttachmentFactory $factory
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		AttachmentFactory $factory
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
		$this->factory = $factory;
		$this->token = $token;
	}

	/**
	 * @param  integer $fid
	 * @param  array   $data
	 * @return boolean
	 */
	public function store($fid, array $data){
		$document = $this->factory->fromArray($data);
		$form = $this->storage->getReference('Visitares\\Entity\\Form', $fid);
		$document->setForm($form);
		$this->storage->store($document);
		$this->storage->apply();
		$document->setData($data['data'] ?? null, $this->token);
		return true;
	}

	/**
	 * @param  integer $fid
	 * @param  integer $did
	 * @return void
	 */
	public function download($fid, $did){
		$em = $this->storage->getEntityManager();
		$attachment = $em->getRepository('Visitares\Entity\Attachment')->findOneBy([
			'id' => $did
		]);
		if($attachment){
			header('Content-Type: ' . $attachment->getMimetype() . '; charset=utf-8');
			$attachment->read($this->token);
			exit;
		}
		return null;
	}

	/**
	 * @param  integer $fid
	 * @param  integer $did
	 * @return void
	 */
	public function remove($fid, $did){
		$em = $this->storage->getEntityManager();
		$attachment = $em->getRepository('Visitares\Entity\Attachment')->findOneBy([
			'id' => $did
		]);
		if($attachment){
			$attachment->removeAttachment($this->token);
			$this->storage->remove($attachment);
			$this->storage->apply();
			return true;
		}
		return false;
	}
}
