<?php

namespace Visitares\API\Statistics;

use Visitares\Service\Reports\FormReportService;
use Visitares\Service\Reports\ReportDataService;
use Visitares\Storage\Facade\InstanceStorageFacade;

class ReportsController{

	private $reportDataService = null;
	private $formReportService = null;

	public function __construct(
		InstanceStorageFacade $storage,
		$token,
		ReportDataService $reportDataService,
		FormReportService $formReportService
	){
		$this->storage = $storage;
		$this->storage->setToken($token);
		$this->reportDataService = $reportDataService;
		$this->formReportService = $formReportService;
	}

	public function getReports(array $filter = []){
		// ..
	}

	public function getFormReport(array $filter = []){
		$data = $this->reportDataService->fetch($filter);
		return $this->formReportService->create($data);
	}

	public function getFormTrend(array $filter = []){
		// ..
	}

}