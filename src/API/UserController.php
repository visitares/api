<?php

namespace Visitares\API;

use DateTime;
use Exception;
use RandomLib\Generator;
use PHPMailer;
use Twig\Environment as Twig;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Visitares\Service\Database\DatabaseFacade;
use Visitares\Entity\User;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Factory\UserFactory;
use Visitares\Service\Cache\UserCacheService;
use Visitares\Service\User\UserMetaGroupService;
use Visitares\Storage\Factory\PDOFactory;


/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class UserController{
	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * \PDO
	 */
	protected $pdo = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var UserFactory
	 */
	protected $factory = null;

	/**
	 * @var Instance
	 */
	protected $instance = null;

	/**
	 * @var Generator
	 */
	protected $random = null;

	/**
	 * @var UserCache
	 */
	protected $userCache = null;

	/**
	 * @var UserMetaGroupService
	 */
	protected $userMetaGroupService = null;

	/**
	 * @var PDOFactory
	 */
	protected $pdoFactory = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param \PDO $pdo
	 * @param InstanceStorageFacade $storage
	 * @param string $token
	 * @param UserFactory $factory
	 * @param Generator $random
	 * @param UserCacheService $userCache
	 * @param UserMetaGroupService $userMetaGroupService
	 * @param PDOFactory $pdoFactory
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		\PDO $pdo,
		InstanceStorageFacade $storage,
		$token,
		UserFactory $factory,
		Generator $random,
		UserCacheService $userCache,
		UserMetaGroupService $userMetaGroupService,
		PDOFactory $pdoFactory
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->pdo = $pdo;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
		$this->factory = $factory;
		$this->random = $random;
		$this->userCache = $userCache;
		$this->userMetaGroupService = $userMetaGroupService;
		$this->pdoFactory = $pdoFactory;
		$this->appConfig = require(APP_DIR_ROOT . '/config/app.php');
	}

	/**
	 * @return User[]
	 */
	public function getAll(){
		session_write_close();
		return $this->instance ? $this->storage->user->find([
			'anonymous' => false
		]) : [];
	}

	/**
	 * @return User[]
	 */
	public function getAllAnonymous(){
		return $this->instance ? $this->storage->user->find([
			'anonymous' => true
		]) : [];
	}

	/**
	 * @param  User $user
	 * @return array
	 */
	protected function serve(User $user){
		$array = $user->toArray();
		if(!$user->isAnonymous()){
			$array['metaGroups'] = array_map(function($metaGroup){
				return $metaGroup->getId();
			}, $this->userMetaGroupService->getMetaGroupsByUser($this->instance, $user));
		}
		return $array;
	}

	/**
	 * @param  string $id
	 * @return User
	 */
	public function getById($id){
		return $this->instance ? $this->serve($this->storage->user->findById($id)) : null;
	}

	/**
	 * @param  string $username
	 * @return User
	 */
	public function getByUsername($username){
		return $this->instance ? $this->serve($this->storage->user->findByUsername($username)) : null;
	}

	/**
	 * @param  array $data
	 * @return User
	 */
	public function store(array $data){
		if($this->instance){
			$user = $this->factory->fromArray($data);

			if($anotherUser = $this->storage->user->findByUsername($user->getUsername())){
				return[
					'error' => 'USERNAME_ALREADY_EXISTS'
				];
			}

			$this->storage->store($user);
			$this->storage->apply();

			if(!$user->isAnonymous()){
				$this->userCache->update($this->instance, $user);
			}

			$this->userMetaGroupService->updateJoins($this->instance, $user, isset($data['metaGroups']) ? $data['metaGroups'] : []);

			return $this->serve($user);
		}
		return null;
	}

	/**
	 * @param  string $username
	 * @param  array $data
	 * @return User
	 */
	public function update($username, array $data){
		if($this->instance && $user = $this->storage->user->findByUsername($username)){
			$user->setModificationDate(new DateTime);
			$user->setIsActive($data['isActive']);
			$user->setUsername($data['username']);

			if(isset($data['configGroup']) && $data['configGroup']){
				$user->setConfigGroup($this->storage->group->findById($data['configGroup']));
			} else{
				$user->setConfigGroup(null);
			}

			$user->setDescription($data['description']);
			$user->setWelcomeText(isset($data['welcomeText']) ? $data['welcomeText'] : null);

			if($data['anonymous']){
				$user->setActiveFrom($data['activeFrom'] ? new DateTime($data['activeFrom']) : null);
				$user->setActiveUntil($data['activeUntil'] ? new DateTime($data['activeUntil']) : null);
				if($language = $this->storage->language->findByCode($data['language'])){
					$user->setLanguage($language);
				}
			} else{
				$user->setRole($data['role']);
				$user->setInstances($data['instances'] ? implode(',', $data['instances']) : null);
				if(isset($data['salutation'])){
					$user->setSalutation($data['salutation']);
				}
				if(isset($data['title'])){
					$user->setTitle($data['title']);
				}
				if(isset($data['firstname'])){
					$user->setFirstname($data['firstname']);
				}
				if(isset($data['lastname'])){
					$user->setLastname($data['lastname']);
				}
				if(isset($data['email'])){
					$user->setEmail($data['email']);
				}
				if(isset($data['phone'])){
					$user->setPhone($data['phone']);
				}
				$user->setCompany($data['company']);
				$user->setDepartment($data['department']);
			}

			foreach($user->getGroups() as $group){
				if(!in_array($group->getId(), $data['groups'])){
					$user->removeGroup($group);
				}
			}
			foreach($data['groups'] as $id){
				if($group = $this->storage->group->findById($id)){
					$user->addGroup($group);
				}
			}
			if($language = $this->storage->language->findByCode($data['language'])){
				$user->setLanguage($language);
			}

			$this->storage->apply();

			if(!$user->isAnonymous()){
				$this->userCache->update($this->instance, $user);
			}

			$this->userMetaGroupService->updateJoins($this->instance, $user, isset($data['metaGroups']) ? $data['metaGroups'] : []);

			return $this->serve($user);
		}
		return null;
	}

	/**
	 * @return void
	 */
	public function resetPassword($username){
		if($this->instance && $user = $this->storage->user->findByUsername($username)){
			$password = $this->random->generateString(8, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!$%&?#_-*+~');
			$user->setPassword(
				password_hash(
					$password,
					$this->appConfig->password->algo,
					$this->appConfig->password->options
				)
			);
			$this->storage->apply();
			return $password;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  string $domain
	 * @param  string $username
	 * @return void
	 */
	public function recoverPassword(PHPMailer $mailer, Twig $twig, $domain, $username){
		if($instance = $this->systemStorage->instance->findByDomain($domain)){
			$this->storage->setToken($instance->getToken());
			if($user = $this->storage->user->findByUsername($username)){
				$password = $this->random->generateString(8, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#$!');
				$user->setPassword(
					password_hash(
						$password,
						$this->appConfig->password->algo,
						$this->appConfig->password->options
					)
				);
				$this->storage->apply();
				$data = [
					'fullname' => $user->getFirstname() . ' ' . $user->getLastname(),
					'password' => $password,
					'url' => 'https://app.visitares.com/#/login/' . $instance->getDomain()
				];
				$mailer->From = 'noreply@visitares.com';
				$mailer->FromName = 'visitares';
				$mailer->addAddress($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname());
				$mailer->Subject = 'Ihr neues visitares Kennwort';
				$mailer->Body = $twig->render('html/mails/password.html', $data);
				$mailer->AltBody = $mailer->Body;
				return $mailer->send() ? true : false;
			}
		}
		return false;
	}

	/**
	 * @param  string $username
	 * @return boolean
	 */
	public function remove($username){
		if($this->instance && $user = $this->storage->user->findByUsername($username)){
			$this->userCache->remove($this->instance, $user);
			$this->storage->remove($user);
			$this->storage->apply();
		}
		return false;
	}

	/**
	 * @param  string $token
	 * @param  array  $ids
	 * @return boolean
	 */
	public function removeMany($token, array $ids){
		$report = [];

		if($this->instance){
			$users = $this->storage->user->find([
				'id' => $ids
			]);
			foreach($users as $user){
				$removed = true;
				$id = $user->getId();
				try{
					$this->userCache->remove($this->instance, $user);
					$this->storage->remove($user);
					$this->storage->apply();
				} catch(Exception $e){
					$removed = false;
				}
				$report[] = [
					'id' => $id,
					'removed' => $removed,
					'user' => $user
				];
			}
			return $report;
		}
		return $report;
	}

	/**
	 * @param  string $token
	 * @param  int $id
	 * @param  UploadedFile $file
	 * @return array
	 */
	public function uploadPhoto($token, $id, $file = null, $remove = false){

		if(!$file || $file === 'null'){
			return false;
		}

		$mime = $file->getMimeType();

		if(is_string($remove)) $remove = json_decode($remove);
		$filename = APP_DIR_ROOT . '/web/shared/avatar/' . $token . '/' . $id . '.png';

		if($remove){
			if(file_exists($filename)){
				@unlink($filename);
			}
			return true;
		}

		if($file instanceof UploadedFile){
			$file->move(APP_DIR_ROOT . '/web/shared/avatar/' . $token . '/', $id . '.png');
			$this->createThumbnail($filename, $mime);
			return true;
		}

		return false;
	}

	/**
	 * @param  string $filename
	 * @return void
	 */
	protected function createThumbnail($filename, $mime){
		list($width, $height) = getimagesize($filename);
		$crop = $width > $height ? $height : $width;

		if($mime === 'image/png'){
			$source = imagecreatefrompng($filename);
		} else{
			$source = imagecreatefromjpeg($filename);
		}

		$tmp = imagecrop($source, [
			'x' => $width / 2 - $crop / 2,
			'y' => $height / 2 - $crop / 2,
			'width' => $crop,
			'height' => $crop
		]);

		$image = imagecreatetruecolor(200, 200);
		imagecopyresized($image, $tmp, 0, 0, 0, 0, 200, 200, $crop, $crop);

		imagepng($image, $filename, 0);

	}

	/**
	 * @param  array $settings
	 * @return boolean
	 */
	public function updateSettings($id, array $settings){

		if(!$user = $this->storage->user->findById($id)){
			return false;
		}

		$user->setDefaultAppScreen($settings['defaultAppScreen']);
		$this->storage->apply();

		/**
		 * Update subs for groups
		 */
		if(isset($settings['groupSubs'])){
			$pdo = $this->pdoFactory->fromToken($this->instance->getToken());
			$query = $pdo->prepare('UPDATE group_user SET sub = FALSE WHERE user_id = :UserId');
			$query->execute([ ':UserId' => $id ]);

			$groupIds = array_map(function($groupSub){
				return $groupSub['sub'] ? intval($groupSub['group_id']) : null;
			}, $settings['groupSubs']);
			$groupIds = array_values(array_filter($groupIds));

			if($groupIds){
				$query = $pdo->prepare('UPDATE group_user SET sub = TRUE WHERE user_id = :UserId AND group_id IN (' . implode(', ', $groupIds) . ')');
				$query->execute([ ':UserId' => $id ]);
			}
		}

		/**
		 * Update subs for meta groups
		 */
		if(isset($settings['metaGroupSubs'])){
			$subs = array_filter($settings['metaGroupSubs'], function($metaGroupSub){
				return $metaGroupSub['notify'];
			});
			$ids = array_map(function($sub){
				return $sub['metaGroup_id'];
			}, $subs);
			$cachedUser = $this->userCache->getCachedUser($this->instance, $user);
			if($cachedUser){
				$query = $this->pdo->prepare('UPDATE usercache_metagroup SET notify = false WHERE user_id = :usercache_id');
				$query->execute([ 'usercache_id' => $cachedUser->getId() ]);

				if($ids){
					$query = $this->pdo->prepare('UPDATE usercache_metagroup SET notify = true WHERE user_id = :usercache_id AND metaGroup_id IN (' . implode(', ', $ids) . ')');
					$query->execute([ 'usercache_id' => $cachedUser->getId() ]);
				}
			}
		}

		return true;
	}


}
