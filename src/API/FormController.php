<?php

namespace Visitares\API;

use DateTime;

use Visitares\Entity\AbstractEntity;
use Visitares\Entity\CatalogEntry;
use Visitares\Entity\Form;
use Visitares\Entity\FormAdmin;
use Visitares\Entity\FormMedia;
use Visitares\Entity\Message;
use Visitares\Entity\Attachment;
use Visitares\Entity\Submit;
use Visitares\Entity\SubmitGroup;
use Visitares\Entity\Unread;
use Visitares\Entity\User;
use Visitares\Entity\UserSubmitInstance;
use Visitares\Entity\Value;
use Visitares\Entity\Translation;
use Visitares\Entity\Translated;

use Visitares\Entity\Factory\AttachmentFactory;
use Visitares\Entity\Factory\FormFactory;
use Visitares\Entity\Factory\InputFactory;
use Visitares\Entity\Factory\OptionFactory;

use Visitares\Service\FormAdminService;
use Visitares\Service\MaxScoreService;
use Visitares\Service\Media\MediaFormService;
use Visitares\Service\SubmitInstance\ScoreService;
use Visitares\Service\SubmitInstance\IsDoneService;

use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class FormController{
	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var string
	 */
	protected $token = null;

	/**
	 * @var FormFactory
	 */
	protected $factory = null;

	/**
	 * @var InputFactory
	 */
	protected $inputFactory = null;

	/**
	 * @var OptionFactory
	 */
	protected $optionFactory = null;

	/**
	 * @var FormAdminService
	 */
	protected $formAdminService = null;

	/**
	 * @var MaxScoreService
	 */
	protected $maxScoreService = null;

	/**
	 * @var MediaFormService
	 */
	protected $mediaFormService = null;

	/**
	 * @var ScoreService
	 */
	protected $scoreService = null;

	/**
	 * @var IsDoneService
	 */
	protected $isDoneService = null;

	/**
	 * @param DatabaseFacade        $database
	 * @param SystemStorageFacade   $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string                $token
	 * @param FormFactory           $factory
	 * @param InputFactory          $inputFactory
	 * @param OptionFactory         $optionFactory
	 * @param FormAdminService      $formAdminService
	 * @param MaxScoreService       $maxScoreService
	 * @param MediaFormService      $mediaFormService
	 * @param ScoreService          $scoreService
	 * @param IsDoneService         $isDoneService
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		FormFactory $factory,
		InputFactory $inputFactory,
		OptionFactory $optionFactory,
		FormAdminService $formAdminService,
		MaxScoreService $maxScoreService,
		MediaFormService $mediaFormService,
		ScoreService $scoreService,
		IsDoneService $isDoneService
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		$this->maxScoreService = $maxScoreService;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
			$this->maxScoreService->setStorage($this->storage);
		}
		$this->token = $token;
		$this->factory = $factory;
		$this->inputFactory = $inputFactory;
		$this->optionFactory = $optionFactory;
		$this->formAdminService = $formAdminService;
		$this->formAdminService->setEntityManager($this->storage->getEntityManager());
		$this->mediaFormService = $mediaFormService;
		$this->scoreService	= $scoreService;
		$this->isDoneService	= $isDoneService;
	}

	/**
	 * @param  string $token
	 * @return Form[]
	 */
	public function getAll($token){
		$forms = $this->instance ? $this->storage->form->findAll() : [];
		$repo = $this->storage->getRepository(FormMedia::class);
		foreach($forms as $form){
			$relations = $repo->findBy([
				'form' => $form->getId()
			]);
			$form->setMedia(array_map(function($relation){
				return $relation->getMedia();
			}, $relations));
		}
		return $forms;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return Form
	 */
	public function getById($token, $id){
		$form = $this->instance ? $this->storage->form->findById($id) : null;
		if($form){
			$repo = $this->storage->getRepository(FormMedia::class);
			$relations = $repo->findBy([
				'form' => $form->getId()
			]);
			$form->setMedia(array_map(function($relation){
				return $relation->getMedia();
			}, $relations));

			$object = json_decode(json_encode($form));
			$object->messageAdmins = array_map(function($formAdmin){
				return $formAdmin->getUser()->getId();
			}, $this->formAdminService->getAdmins($form, FormAdmin::ROLE_MESSAGE_ADMIN));

			if($form->getCatalogEntry()){
				$object->catalogEntry = $form->getCatalogEntry()->toArray();
				$object->catalogEntry['attributes'] = array_map(function($attribute){
					$export = $attribute->toArray();
					$export['attribute'] = $attribute->getAttribute()->toArray();
					return $export;
				}, $form->getCatalogEntry()->getAttributes()->toArray());
				$object->catalogEntry['catalog'] = $form->getCatalogEntry()->getCatalog()->toArray();
				$object->catalogEntry['catalog']['attributes'] = $form->getCatalogEntry()->getCatalog()->getAttributes()->toArray();
			}

			return $object;
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @param  array $context
	 * @return void
	 */
	public function getByCategory($token, $id, array $context = []){
		if($this->instance && $category = $this->storage->category->findById($id)){
			$forms = $this->storage->getEntityManager()
				->getRepository('Visitares\Entity\Form')
				->findBy([
					'category' => $id
				]);

			$repo = $this->storage->getRepository(FormMedia::class);
			foreach($forms as $form){
				$relations = $repo->findBy([
					'form' => $form->getId()
				]);
				$form->setMedia(array_map(function($relation){
					$media = $relation->getMedia()->toArray();
					$media['relationDate'] = $relation->getCreationDate()->format('Y-m-d H:i:s');
					$media['new'] = $relation->getCreationDate()->format('Y-m-d H:i:s') >= (new DateTime)->modify('-1 week')->format('Y-m-d H:i:s');
					return $media;
				}, $relations));
			}

			$forms = $this->storage->form->prepareForms($forms, $this->instance);
			$forms = array_map(function($form) use($context){
				$export = $form->toArray();
				if($form->getCatalogEntry()){
					$catalogExport = $form->getCatalogEntry()->getCatalog()->toArray();
					$export['catalog'] = (object)[
						'allowInstructions' => $catalogExport['allowInstructions']
					];
					$export['catalogEntry'] = $form->getCatalogEntry()->toArray();
					$export['catalogEntry']['attributes'] = array_map(function($attribute){
						$export = $attribute->toArray();
						$export['attribute'] = $attribute->getAttribute()->toArray();
						return $export;
					}, $form->getCatalogEntry()->getAttributes()->toArray());
					$export['catalogEntry']['attributes'] = array_map(function($attribute){
						return[
							'name' => $attribute['attribute']['name'],
							'value' => $attribute['value'],
						];
					}, $export['catalogEntry']['attributes']);
					$export['catalogEntry'] = [
						'name' => $export['catalogEntry']['name'],
						'attributes' => $export['catalogEntry']['attributes'],
					];

					// get instructions
					if(isset($context['user'])){
						$export['instructions'] = $this->storage->getEntityManager()->getRepository(UserSubmitInstance::class)->findBy([
							'isInstructed' => true,
							'instructedForm' => $form->getId(),
							'webinstructor' => $context['user']
						]);
						foreach($export['instructions'] as $index => $instruction){
							$user = $instruction->getUser();
							$export['instructions'][$index] = $instruction->toArray();
							$export['instructions'][$index]['user'] = [
								'id' => $user->getId(),
								'firstname' => $user->getFirstname(),
								'lastname' => $user->getLastname(),
								'email' => $user->getEmail(),
								'username' => $user->getUsername(),
							];
						}
					}

				}
				return $export;
			}, $forms);

			return $forms;
		}
		return [];
	}

	/**
	 * @param  integer $id
	 * @param  string $language
	 * @return array
	 */
	public function getStats($id, $language){
		if($this->instance){
			$em = $this->storage->getEntityManager();

			$query = sprintf('
				SELECT	s FROM Visitares\Entity\Submit s
				JOIN	s.form f
				WHERE	f.id = :form
					AND s.creationDate >= DATE_SUB(CURRENT_DATE(), %d, \'day\')
			', $this->instance->getStatsDayRange());

			$params = [
				'form' => $id,
				// 'language' => $language
			];

			$dql = $em->createQuery($query)->setParameters($params);
			$submits = $dql->getResult();

			if(count($submits)){
				$stats = [];
				$form = $this->storage->form->findById($id);
				foreach($form->getInputs() as $input){
					if($form->getType() === Form::TYPE_SELECT){
						$stats[$input->getId()] = [
							'options' => []
						];
						foreach($input->getOptions() as $option){
							$stats[$input->getId()]['options'][$option->getId()] = [
								'count' => 0
							];
						}
					} else{
						$stats[$input->getId()] = [
							'count' => 0
						];
					}
				}
				foreach($submits as $submit){
					foreach($submit->getValues() as $value){
						$input = $value->getInput();
						if($form->getType() === Form::TYPE_SELECT){
							$option = $value->getOption();
							if($option){
								$ok = $stats[$input->getId()]['options'][$option->getId()]['count'] ?? null;
								if($ok !== null){
									$stats[$input->getId()]['options'][$option->getId()]['count']++;
								}
							}
						} else{
							if($value->getChecked()){
								$ok = $stats[$input->getId()]['count'] ?? null;
								if($ok !== null){
									$stats[$input->getId()]['count']++;
								}
							}
						}
					}
				}

				return $stats;
			}
			return [];
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  array $data
	 * @return Form
	 */
	public function store($token, array $data){
		if($this->instance){
			$form = $this->factory->fromArray($data);
			$this->storage->store($form);
			$this->storage->apply();

			foreach($data['inputs'] as $inputData){
				$inputData['form'] = $form->getId();
				$input = $this->inputFactory->fromArray($inputData);
				$this->storage->store($input);
				$this->storage->apply();

				foreach($inputData['options'] as $optionValues){
					$option = $this->optionFactory->fromArray($optionValues);
					$input->addOption($option);
					$this->storage->apply();
				}
			}

			$this->storage->getEntityManager()->refresh($form);
			$form->setMaxScore( $this->maxScoreService->getFormMaxScore($form) );
			$this->storage->apply();

			$category = $this->storage->category->findById($form->getCategory()->getId());
			$category->setMaxScore( $this->maxScoreService->getCategoryMaxScore($category) );
			$this->storage->apply();

			// Set admins
			$this->formAdminService->setAdmins($form, $data['messageAdmins'], FormAdmin::ROLE_MESSAGE_ADMIN);

			// Set media relations
			if($form->getType() === Form::TYPE_MEDIA){
				$this->mediaFormService->updateMediaRelations($form, $data['media']);
			} else{
				$this->mediaFormService->removeAllMediaRelations($form);
			}

			return $this->getById($token, $form->getId());
		}
		return null;
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @param  array $data
	 * @return Form
	 */
	public function update($token, $id, array $data){
		if($this->instance && $form = $this->storage->form->findById($id)){
			$form->setModificationDate(new DateTime);

			if($category = $this->storage->category->findById($data['category'])){
				$form->setCategory($category);
			}

			if($data['catalogEntry']['id'] ?? null){
				$form->setCatalogEntry($this->storage->getReference(CatalogEntry::class, $data['catalogEntry']['id']));
			} else {
				$form->setCatalogEntry(null);
			}

			$form->setIsActive($data['isActive']);
			$form->setType($data['type']);
			$form->setSort(isset($data['sort']) ? $data['sort'] : null);
			$form->setPublicStats($data['publicStats']);
			$form->setUrl($data['url']);
			$form->setEmbedUrl($data['embedUrl']);

			if(!isset($data['singleSubmitOnly'])){
				$form->setSingleSubmitOnly(false);
			} else{
				$form->setSingleSubmitOnly($data['singleSubmitOnly']);
			}

			// $form->setMedia($data['media']);

			foreach($data['name'] as $langCode => $value){
				$form->setName($langCode, $value);
			}
			foreach($data['description'] as $langCode => $value){
				$form->setDescription($langCode, $value);
			}
			foreach($data['shortDescription'] as $langCode => $value){
				$form->setShortDescription($langCode, $value);
			}
			foreach($data['htmlText'] ?? [] as $langCode => $value){
				$form->setHtmlText($langCode, $value);
			}


			// Set admins
			$this->formAdminService->setAdmins($form, $data['messageAdmins'], FormAdmin::ROLE_MESSAGE_ADMIN);


			// Set media relations
			if($form->getType() === Form::TYPE_MEDIA){
				$this->mediaFormService->updateMediaRelations($form, $data['media']);
			} else{
				$this->mediaFormService->removeAllMediaRelations($form);
			}


			$inputIds = [];
			foreach($data['inputs'] as $input){
				if(isset($input['id'])){
					$inputIds[] = $input['id'];
				}
			}
			foreach($form->getInputs() as $input){
				if(!in_array($input->getId(), $inputIds)){
					$form->removeInput($input);
					$this->storage->remove($input);
				}
			}

			foreach($data['inputs'] as $inputData){
				if(!isset($inputData['id'])){
					$inputData['form'] = $form->getId();
					$input = $this->inputFactory->fromArray($inputData);

					$optionIds = [];

					foreach($inputData['options'] as $optionData){
						if(isset($optionData['id'])){
							$optionIds[] = $optionData['id'];
						}
					}
					foreach($input->getOptions() as $option){
						if(!in_array($option->getId(), $optionIds)){
							$input->removeOption($option);
							$this->storage->remove($option);
						}
					}

					foreach($inputData['options'] as $optionData){
						if(!isset($optionData['id'])){
							$option = $this->optionFactory->fromArray($optionData);
						} elseif($option = $this->storage->option->findById($optionData['id'])){
							$option->setCoefficient($optionData['coefficient']);
							foreach($optionData['label'] as $langCode => $value){
								$option->setLabel($langCode, $value);
							}
						}
						if($option){
							$input->addOption($option);
						}
					}
				} elseif($input = $this->storage->input->findById($inputData['id'])){
					$input->setRequired($inputData['required']);
					$input->setSort($inputData['sort']);
					$input->setCoefficient($inputData['coefficient']);
					$input->setType($inputData['type'] ?? 'text');

					if($inputData['unit']){
						$unit = $this->storage->getReference('Visitares\Entity\Unit', $inputData['unit']);
						$input->setUnit($unit);
					} else{
						$input->setUnit(null);
					}

					foreach($inputData['label'] as $langCode => $value){
						$input->setLabel($langCode, $value);
					}

					$optionIds = [];

					foreach($inputData['options'] as $optionData){
						if(isset($optionData['id'])){
							$optionIds[] = $optionData['id'];
						}
					}
					foreach($input->getOptions() as $option){
						if(!in_array($option->getId(), $optionIds)){
							$input->removeOption($option);
							$this->storage->remove($option);
						}
					}

					foreach($inputData['options'] as $optionData){
						if(!isset($optionData['id'])){
							$option = $this->optionFactory->fromArray($optionData);
						} elseif($option = $this->storage->option->findById($optionData['id'])){
							$option->setSort($optionData['sort']);
							$option->setCoefficient($optionData['coefficient']);
							foreach($optionData['label'] as $langCode => $value){
								$option->setLabel($langCode, $value);
							}
						}
						if($option){
							$input->addOption($option);
						}
					}
				}
				if($input){
					$this->storage->store($input);
				}
			}

			$this->storage->apply();

			$form->setMaxScore( $this->maxScoreService->getFormMaxScore($form));
			foreach($data['documents'] ?? [] as $document){
				if(!($document['id'] ?? null)){
					continue;
				}
				$documentEntity = $this->storage->attachment->findById($document['id']);
				$documentEntity->setSort($document['sort'] ?? null);
			}
			$this->storage->apply();

			$category = $this->storage->category->findById($form->getCategory()->getId());
			$category->setMaxScore( $this->maxScoreService->getCategoryMaxScore($category) );
			$this->storage->apply();

			return $this->getById($token, $form->getId());
		}
		return null;
	}

	/**
	 * @param  array $data
	 * @return boolean
	 */
	public function submit($data){
		$form = $this->storage->form->findById($data['formId']);
		$language = $this->storage->language->findByCode($data['language']);

		$user = null;
		if($data['userId']){
			$user = $this->storage->user->findById($data['userId']);
		}

		$categoryProcess = null;
		if(isset($data['processToken']) && $data['processToken']){
			$categoryProcess = $this->storage->categoryProcess->findOne([
				'token' => $data['processToken']
			]);
		}

		$submit = new Submit;
		$submit->setForm($form);
		$submit->setLanguage($language);
		if($user){
			$submit->setUser($user);
		}
		if($categoryProcess){
			$submit->setCategoryProcess($categoryProcess);
		}
		if(isset($data['submitInstance'])){
			$submit->setSubmitInstance( $this->storage->getReference(UserSubmitInstance::class, $data['submitInstance']) );
		}
		if(isset($data['sessionToken'])){
			$submit->setToken($data['sessionToken']);
		}
		$this->storage->store($submit);
		$this->storage->apply();


		// Store group informations for statistics
		foreach($data['groups'] as $groupId){
			if($group = $this->storage->group->findById($groupId)){
				$submitGroup = new SubmitGroup;
				$submitGroup->setSubmit($submit);
				$submitGroup->setGroup($group);
				$this->storage->store($submitGroup);
			}
		}
		$this->storage->apply();

		if($form->getType() === Form::TYPE_TEXT){
			// Check for dirty words
			$words = $this->storage->dirtyWord->findAll();
			$code = $language->getCode();
			$match = strtolower($data['message']);
			foreach($words as $word){
				if(strpos($match, strtolower($word->getWord($code))) !== false){
					return[
						'error' => true,
						'code' => 'MESSAGE_CONTAINS_DIRTY_WORDS'
					];
				}
			}

			// Create message
			$message = new Message;
			$message->setUser($user);
			$message->setSubmit($submit);
			$message->setMessage($data['message']);
			$this->storage->store($message);
			$this->storage->apply();

			// Create attachments
			if($form->getType() === Form::TYPE_TEXT && $data['attachments']){
				$factory = new AttachmentFactory;
				foreach($data['attachments'] as $attachment){
					$attachment = $factory->fromArray($attachment);
					$attachment->setMessage($message);
					$this->storage->store($attachment);
					$this->storage->apply();

					$attachment->setData($attachment['data'] ?? null, $this->token);
				}
			}

			// Mark messages as unread for every user
			// related to that category
			$users = $this->markMessageAsUnread($user, $form, $submit, $message);
			return $users;

		} else{
			// Add values
			foreach($data['values'] as $valueData){
				$input = $this->storage->input->findById($valueData['inputId']);
				if($valueData['optionId']){
					$option = $this->storage->option->findById($valueData['optionId']);
				} else{
					$option = null;
				}

				$value = new Value;
				$value->setSubmit($submit);
				$value->setInput($input);
				$value->setOption($option);

				if($input->getType() === 'date'){
					if(is_string($valueData['text'])){
						$date = substr($valueData['text'], 0, 10);
						$value->setText((new DateTime($date))->format('d.m.Y'));
					}
				} else {
					$value->setText($valueData['text']);
				}
				$value->setChecked($valueData['checked']);

				$this->storage->store($value);
			}
			$this->storage->apply();
		}

		$this->storage->clear();

		if($submit->getSubmitInstance()){
			$this->scoreService->updateScore($submit->getSubmitInstance());
			$this->isDoneService->updateIsDone($submit->getSubmitInstance());
		}

		return true;
	}

	/**
	 * @param  User    $user
	 * @param  Form    $form
	 * @param  Submit  $submit
	 * @param  Message $message
	 * @return void
	 */
	protected function markMessageAsUnread($user, Form $form, Submit $submit, Message $message){
		$dql = '
			SELECT
				u
			FROM
				Visitares\Entity\User u
			LEFT JOIN
				u.groups g
			LEFT JOIN
				g.categories c
			WHERE
				u.id != :user
			AND c.id = :category';

		$em = $this->storage->getEntityManager();
		$query = $em->createQuery($dql);
		$query->setParameter('user', $user ? $user->getId() : -1);
		$query->setParameter('category', $form->getCategory()->getId());
		$users = $query->getResult();

		foreach($users as $user){
			// Create new row
			$unread = new Unread;
			$unread->setUser($user);
			$unread->setSubmit($submit);
			$unread->setMessage($message);
			$this->storage->store($unread);

			// Apply changes
			$this->storage->apply();
		}
	}

	/**
	 * @param  string $token
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($token, $id){
		if($this->instance && $form = $this->storage->form->findById($id)){

			$this->storage->remove($form);
			$this->storage->apply();

			$category = $this->storage->category->findById($form->getCategory()->getId());
			$category->setMaxScore( $this->maxScoreService->getCategoryMaxScore($category) );
			$this->storage->apply();

			return true;
		}
		return false;
	}

	/**
	 * @param  string $token
	 * @param  array  $ids
	 * @return boolean
	 */
	public function removeMany($token, array $ids){
		if($this->instance){

			$forms = $this->storage->form->findBy([
				'id' => $ids
			]);
			foreach($forms as $form){
				$this->storage->remove($form);
				$this->storage->apply();
				
				$category = $this->storage->category->findById($form->getCategory()->getId());
				$category->setMaxScore( $this->maxScoreService->getCategoryMaxScore($category) );
				$this->storage->apply();
			}
			return true;
		}
		return false;
	}

	/**
	 * @param  string  $instanceToken
	 * @param  integer $categoryId
	 * @return boolean
	 */
	public function share($id, $instanceToken, $categoryId, $returnObj = false){
		if($this->instance && $form = $this->storage->form->findById($id)){
			$this->storage->setToken($instanceToken);

			// Create language map
			$languages = $this->storage->language->findAll();
			$langByCode = [];
			foreach($languages as $language){
				// $langByCode[$language->getCode()] = $language;
				$langByCode[$language->getCode()] = $this->storage->getReference('Visitares\Entity\Language', $language->getId());
			}

			// Clone form object
			$copy = new Form;
			$copy->setStatsLocked($form->getStatsLocked());
			$copy->setIsActive($form->getIsActive());
			$copy->setType($form->getType());
			$copy->setSort($form->getSort());
			$copy->setPublicStats($form->getPublicStats());
			$copy->setMaxScore($form->getMaxScore());
			$copy->setSingleSubmitOnly($form->getSingleSubmitOnly());
			$copy->setUrl($form->getUrl());
			$copy->setEmbedUrl($form->getEmbedUrl() ? $form->getEmbedUrl() : false);

			// Clone translations
			$this->cloneTranslations(['name', 'description', 'shortDescription'], $form, $copy);

			// Set category reference
			$category = $this->storage->category->findById($categoryId);
			$copy->setCategory($category);

			// Copy inputs
			foreach($form->getInputs() as $input){
				$inputCopy = clone $input;
				$inputCopy->setForm($copy);
				$this->cloneTranslations(['label'], $input, $inputCopy);
				$this->storage->store($inputCopy);

				// Copy options
				foreach($input->getOptions() as $option){
					$optionCopy = clone $option;
					$optionCopy->setInput($inputCopy);
					$this->cloneTranslations(['label'], $option, $optionCopy);
					$this->storage->store($optionCopy);
				}
			}

			// Persist it
			$this->storage->store($copy);
			$this->storage->apply();

			if($returnObj){
				return $copy;
			}
		}
		return true;
	}

	/**
	 * @param  array          $fields
	 * @param  AbstractEntity $source
	 * @param  AbstractEntity $dest
	 * @return void
	 */
	protected function cloneTranslations(array $fields, AbstractEntity $source, AbstractEntity $dest){
		foreach($fields as $field){
			$setter = sprintf('set%sTranslation', ucfirst($field));
			$getter = sprintf('get%sTranslation', ucfirst($field));
			if($source->$getter() !== null){
				$dest->$setter($this->cloneTranslation($source->$getter()));
			}
		}
	}

	/**
	 * @param  Translation $this->source
	 * @return Translation
	 */
	protected function cloneTranslation(Translation $source){
		$translation = new Translation;
		foreach($source->getTranslations() as $entry){
			$language = $this->storage->language->findByCode($entry->getLanguage()->getCode());
			$langRef = $this->storage->getReference('Visitares\Entity\Language', $language->getId());

			$translated = new Translated;
			$translated->setLanguage($langRef);
			$translated->setTranslation($translation);
			$translated->setContent($entry->getContent());
			$translation->add($translated);
		}
		return $translation;
	}

	/**
	 * @return void
	 */
	public function copy($token, $id){
		$form = $this->storage->form->findById($id);
		$categoryId = $form->getCategory()->getId();
		$copy = $this->share($id, $token, $categoryId, true);

		// copy form media
		$formMediaRepo = $this->storage->getRepository(FormMedia::class);
		if($formMediaRelations = $formMediaRepo->findBy(['form' => $id])){
			foreach($formMediaRelations as $formMediaRelation){
				$newFormMediaRelation = new FormMedia;
				$newFormMediaRelation->setForm($copy);
				$newFormMediaRelation->setMedia($formMediaRelation->getMedia());
				$this->storage->store($newFormMediaRelation);
			}
			$this->storage->apply();
		}

		// copy attachments
		$attachmentRepo = $this->storage->getRepository(Attachment::class);
		if($attachments = $attachmentRepo->findBy(['form' => $id])){
			foreach($attachments as $attachment){
				$newAttachment = new Attachment();
				$newAttachment->setForm($copy);
				$newAttachment->setCreationDate(new \DateTime);
				$newAttachment->setName($attachment->getName());
				$newAttachment->setMimetype($attachment->getMimetype());
				$newAttachment->setSize($attachment->getSize());
				$newAttachment->setSort($attachment->getSort());
				$this->storage->store($newAttachment);
				$this->storage->apply();

				$newAttachment->copyAttachment($attachment, $token);
			}
			$this->storage->apply();
			$this->storage->clear();
		}

		return $this->getById($token, $copy->getId());
	}

}
