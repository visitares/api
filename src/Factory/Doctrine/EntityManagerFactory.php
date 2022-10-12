<?php

namespace Visitares\Factory\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Visitares\Storage\Facade\SystemDatabaseFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class EntityManagerFactory{
	/**
	 * @var array
	 */
	protected $dbConfig = null;

	/**
	 * @var array
	 */
	protected $systemConfig = null;

	/**
	 * @var array
	 */
	protected $instanceConfig = null;

	/**
	 * @var EntityManager
	 */
	protected $systemEntityManager = null;

	/**
	 * @param array $dbConfig
	 * @param array $systemConfig
	 * @param array $instanceConfig
	 */
	public function __construct(
		array $dbConfig,
		array $systemConfig,
		array $instanceConfig
	){
		$this->dbConfig = $dbConfig;
		$this->systemConfig = $systemConfig;
		$this->instanceConfig = $instanceConfig;
	}

	/**
	 * Creates an entity manager for the system database.
	 *
	 * @return EntityManager
	 */
	public function getSystemEntityManager(){
		if(!$this->systemEntityManager){
			$driver = new XmlDriver($this->systemConfig['mappingFiles'], '.orm.xml');
			$config = new Configuration;
			$config->setMetadataDriverImpl($driver);
			$config->setProxyDir($this->systemConfig['proxyDir']);
			$config->setProxyNamespace($this->systemConfig['proxyNamespace']);
			$config->setAutogenerateProxyClasses($this->systemConfig['autogenerateProxyClasses']);
			// $config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
			$connection = [
				'driver' => $this->dbConfig['default']['driver'],
				'host' => $this->dbConfig['default']['host'],
				'port' => $this->dbConfig['default']['port'],
				'dbname' => $this->dbConfig['default']['db_prefix'] . 'visitares',
				'charset' => $this->dbConfig['default']['charset'],
				'user' => $this->dbConfig['db_client']['username'],
				'password' => $this->dbConfig['db_client']['password'],
			];
			$this->systemEntityManager = EntityManager::create($connection, $config);
		}
		return $this->systemEntityManager;
	}

	/**
	 * Creates an entity manager for a specified instance.
	 *
	 * @param  string $token
	 * @return EntityManager
	 */
	public function getInstanceEntityManager($token){
		if($this->instanceExists($token)){
			$driver = new XmlDriver($this->instanceConfig['mappingFiles'], '.orm.xml');
			$config = new Configuration;
			$config->setMetadataDriverImpl($driver);
			$config->setProxyDir($this->instanceConfig['proxyDir']);
			$config->setProxyNamespace($this->instanceConfig['proxyNamespace']);
			$config->setAutogenerateProxyClasses($this->instanceConfig['autogenerateProxyClasses']);
			$connection = [
				'driver' => $this->dbConfig['default']['driver'],
				'host' => $this->dbConfig['default']['host'],
				'port' => $this->dbConfig['default']['port'],
				'dbname' => $this->dbConfig['default']['db_prefix'] . $token,
				'charset' => $this->dbConfig['default']['charset'],
				'user' => $this->dbConfig['db_client']['username'],
				'password' => $this->dbConfig['db_client']['password'],
			];
			return EntityManager::create($connection, $config);
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @return boolean
	 */
	protected function instanceExists($token){
		$repository = $this->getSystemEntityManager()->getRepository('Visitares\Entity\Instance');
		if($repository->findOneBy(['token' => $token])){
			return true;
		} else{
			return false;
		}
	}
}