<?php

namespace Visitares\Service\User;

use Visitares\Entity\User;
use Visitares\Entity\CachedUser;
use Visitares\Entity\MetaGroup;
use Visitares\Entity\Instance;
use Visitares\Entity\CachedUserMetaGroup;
use Visitares\Storage\Facade\SystemStorageFacade;

class UserMetaGroupService{

	/**
	 * @var SystemStorageFacade
	 */
	private $storage = null;

	/**
	 * @var EntityManager
	 */
	private $em = null;

	/**
	 * @var Repository
	 */
	private $cachedUsers = null;

	/**
	 * @var Repository
	 */
	private $metaGroups = null;

	/**
	 * @var Repository
	 */
	private $userMetaGroups = null;

	/**
	 * @param SystemStorageFacade $storage
	 */
	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $storage->getEntityManager();
		$this->cachedUsers = $this->em->getRepository(CachedUser::class);
		$this->userMetaGroups = $this->em->getRepository(CachedUserMetaGroup::class);
	}

	/**
	 * @param  User $user
	 * @return MetaGroup[]
	 */
	public function getMetaGroupsByUser(Instance $instance, User $user){
		$cachedUser = $this->cachedUsers->findOneBy([
			'instance' => $instance,
			'userId' => $user->getId()
		]);
		return $this->getMetaGroupsByCachedUser($cachedUser);
	}

	/**
	 * @param  CachedUser $user
	 * @return MetaGroup[]
	 */
	public function getMetaGroupsByCachedUser(CachedUser $cachedUser = null){
		if(!$cachedUser){
			return [];
		}
		$userMetaGroups = $this->userMetaGroups->findBy([
			'user' => $cachedUser
		]);
		return array_map(function($userMetaGroup){
			return $userMetaGroup->getMetaGroup();
		}, $userMetaGroups);
	}


	public function getMetaGroupSubsByUser(Instance $instance, User $user){
		$cachedUser = $this->cachedUsers->findOneBy([
			'instance' => $instance,
			'userId' => $user->getId()
		]);
		if(!$cachedUser){
			return [];
		}

		$query = $this->storage->getPdo()->prepare('SELECT um.user_id, um.metaGroup_id, um.notify, m.name, m.description FROM usercache_metagroup um LEFT JOIN metagroup m ON m.id = um.metaGroup_id WHERE um.user_id = :usercache_id');
		$query->execute([ ':usercache_id' => $cachedUser->getId() ]);
		return array_map(function($row){
			$row->notify = (bool)$row->notify;
			return $row;
		}, $query->fetchAll(\PDO::FETCH_OBJ));
	}

	/**
	 * @param  User  $user
	 * @param  array $ids
	 * @return CachedUserMetaGroup[]
	 */
	public function updateJoins(Instance $instance, User $user, array $ids){
		$cachedUser = $this->cachedUsers->findOneBy([
			'instance' => $instance,
			'userId' => $user->getId()
		]);
		$userMetaGroups = $this->userMetaGroups->findBy([
			'user' => $cachedUser
		]);
		foreach($userMetaGroups as $userMetaGroup){
			$this->em->remove($userMetaGroup);
		}
		$this->em->flush();

		foreach($ids as $id){
			$userMetaGroup = new CachedUserMetaGroup;
			$userMetaGroup->setUser($cachedUser);
			$userMetaGroup->setMetaGroup( $this->em->getReference(MetaGroup::class, $id) );
			$this->em->persist($userMetaGroup);
		}
		$this->em->flush();

	}

}