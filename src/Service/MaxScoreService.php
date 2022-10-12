<?php

namespace Visitares\Service;

use Visitares\Entity\Category;
use Visitares\Entity\Form;
use Visitares\Entity\Input;
use Visitares\Storage\Facade\InstanceStorageFacade;

class MaxScoreService{
	private $storage = null;

	/**
	 * @param InstanceStorageFacade $storage
	 */
	public function setStorage(InstanceStorageFacade $storage){
		$this->storage = $storage;
	}

	/**
	 * @param  Category $category
	 * @return integer
	 */
	public function getCategoryMaxScore(Category $category){
		$forms = $this->storage->form->findBy([
			'category' => $category
		]);
		$maxScore = 0;
		foreach($forms as $form){
			switch($form->getType()){
				case Form::TYPE_CHECKBOX:
				case Form::TYPE_RADIO:
				case Form::TYPE_SELECT:
					$maxScore += $form->getMaxScore() !== null ? $form->getMaxScore() : $this->getFormMaxScore($form);
					break;
			}
		}
		return $maxScore;
	}

	/**
	 * @param  Form $form
	 * @return integer
	 */
	public function getFormMaxScore(Form $form){
		$maxScore = 0;

		switch($form->getType()){
			case Form::TYPE_CHECKBOX:
			case Form::TYPE_RADIO:
				foreach($form->getInputs() as $input){
					$maxScore += $input->getCoefficient() !== null ? $input->getCoefficient() : 1;
				}
				break;

			case Form::TYPE_SELECT:
				foreach($form->getInputs() as $input){
					$maxScore += $this->getMaxOptionsCoefficient($input);
				}
				break;
		}

		return $maxScore;
	}

	/**
	 * @param  Option[] $options
	 * @return integer
	 */
	public function getMaxOptionsCoefficient(Input $input){
		$coefficient = null;
		foreach($input->getOptions() as $option){
			if($option->getCoefficient() !== null){
				if($coefficient === null || $option->getCoefficient() > $coefficient){
					$coefficient = $option->getCoefficient();
				}
			}
		}
		return $coefficient !== null ? $coefficient : 1;
	}

}