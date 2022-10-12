<?php

namespace Visitares\Service\SubmitInstance;

use Visitares\Entity\UserSubmitInstance;
use Visitares\Storage\Facade\InstanceStorageFacade;

class ScoreService{

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function __construct(
		InstanceStorageFacade $storage
	){
		$this->storage = $storage;
	}

	/**
	 * @param  UserSubmitInstance $submitInstance
	 * @return void
	 */
	public function updateScore(UserSubmitInstance $submitInstance){
		$category = $submitInstance->getCategory();
		$submits = $this->storage->submit->findBy([
			'submitInstance' => $submitInstance->getId()
		]);

		$score = 0;

		foreach($submits as $submit){
			foreach($submit->getValues() as $value){

				if($value->getOption()){
					if($value->getOption()->getCoefficient()){
						$score += $value->getOption()->getCoefficient();
					}
				} else if($value->getInput()){
					if($value->getInput()->getCoefficient()){
						$score += $value->getInput()->getCoefficient();
					}
				}
			}
		}

		$submitInstance->setScore($score);
		$this->storage->apply();
	}

}