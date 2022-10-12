<?php

namespace Visitares\Storage\Facade;

use Visitares\Entity\AbstractEntity;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\CategoryStorage;
use Visitares\Storage\ClientStorage;
use Visitares\Storage\CustomerStorage;
use Visitares\Storage\FormStorage;
use Visitares\Storage\GroupStorage;
use Visitares\Storage\InputStorage;
use Visitares\Storage\LanguageStorage;
use Visitares\Storage\DirtyWordStorage;
use Visitares\Storage\MessageStorage;
use Visitares\Storage\OptionStorage;
use Visitares\Storage\SubmitStorage;
use Visitares\Storage\TranslatedStorage;
use Visitares\Storage\TranslationStorage;
use Visitares\Storage\UnreadStorage;
use Visitares\Storage\UserStorage;
use Visitares\Storage\ValueStorage;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class InstanceStorageFacade{
	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var string
	 */
	protected $token = null;

	/**
	 * @var array
	 */
	protected $storages = [
		'attachment' => 'Visitares\Storage\AttachmentStorage',
		'category' => 'Visitares\Storage\CategoryStorage',
		'categoryProcess' => 'Visitares\Storage\CategoryProcessStorage',
		'client' => 'Visitares\Storage\ClientStorage',
		'customer' => 'Visitares\Storage\CustomerStorage',
		'form' => 'Visitares\Storage\FormStorage',
		'group' => 'Visitares\Storage\GroupStorage',
		'input' => 'Visitares\Storage\InputStorage',
		'language' => 'Visitares\Storage\LanguageStorage',
		'dirtyWord' => 'Visitares\Storage\DirtyWordStorage',
		'unit' => 'Visitares\Storage\UnitStorage',
		'message' => 'Visitares\Storage\MessageStorage',
		'option' => 'Visitares\Storage\OptionStorage',
		'submit' => 'Visitares\Storage\SubmitStorage',
		'translated' => 'Visitares\Storage\TranslatedStorage',
		'translation' => 'Visitares\Storage\TranslationStorage',
		'unread' => 'Visitares\Storage\UnreadStorage',
		'user' => 'Visitares\Storage\UserStorage',
		'value' => 'Visitares\Storage\ValueStorage'
	];

	/**
	 * @var array
	 */
	protected $storageMap = [];

	/**
	 * @param DatabaseFacade $db
	 */
	public function __construct(DatabaseFacade $db){
		$this->db = $db;
	}

	/**
	 * @param string $token
	 * @return EntityManager
	 */
	public function getEntityManager($token = null){
		if(!$token){
			$token = $this->token;
		}
		return $this->db->fromInstance($token);
	}

	/**
	 * @param string $class
	 * @param string $token
	 * @return Repository
	 */
	public function getRepository($class, $token = null){
		$em = $this->getEntityManager($token);
		return $em->getRepository($class);
	}

	/**
	 * @param string $token
	 */
	public function setToken($token){
		$this->token = $token;
	}

	/**
	 * @return string
	 */
	public function getToken(){
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return void
	 */
	public function apply($token = null){
		if(!$token){
			$token = $this->token;
		}
		$this->db->fromInstance($token)->flush();
		return $this;
	}

	/**
	 * @param string $token
	 * @return void
	 */
	public function clear($token = null){
		if(!$token){
			$token = $this->token;
		}
		$this->db->fromInstance($token)->clear();
	}

	/**
	 * @param  string $class
	 * @param  integer $id
	 * @param  string $token
	 * @return mixed
	 */
	public function getReference($class, $id, $token = null){
		if(!$token){
			$token = $this->token;
		}
		return $this->db->fromInstance($token)->getReference($class, $id);
	}

	/**
	 * @param AbstractEntity $object
	 * @param string $token
	 * @return void
	 */
	public function store(AbstractEntity $object, $token = null){
		if(!$token){
			$token = $this->token;
		}
		$this->db->fromInstance($token)->persist($object);
		return $this;
	}

	/**
	 * @param AbstractEntity $object
	 * @param string $token
	 * @return void
	 */
	public function remove(AbstractEntity $object, $token = null){
		if(!$token){
			$token = $this->token;
		}
		$this->db->fromInstance($token)->remove($object);
		return $this;
	}

	/**
	 * @param  string $storage
	 * @return mixed
	 */
	public function __get($storage){
		if(array_key_exists($storage, $this->storages)){

			if(!isset($this->storageMap[$this->token])){
				$this->storageMap[$this->token] = [];
			}

			if(!isset($this->storageMap[$this->token][$storage])){
				$this->storageMap[$this->token][$storage] = new $this->storages[$storage]($this->db->fromInstance($this->token));
			}

			return $this->storageMap[$this->token][$storage];
		}
		return null;
	}
}