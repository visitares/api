<?php

namespace Visitares\Service\Reports;

use Visitares\Entity\Form;

class FormReportService{

	public function create(array $rows){
		$charts = [];

		foreach($rows as $row){
			$year = $row->getCreationDate()->format('Y');
			if(!isset($charts[$year])){
				$charts[$year] = $this->createEmptyChart($row->getForm());
			}
		}

		//print_r($charts);


		/*
		return[
			'charts' => [
				[
					'year' => 2015,
					'type' => 'monthly',
					'data' => [
						[1, 2, 3], // A
						[1, 2, 3], // B
						[1, 2, 3] // C
					]
				],
				[
					'year' => 2016,
					'type' => 'monthly',
					'data' => [
						[1, 2, 3], // A
						[1, 2, 3], // B
						[1, 2, 3] // C
					]
				]
			],
			'trends' => [..]
		];
		*/
		return $rows;
		return 'FormReportService::create() not implemented';
	}

	protected function createEmptyChart(Form $form){
		$chart = [];
		$emptyRow = array_fill(0, 12, 0);

		switch($form->getType()){
			case 0:
			case 1:
				foreach($form->getInputs() as $input){
					$chart[] = $emptyRow;
				}
				break;

			case 2:
				break;

			case 3:
				break;
		}

		return $chart;
	}

}