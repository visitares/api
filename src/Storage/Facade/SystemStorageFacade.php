<?php

namespace Visitares\Storage\Facade;

use Visitares\Entity\AbstractEntity;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\DirtyWordStorage;
use Visitares\Storage\EmoticonStorage;
use Visitares\Storage\EventStorage;
use Visitares\Storage\InstanceStorage;
use Visitares\Storage\LanguageStorage;
use Visitares\Storage\ReuqestStorage;
use Visitares\Storage\SessionStorage;
use Visitares\Storage\StringStorage;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class SystemStorageFacade{
	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var array
	 */
	protected $storages = [
		'config' => 'Visitares\Storage\ConfigStorage',
		'dirtyWord' => 'Visitares\Storage\DirtyWordStorage',
		'emoticon' => 'Visitares\Storage\EmoticonStorage',
		'event' => 'Visitares\Storage\EventStorage',
		'master' => 'Visitares\Storage\MasterStorage',
		'instance' => 'Visitares\Storage\InstanceStorage',
		'language' => 'Visitares\Storage\LanguageStorage',
		'request' => 'Visitares\Storage\ReuqestStorage',
		'session' => 'Visitares\Storage\SessionStorage',
		'string' => 'Visitares\Storage\StringStorage',
		'image' => 'Visitares\Storage\ImageStorage',
		'imageGroup' => 'Visitares\Storage\ImageGroupStorage'
	];

	/**
	 * @param DatabaseFacade $db
	 */
	public function __construct(DatabaseFacade $db, \PDO $pdo){
		$this->db = $db;
		$this->pdo = $pdo;
	}

	public function getPdo(){
		return $this->pdo;
	}

	/**
	 * @param string $token
	 * @return EntityManager
	 */
	public function getEntityManager(){
		return $this->db->fromSystem();
	}

	/**
	 * @return void
	 */
	public function apply(){
		$this->db->fromSystem()->flush();
	}

	/**
	 * @return void
	 */
	public function clear(){
		$this->db->fromSystem()->clear();
	}

	/**
	 * @param  string $class
	 * @param  integer $id
	 * @return mixed
	 */
	public function getReference($class, $id){
		$this->db->fromSystem()->getReference($class, $id);
	}

	/**
	 * @param AbstractEntity $object
	 * @return void
	 */
	public function store(AbstractEntity $object){
		$this->db->fromSystem()->persist($object);
	}

	/**
	 * @param AbstractEntity $object
	 * @return void
	 */
	public function remove(AbstractEntity $object){
		$this->db->fromSystem()->remove($object);
	}

	/**
	 * @param  string $storage
	 * @return mixed
	 */
	public function __get($storage){
		if(array_key_exists($storage, $this->storages)){
			if(is_string($this->storages[$storage])){
				$this->storages[$storage] = new $this->storages[$storage]($this->db->fromSystem());
			}
			return $this->storages[$storage];
		}
		return null;
	}
}