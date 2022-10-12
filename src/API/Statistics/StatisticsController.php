<?php

namespace Visitares\API\Statistics;

use DateTime;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Submit;
use Visitares\Entity\Value;
use Visitares\Entity\Instance;

use Doctrine\Common\Util\Debug;

class StatisticsController{

	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var Instance
	 */
	protected $instance = null;

	/**
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token
	){
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		$this->storage->setToken($token);
		$this->instance = $this->systemStorage->instance->findByToken($token);
	}

	/**
	 * @param  array $filter
	 * @return array
	 */
	public function getStatistics(array $filter){

		set_time_limit(20);

		$em = $this->storage->getEntityManager();

		$query = $em->createQuery('
			SELECT s
			FROM	Visitares\Entity\Submit s
			WHERE	s.form IN (:forms)
				AND	s.creationDate >= :from
				AND s.creationDate <= :to
		');
		$query->setParameter('forms', $filter['forms']);
		$query->setParameter('from', new DateTime($filter['from']));
		$query->setParameter('to', new DateTime($filter['to']));

		$submits = $query->getResult();
		$submits = array_map(function($submit){
			return $this->prepareSubmit($submit);
		}, $submits);

		$submits = json_decode(json_encode($submits));

		if(isset($filter['useWordFilter']) && $filter['useWordFilter']){
			$submits = $this->filterInputsAndOptionsByWords($submits, $this->getFilteredWords());
		}

		return $submits;
	}


	/**
	 * @return array
	 */
	private function getFilteredWords(){
		$settings = $this->instance->getSettings();
		if(!$settings){
			return [];
		}
		if(!isset($settings->stats) && !isset($settings->stats->filterInputWithLabels)){
			return [];
		}
		return array_map('trim', explode(',', $settings->stats->filterInputWithLabels));
	}

	/**
	 * @param array $submits
	 * @return array
	 */
	private function filterInputsAndOptionsByWords(array $submits, array $words, string $lang = 'de'){
		return array_filter($submits, function($submit) use($words, $lang){
			return array_filter($submit->values, function($value) use($words, $lang){

				if($value->input && $value->input->options){
					$value->input->options = array_filter($value->input->options, function($option) use($words, $lang){
						return !in_array($option->label->{$lang}, $words);
					});
				}

				# unset($value->input->options);

				if($value->option){
					if(in_array($value->option->label->{$lang}, $words)){
						return false;
					}
				} elseif($value->input){
					if(in_array($value->input->label->{$lang}, $words)){
						return false;
					}
				}

				return true;
			});
		});
	}


	/**
	 * @param  Submit $submit
	 * @return array
	 */
	public function prepareSubmit($submit){

		$values = [];
		foreach($submit->getValues() as $value){
			$value = $value->toArray();
			foreach([
				'creationDate',
				'modificationDate',
				'submit'
			] as $prop){
				unset($value[$prop]);
			}
			$values[] = $value;
		}

		$message = null;
		foreach($submit->getMessages() as $message){
			$message = $message->getMessage();
			break;
		}

		$submit = $submit->toArray();
		$submit['values'] = $values;
		$submit['message'] = $message;

		$em = $this->storage->getEntityManager();
		$submitGroups = $em->getRepository('Visitares\Entity\SubmitGroup')->findBy([
			'submit' => $submit['id']
		]);

		$submit['groups'] = array_map(function($submitGroup){
			return $submitGroup->getGroup()->getId();
		}, $submitGroups);

		return $submit;
	}
}
