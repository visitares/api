<?php

namespace Visitares\UseCase\Instance;

use PDO;
use RuntimeException;
use RandomLib\Generator;
use Visitares\Entity\Instance;
use Visitares\Factory\Doctrine\EntityManagerFactory;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Service\Database\DatabaseManager;
use Visitares\Service\Database\Migration;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Storage\Facade\SystemStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CreateInstance{
	/**
	 * @var EntitManagerFactory
	 */
	protected $emFactory = null;

	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var SystemStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $instanceStorage = null;

	/**
	 * @var DatabaseManager
	 */
	protected $dbManager = null;

	/**
	 * @var Generator
	 */
	protected $random = null;

	/**
	 * @var Migration
	 */
	protected $migration = null;

	/**
	 * @var boolean
	 */
	protected $useExistingDbs = false;

	/**
	 * @var PDO
	 */
	protected $pdo = null;

	/**
	 * @var string
	 */
	protected $dbPrefix = null;

	/**
	 * @param EntityManagerFactory $emFactory
	 * @param DatabaseFacade $db
	 * @param SystemStorageFacade $storage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param DatabaseManager $dbManager
	 * @param Generator $random
	 * @param Migration $migration
	 * @param bool $useExistingDbs
	 * @param PDO $pdo
	 * @param string $dbPrefix
	 */
	public function __construct(
		EntityManagerFactory $emFactory,
		DatabaseFacade $db,
		SystemStorageFacade $storage,
		InstanceStorageFacade $instanceStorage,
		DatabaseManager $dbManager,
		Generator $random,
		Migration $migration,
		$useExistingDbs,
		PDO $pdo,
		string $dbPrefix
	){
		$this->emFactory = $emFactory;
		$this->db = $db;
		$this->storage = $storage;
		$this->instanceStorage = $instanceStorage;
		$this->dbManager = $dbManager;
		$this->random = $random;
		$this->migration = $migration;
		$this->useExistingDbs = $useExistingDbs;
		$this->pdo = $pdo;
		$this->dbPrefix = $dbPrefix;
	}

	/**
	 * @param Instance $instance
	 * @param boolean $useExistingDbs
	 * @return Instance|null
	 */
	public function create(Instance $instance){
		if($this->useExistingDbs){
			if($foundInstance = $this->storage->instance->findByDomain(null)){
				$token = $foundInstance->getToken();
				$registrationToken = $this->random->generateString(16, Generator::CHAR_ALNUM);
				$foundInstance->setRegistrationToken($registrationToken);
				$foundInstance->setIsActive($instance->getIsActive());
				$foundInstance->setIsTemplate($instance->getIsTemplate());
				$foundInstance->setDomain($instance->getDomain());
				$foundInstance->setName($instance->getName());
				$foundInstance->setShortDescription($instance->getShortDescription());
				$foundInstance->setDescription($instance->getDescription());
				$foundInstance->setCountry($instance->getCountry());
				$foundInstance->setPostalCode($instance->getPostalCode());
				$foundInstance->setCity($instance->getCity());
				$foundInstance->setStreet($instance->getStreet());
				$foundInstance->setSector($instance->getSector());
				$foundInstance->setStatsDayRange($instance->getStatsDayRange());
				$foundInstance->setStatsMinUserCount($instance->getStatsMinUserCount());
				$foundInstance->setUsersCountByContract($instance->getUsersCountByContract());
				$foundInstance->setMessageAdministration($instance->getMessageAdministration());
				$foundInstance->setLogoffTimer($instance->getLogoffTimer());
				$foundInstance->setLogo($instance->getLogo());
				$foundInstance->setMessageModule($instance->getMessageModule());
				$foundInstance->setDefaultRegistrationRole($instance->getDefaultRegistrationRole());
				$foundInstance->setCustomerNumber($instance->getCustomerNumber());

				$foundInstance->setShowMyProcesses($instance->getShowMyProcesses());
				$foundInstance->setShowAppAnonymousButton($instance->getShowAppAnonymousButton());
				$foundInstance->setShowAppUserSettings($instance->getShowAppUserSettings());
				$foundInstance->setShowAppLogout($instance->getShowAppLogout());

				if($instance->getBackgroundId()){
					$foundInstance->setBackgroundId($instance->getBackgroundId());
				}

				$instance = $foundInstance;
			} else{
				return null;
			}

		} else{
			$token = $this->random->generateString(4, Generator::CHAR_LOWER);
			$registrationToken = $this->random->generateString(16, Generator::CHAR_ALNUM);
			$instance->setToken($token);
			$instance->setRegistrationToken($registrationToken);
			$this->dbManager->create($token);
		}
		$this->migration->createConfig($token);
		$this->migration->run($token);
		$this->storage->store($instance);
		$this->storage->apply();
		return $instance;
	}

	/**
	 * @param  Instance $instance
	 * @return boolean
	 */
	public function delete(Instance $instance){
		if($this->useExistingDbs){

			$tables = array_map(function(array $row){
				return $row[0];
			}, $this->pdo->query(sprintf('SHOW TABLES FROM `%s%s`;', $this->dbPrefix, $instance->getToken()))->fetchAll(\PDO::FETCH_NUM));

			$this->pdo->query('SET FOREIGN_KEY_CHECKS=0;')->execute();
			foreach($tables as $table){
				$sql = sprintf('DROP TABLE `%s%s`.%s; ', $this->dbPrefix, $instance->getToken(), $table);
				try{
					$stmt = $this->pdo->prepare($sql);
					$stmt->execute();
				} catch(\Exception $e){
					return false;
				}
			}

			$this->migration->deleteConfig($instance->getToken());

			$instance->setIsActive(null);
			$instance->setIsTemplate(null);
			$instance->setCustomerNumber(null);
			$instance->setRegistrationToken(null);
			$instance->setDomain(null);
			$instance->setName(null);
			$instance->setShortDescription(null);
			$instance->setDescription(null);
			$instance->setCountry(null);
			$instance->setPostalCode(null);
			$instance->setCity(null);
			$instance->setStreet(null);
			$instance->setSector(null);
			$instance->setLogo(null);
			$instance->setBackground(null);

			$instance->setStatsDayRange(30);
			$instance->setMinUserCount(5);
			$instance->setUsersCountByContract(0);
			$instance->setMessageAdministration(false);

			$this->storage->apply();

			return true;
		} else{
			$this->dbManager->drop($instance->getToken());
			$this->migration->deleteConfig($instance->getToken());
			$this->storage->remove($instance);
			$this->storage->apply();
			return true;
		}
	}
}