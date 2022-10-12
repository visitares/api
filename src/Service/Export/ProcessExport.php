<?php

namespace Visitares\Service\Export;

use Visitares\Storage\Facade\InstanceStorageFacade;

class ProcessExport{

	private $storage = null;

	public function __construct(
		InstanceStorageFacade $storage
	){
		$this->storage = $storage;
	}

	public function export($id, $language = 'de'){
		if(!$process = $this->storage->categoryProcess->findById($id)){
			return null;
		}
		$submits = $this->storage->submit->findBy([
			'categoryProcess' => $process
		], [
			'creationDate' => 'ASC'
		]);

		$formIndex = [];
		foreach($submits as $submit){
			$formId = $submit->getForm()->getId();
			if(!isset($formIndex[$formId])){
				$formIndex[$formId] = (object)[
					'form' => $submit->getForm(),
					'submits' => []
				];
			}
			$formIndex[$formId]->submits[] = $submit;
		}

		$result = (object)[
			'campaign' => [
				'name' => $process->getCategory()->getName($language)
			],
			'process' => (object)[
				'name' => $process->getName(),
				'description' => $process->getDescription(),
				'definition' => $process->getDefinition(),
				'date' => $process->getCreationDate()
			],
			'data' => []
		];
		foreach($formIndex as $id => $context){
			$entry = (object)[];
			$entry->title = $context->form->getName($language);
			$entry->sort = $context->form->getSort();
			$entry->type = $context->form->getType();
			$entry->description = $context->form->getDescription($language);
			$entry->submits = [];

			foreach($context->submits as $submit){
				$submitEntry = [];
				$submitEntry['date'] = $submit->getCreationDate();
				$values = $submit->getValues();
				$valueIndex = [];

				foreach($values as $value){
					$valueIndex[$value->getInput()->getId()] = $value;
				}
			
				switch($entry->type){
					case 0: // checkbox
						$checkedValues = [];
						foreach($submit->getValues() as $value){
							if($value->isChecked()){
								$checkedValues[] = $value;
							}
						}
						$submitEntry[] = (object)[
							'q' => $context->form->getDescription($language),
							'a' => array_map(function($value) use ($language){
								return $value->getInput()->getLabel($language);
							}, $checkedValues)
						];
						break;

					case 1: // radio
						$checkedValue = null;
						foreach($submit->getValues() as $value){
							if($value->isChecked()){
								$checkedValue = $value;
							}
						}
						$submitEntry[] = (object)[
							'q' => $context->form->getDescription($language),
							'a' => $checkedValue->getInput()->getLabel($language)
						];
						break;

					case 2: // select
						foreach($context->form->getInputs() as $input){
							$submitEntry[] = (object)[
								'q' => $input->getLabel($language),
								'a' => $valueIndex[$input->getId()]->getOption()->getLabel($language)
							];
						}
						break;
						
						case 3: // text
							$message = $submit->getMessages()[0];
							$submitEntry = (object)[
								'message' => $message->getMessage()
							];
							break;
						
						case 5: // questions
							foreach($context->form->getInputs() as $input){
								$submitEntry[] = (object)[
									'q' => $input->getLabel($language),
									'a' => $valueIndex[$input->getId()]->getText(),
									'u' => $input->getUnit() ? $input->getUnit()->getLabel() : ''
								];
							}
							break;
				}

				$entry->submits[] = $submitEntry;
			}

			$result->data[] = $entry;
		}

		return $result;
	}

}
