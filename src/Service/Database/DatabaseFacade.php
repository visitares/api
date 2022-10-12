<?php

namespace Visitares\Service\Database;

use Visitares\Factory\Doctrine\EntityManagerFactory;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class DatabaseFacade{
	/**
	 * @var EntityManager
	 */
	protected $system = null;

	/**
	 * @var EntityManager[]
	 */
	protected $instances = [];

	/**
	 * @var string
	 */
	protected $defaultToken = null;

	/**
	 * @param EntityManagerFactory $factory
	 */
	public function __construct(EntityManagerFactory $factory){
		$this->factory = $factory;
	}

	/**
	 * @return EntityManager
	 */
	public function fromSystem(){
		if(!$this->system){
			$this->system = $this->factory->getSystemEntityManager();
		}
		return $this->system;
	}

	/**
	 * @param string $token
	 */
	public function setDefaultToken($token){
		$this->defaultToken = $token;
	}

	/**
	 * @param  string $token
	 * @return EntityManager
	 */
	public function fromInstance($token = null){
		if(!$token){
			$token = $this->defaultToken;
		}
		if(!array_key_exists($token, $this->instances)){
			$this->instances[$token] = $this->factory->getInstanceEntityManager($token);
		}
		return $this->instances[$token];
	}
}