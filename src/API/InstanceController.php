<?php

namespace Visitares\API;

use DateTime;
use Visitares\Entity\Instance;
use Visitares\Entity\Factory\InstanceFactory;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\UseCase\Instance\CreateInstance;
use Visitares\UseCase\Instance\CloneInstance;
use Visitares\UseCase\Instance\ListInstances;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class InstanceController{
	/**
	 * @var SystemStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $instanceStorage = null;

	/**
	 * @var CreateInstance
	 */
	protected $createInstanceUseCase = null;

	/**
	 * @var CloneInstance
	 */
	protected $cloneInstanceUseCase = null;

	/**
	 * @var ListInstances
	 */
	protected $listInstancesUseCase = null;

	/**
	 * @var InstanceFactory
	 */
	protected $factory = null;

	/**
	 * @param SystemStorageFacade $storage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param CreateInstance $createInstanceUseCase
	 * @param CloneInstance $cloneInstanceUseCase
	 * @param ListInstances $listInstancesUseCase
	 * @param InstanceFactory $facory
	 */
	public function __construct(
		SystemStorageFacade $storage,
		InstanceStorageFacade $instanceStorage,
		CreateInstance $createInstanceUseCase,
		CloneInstance $cloneInstanceUseCase,
		ListInstances $listInstancesUseCase,
		InstanceFactory $factory
	){
		$this->storage = $storage;
		$this->instanceStorage = $instanceStorage;
		$this->createInstanceUseCase = $createInstanceUseCase;
		$this->cloneInstanceUseCase = $cloneInstanceUseCase;
		$this->listInstancesUseCase = $listInstancesUseCase;
		$this->factory = $factory;
	}

	/**
	 * @return Instance[]
	 */
	public function getAll(){
		session_write_close();

		$instances = $this->listInstancesUseCase->getList();
		return array_map(function($instance){
			$array = $instance->toArray();
			$array['usersCount'] = $this->getUsersCount($instance->getId());
			$array['master'] = $instance->getMaster() ? $instance->getMaster()->getId() : null;
			return $array;
		}, $instances);
	}

	/**
	 * @param  integer $id
	 * @return Instance
	 */
	public function getById($id){
		$instance = $this->storage->instance->findById($id);
		$instance->usersCount = $this->getUsersCount($instance->getId());
		return $instance;
	}

	/**
	 * @param  string $domain
	 * @return Instance
	 */
	public function getByDomain($domain){
		return $this->storage->instance->findByDomain($domain);
	}

	/**
	 * @param  string $token
	 * @return Instance
	 */
	public function getByRegToken($token){
		return $this->storage->instance->findByToken($token);
	}

	/**
	 * @param  string  $token
	 * @param  integer $id
	 * @return Instance[]
	 */
	public function getByUser($token, $id){
		$this->instanceStorage->setToken($token);
		if($user = $this->instanceStorage->user->findById($id)){
			$result = [];
			$ids = explode(',', $user->getInstances());
			if($ids){
				$instances = $this->storage->instance->findByIdRange($ids);
				$result = array_merge($result, $instances);
			}
			return $result;
		}
		return [];
	}

	/**
	 * @return array
	 */
	public function getTemplates(){
		return $this->storage->instance->findTemplates();
	}

	/**
	 * @return Language[]
	 */
	public function getLanguages($token){
		if($instance = $this->storage->instance->findByToken($token)){
			$this->instanceStorage->setToken($token);
			return $this->instanceStorage->language->findAll();
		}
		return [];
	}

	/**
	 * @param  integer $id
	 * @return integer
	 */
	public function getUsersCount($id){
		if($instance = $this->storage->instance->findById($id)){
			$em = $this->instanceStorage->getEntityManager($instance->getToken());
			$queryBuilder = $em->createQueryBuilder();
			$queryBuilder->select('count(u.id)');
			$queryBuilder->from('Visitares\Entity\User', 'u');
			return (int)$queryBuilder->getQuery()->getSingleScalarResult();
		}
		return 0;
	}

	/**
	 * @param  string $token
	 * @return string
	 */
	public function getLogoImage($token){
		if($instance = $this->storage->instance->findByToken($token)){
			if($instance->getLogo()){
				$filename = sprintf('%s-%s.%s', $instance->getToken(), 'logo', 'jpg');
				$image = file_get_contents(APP_DIR_ROOT . '/var/system/images/' . $filename);
				return[
					'type' => 'image/jpeg',
					'data' => $image
				];
			}
		}
		return null;
	}

	/**
	 * @return Instance
	 */
	public function store(array $data){

		if($this->storage->instance->findByDomain($data['domain'])){
			return[
				'error' => true,
				'code' => 'DOMAIN_ALREADY_EXISTS'
			];
		}

		$instance = $this->factory->fromArray($data);
		if(isset($data['template'])){
			$source = $this->storage->instance->findById($data['template']);
			return $this->cloneInstanceUseCase->cloneFrom($source, $instance);
		} else{
			return $this->createInstanceUseCase->create($instance);
		}
	}

	/**
	 * @param  array  $data
	 * @return Instance
	 */
	public function update($id, array $data){
		set_time_limit(0);

		if($instance = $this->storage->instance->findById($id)){
			$instance->setModificationDate(new DateTime);
			$instance->setIsActive($data['isActive']);
			$instance->setIsTemplate($data['isTemplate']);
			$instance->setCustomerNumber($data['customerNumber']);
			$instance->setToken($data['token']);
			$instance->setDomain($data['domain']);
			$instance->setName($data['name']);
			$instance->setShortDescription($data['shortDescription']);
			$instance->setDescription($data['description']);
			$instance->setStatsDayRange($data['statsDayRange']);
			$instance->setStatsMinUserCount($data['statsMinUserCount']);
			$instance->setUsersCountByContract($data['usersCountByContract']);
			$instance->setMessageAdministration($data['messageAdministration']);
			$instance->setLogoffTimer($data['logoffTimer']);
			$instance->setCountry($data['country']);
			$instance->setPostalCode($data['postalCode']);
			$instance->setCity($data['city']);
			$instance->setStreet($data['street']);
			$instance->setSector($data['sector']);
			$instance->setMessageModule($data['messageModule']);
			$instance->setDefaultRegistrationRole($data['defaultRegistrationRole']);
			$instance->setNotifyEmail($data['notifyEmail']);
			$instance->setAppSendDeeplinks($data['appSendDeeplinks']);
			
			$instance->setAppDefaultUserMode($data['appDefaultUserMode'] ?? 'anonymous');
			$instance->setShowMyProcesses($data['showMyProcesses'] ?? true);
			$instance->setShowAppAnonymousButton($data['showAppAnonymousButton'] ?? true);
			$instance->setShowAppUserSettings($data['showAppUserSettings'] ?? true);
			$instance->setShowAppLogout($data['showAppLogout'] ?? true);

			$instance->setShowFormSearch($data['showFormSearch'] ?? true);
			$instance->setShowFormSearchShortDescription($data['showFormSearchShortDescription'] ?? true);
			$instance->setShowFormSearchDescription($data['showFormSearchDescription'] ?? true);
			
			$instance->setAllowInstructions($data['allowInstructions'] ?? false);

			if($instance->getLogo() && $data['logo'] === null){
				$filename = sprintf('%s-%s.%s', $instance->getToken(), 'logo', 'jpg');
				@unlink(APP_DIR_ROOT . '/var/system/images/' . $filename);
				$instance->setLogo(null);
			} elseif(isset($data['logoImage'])){
				list($dataType, $imageData) = explode(',', $data['logoImage']);
				$image = base64_decode($imageData);
				$filename = sprintf('%s-%s.%s', $instance->getToken(), 'logo', 'jpg');
				file_put_contents(APP_DIR_ROOT . '/var/system/images/' . $filename, $image);
				$instance->setLogo('image/jpeg');
			}

			if($data['background'] === null){
				$instance->setBackgroundId(null);
			} else{
				$instance->setBackgroundId($data['background']['id']);
			}

			$this->storage->apply();
			return $instance;
		}
		return null;
	}

	/**
	 * @param  integer $id
	 * @param  array   $data
	 * @return boolean
	 */
	public function updateSettings($id, array $data){
		if($instance = $this->storage->instance->findById($id)){
			if(isset($data['settings'])){
				$instance->setSettings($data['settings']);
			}
			if(isset($data['cmsConfig'])){
				$instance->setCmsConfig($data['cmsConfig']);
			}
			$this->storage->apply();
			return $instance;
		}
		return null;
	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($id){
		set_time_limit(60 * 15);
		if($instance = $this->storage->instance->findById($id)){
			return $this->createInstanceUseCase->delete($instance);
		}
		return null;
	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function removeMany($ids){
		set_time_limit(60 * 15);
		foreach($ids as $id){
			$this->remove($id);
		}
		return null;
	}
}
