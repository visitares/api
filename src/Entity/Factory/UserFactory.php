<?php

namespace Visitares\Entity\Factory;

use DateTime;
use RandomLib\Generator;
use Visitares\Entity\User;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class UserFactory{
	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var Generator
	 */
	protected $random = null;

	/**
	 * @param InstanceStorageFacade $storage
	 * @param Generator $random
	 */
	public function __construct(
		InstanceStorageFacade $storage,
		Generator $random
	){
		$this->storage = $storage;
		$this->random = $random;
		$this->appConfig = require(APP_DIR_ROOT . '/config/app.php');
	}

	/**
	 * @param  array $values
	 * @return User
	 */
	public function fromArray(array $values){
		$user = new User;
		$user->setIsActive($values['isActive']);
		$user->setRole($values['role']);
		$user->setUsername($values['username']);
		$user->setAnonymous($values['anonymous']);
		$user->setActiveFrom($this->getDate($values['activeFrom']));
		$user->setActiveUntil($this->getDate($values['activeUntil']));

		if($values['anonymous']){
			$token = $this->random->generateString(32, Generator::CHAR_ALNUM);
			$user->setAnonymousToken($token);
			$user->setPassword(
			password_hash(
				$token,
				$this->appConfig->password->algo,
				$this->appConfig->password->options
			)
		);
			$user->setDescription($values['description']);
			$user->setWelcomeText(isset($values['welcomeText']) ? $values['welcomeText'] : null);
		} else{
			$user->setPassword(
				password_hash(
					$values['password'],
					$this->appConfig->password->algo,
					$this->appConfig->password->options
				)
			);
			$user->setSalutation($values['salutation']);
			$user->setTitle($values['title']);
			$user->setFirstname($values['firstname']);
			$user->setLastname($values['lastname']);
			$user->setEmail($values['email']);
			$user->setPhone($values['phone']);
		}
		$user->setCompany(isset($values['company']) ? $values['company'] : null);
		$user->setDepartment(isset($values['department']) ? $values['department'] : null);

		// References
		$user->setLanguage($this->storage->language->findByCode($values['language']));
		foreach($values['groups'] as $id){
			if($group = $this->storage->group->findById($id)){
				$user->addGroup($group);
			}
		}
		$user->setInstances($values['instances'] ? implode(',', $values['instances']) : null);

		if(isset($values['configGroup']) && $values['configGroup']){
			$user->setConfigGroup($this->storage->group->findById($values['configGroup']));
		}

		return $user;
	}

	/**
	 * @param  string $value
	 * @return DateTime|null
	 */
	protected function getDate($value){
		return is_string($value) ? new DateTime($value) : null;
	}
}