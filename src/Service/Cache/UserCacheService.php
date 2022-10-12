<?php

namespace Visitares\Service\Cache;

use DateTime;

use Visitares\Storage\Facade\SystemStorageFacade;

use Visitares\Entity\User;
use Visitares\Entity\Instance;
use Visitares\Entity\CachedUser;

class UserCacheService{

	private $storage = null;
	private $em = null;
	private $usercache = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $storage->getEntityManager();
		$this->usercache = $this->em->getRepository(CachedUser::class);
	}

	/**
	 * @param  Instance $instance
	 * @param  User     $user
	 * @return CachedUser
	 */
	public function getCachedUser(Instance $instance, User $user){
		return $this->usercache->findOneBy([
			'instance' => $instance,
			'userId' => $user->getId()
		]);
	}

	/**
	 * @param  Instance $instance
	 * @param  User     $user
	 * @return CachedUser
	 */
	public function update(Instance $instance, User $user){
		if(!$cachedUser = $this->usercache->findOneBy([
			'instance' => $instance,
			'userId' => $user->getId()
		])){
			$cachedUser = new CachedUser;
			$cachedUser->setInstance($instance);
			$cachedUser->setUserId($user->getId());
		} else{
			$cachedUser->setModificationDate(new DateTime);
		}

		$cachedUser->setSalutation($user->getSalutation());
		$cachedUser->setTitle($user->getTitle());
		$cachedUser->setUsername($user->getUsername());
		$cachedUser->setFirstname($user->getFirstname() ? $user->getFirstname() : '');
		$cachedUser->setLastname($user->getLastname() ? $user->getLastname() : '');
		$cachedUser->setEmail($user->getEmail() ? $user->getEmail() : '');
		$cachedUser->setCompany($user->getCompany());
		$cachedUser->setDepartment($user->getDepartment());

		$this->em->persist($cachedUser);
		$this->em->flush();

		return $cachedUser;
	}

	/**
	 * @param  Instance $instance
	 * @param  User     $user
	 * @return boolean
	 */
	public function remove(Instance $instance, User $user){
		if($cachedUser = $this->usercache->findOneBy([
			'instance' => $instance,
			'userId' => $user->getId()
		])){
			$this->em->remove($cachedUser);
			$this->em->flush();
			return true;
		}
		return false;
	}

}