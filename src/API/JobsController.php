<?php

namespace Visitares\API;

use Visitares\Entity\Form;
use Visitares\Service\MaxScoreService;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Entity\Instance;
use Visitares\Entity\User;
use Visitares\Entity\CachedUser;
use Visitares\Entity\Group;
use Visitares\Entity\CachedGroup;

use Visitares\Service\Cache\UserCacheService;
use Visitares\Service\Cache\GroupCacheService;

class JobsController{

	private $systemStorage = null;
	private $storage = null;
	private $maxScoreService = null;

	public function __construct(
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		MaxScoreService $maxScoreService
	){
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		$this->maxScoreService = $maxScoreService;
	}



	public function recalcMaxScores(){
		$instances = $this->systemStorage->instance->findAll();
		foreach($instances as $instance){
			if(!$instance->getToken()){
				continue;
			}
			$this->storage->setToken($instance->getToken());
			$this->maxScoreService->setStorage($this->storage);

			$forms = $this->storage->form->findAll();
			foreach($forms as $form){

				switch($form->getType()){
					case Form::TYPE_CHECKBOX:
					case Form::TYPE_RADIO:
						foreach($form->getInputs() as $input){
							if($input->getCoefficient() === null){
								$input->setCoefficient(1);
							}
						}
						break;

					case Form::TYPE_SELECT:
						foreach($form->getInputs() as $input){
							foreach($input->getOptions() as $option){
								if($option->getCoefficient() === null){
									$option->setCoefficient(1);
								}
							}
						}
						break;
				}

				$form->setMaxScore( $this->maxScoreService->getFormMaxScore($form) );
			}
			$this->storage->apply();

			$categories = $this->storage->category->findAll();
			foreach($categories as $category){
				$category->setMaxScore( $this->maxScoreService->getCategoryMaxScore($category) );
			}
			$this->storage->apply();
		}
	}



	public function importImages(){
		$iterator = new \RecursiveDirectoryIterator(APP_DIR_ROOT . '/res/icons', \RecursiveDirectoryIterator::SKIP_DOTS);
		$iteratorIterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

		$currentGroup = null;
		$imageMap = [];

		foreach($iteratorIterator as $file){

			if($file->isDir()){
				$imageGroup = new \Visitares\Entity\ImageGroup;
				$imageGroup->setCreationDate(new \DateTime);
				$imageGroup->setLabel(utf8_encode($file->getFilename()));
				$imageGroup->setType(\Visitares\Entity\ImageGroup::TYPE_ICON);
				$this->systemStorage->store($imageGroup);
				$this->systemStorage->apply();

				$currentGroup = $imageGroup;
			}

			if($file->isFile()){
				$image = new \Visitares\Entity\Image;
				$image->setCreationDate(new \DateTime);
				$image->setGroupId($currentGroup->getId());

				$content = file_get_contents($file->getPathname());
				$checksum = hash('sha256', $content . $image->getCreationDate()->format('Y-m-d H:i:s'));
				$image->setFilename($checksum);
				copy($file->getPathname(), APP_DIR_ROOT . '/web/shared/images/' . $checksum . '.jpg');
				
				$this->systemStorage->store($image);
				$this->systemStorage->apply();

				$imageMap[$file->getFilename()] = $image;
			}
		}

		$instances = $this->systemStorage->instance->findAll();
		foreach($instances as $instance){
			if(array_key_exists($instance->getBackground(), $imageMap)){
				$instance->setBackgroundId($imageMap[$instance->getBackground()]->getId());
				$this->systemStorage->apply();
			}

			$this->storage->setToken($instance->getToken());
			$categories = $this->storage->category->findAll();
			foreach($categories as $category){
				if(array_key_exists($category->getIcon(), $imageMap)){
					$category->setIconId($imageMap[$category->getIcon()]->getId());
					$this->storage->apply();
				}
			}
		}
	}



	public function createUserCache(UserCacheService $userCacheService){
		set_time_limit(60 * 15); // 15 minutes, lets go..

		$em = $this->systemStorage->getEntityManager();
		$instancesRepo = $em->getRepository(Instance::class);

		$instances = $instancesRepo->findAll();
		foreach($instances as $instance){
			if($instance->getDomain() !== null){
				$this->storage->setToken($instance->getToken());
				$iem = $this->storage->getEntityManager();
				$usersRepo = $iem->getRepository(User::class);

				$users = $usersRepo->findAll();

				foreach($users as $user){
					$userCacheService->update($instance, $user);
				}

			}
		}
	}



	public function createGroupCache(GroupCacheService $groupCacheService){
		set_time_limit(60 * 15); // 15 minutes, lets go..

		$em = $this->systemStorage->getEntityManager();
		$instancesRepo = $em->getRepository(Instance::class);

		$instances = $instancesRepo->findAll();
		foreach($instances as $instance){
			if($instance->getDomain() !== null){

				$this->storage->setToken($instance->getToken());
				$iem = $this->storage->getEntityManager();
				$groupsRepo = $iem->getRepository(Group::class);

				$groups = $groupsRepo->findAll();

				foreach($groups as $group){
					$groupCacheService->update($instance, $group);
				}

			}
		}
	}

}