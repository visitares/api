<?php

namespace Visitares\API\Statistics;

use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

class StatisticsFormController{
	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

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
	 * @return array
	 */
	public function getForm(){
		$result = [
			'clients' => [],
			'campaigns' => [],
			'groups' => [],
			'units' => $this->storage->unit->findAll(),
			'filterWords' => $this->getFilteredWords(),
		];

		$clients = $this->storage->client->findAll();
		$result['clients'] = $clients;

		$campaigns = $this->storage->category->findAll();
		foreach($campaigns as $campaign){
			$campaignArray = $this->prepareCampaign($campaign);
			$campaignArray['client'] = $campaign->getClient()->getId();

			$forms = $this->storage->form->findBy(['category' => $campaign, 'type' => [0,1,2,3,4,5,6,7]]);
			foreach($forms as $form){
				if(in_array($form->getType(), [4, 6])){
					continue;
				}
				$campaignArray['forms'][] = $this->prepareForm($form);
			}

			$result['campaigns'][] = $campaignArray;
		}

		$groups = $this->storage->group->findAll();
		$instance = $this->systemStorage->instance->findByToken($this->storage->getToken());
		$minCount = $instance->getStatsMinUserCount();
		foreach($groups as $group){
			//if(count($group->getUsers()) >= $minCount){
				$result['groups'][] = $this->prepareGroup($group);
			//}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getFilteredWords(){
		$settings = $this->instance->getSettings();
		if(!$settings){
			return [];
		}
		return array_map('trim', explode(',', $settings->stats->filterInputWithLabels ?? ''));
	}

	/**
	 * @param  Category $campaign
	 * @return array
	 */
	protected function prepareCampaign($campaign){
		$campaign = $campaign->toArray();
		foreach([
			'creationDate',
			'modificationDate',
			'client',
			'groups',
			'isActive',
			'isCopy',
			'beginDate',
			'endDate',
			'inputLockHours',
			'lineBreak',
			'icon',
			'description'
		] as $prop){
			unset($campaign[$prop]);
		}
		$campaign['forms'] = [];
		//$campaign['groups'] = [];
		return $campaign;
	}

	/**
	 * @param  Form $group
	 * @return array
	 */
	protected function prepareGroup($group){
		$array = $group->toArray();

		foreach([
			'creationDate',
			'modificationDate',
			'categories',
			'users',
			'description'
		] as $prop){
			unset($array[$prop]);
		}

		return $array;
	}

	/**
	 * @param  Form $form
	 * @return array
	 */
	protected function prepareForm($form){
		$form = $form->toArray();
		foreach([
			'creationDate',
			'modificationDate',
			'statsLocked',
			'publicStats',
			'documents',
			//'description'
		] as $prop){
			unset($form[$prop]);
		}

		$form['inputs'] = array_map(function($input){
			$options = [];
			foreach($input->getOptions() as $option){
				$options[] = $this->prepareOption($option);
			}
			$input = $input->toArray();
			$input['options'] = $options;
			return $input;
		}, $form['inputs']);

		foreach([
			'creationDate',
			'modificationDate',
			'form'
		] as $prop){
			foreach($form['inputs'] as &$input){
				unset($input[$prop]);
			}
		}

		$form['campaign'] = $form['category'];
		unset($form['category']);

		return $form;
	}

	protected function prepareOption($option){
		$option = $option->toArray();

		foreach([
			'creationDate',
			'modificationDate'
		] as $prop){
			unset($option[$prop]);
		}

		return $option;
	}
}
