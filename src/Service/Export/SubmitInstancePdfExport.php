<?php

namespace Visitares\Service\Export;

use DateTime;
use HTML2PDF;
use Twig\Environment as Twig;

class SubmitInstancePdfExport{

	private $twig = null;

	public function __construct(
		Twig $twig
	){
		$this->twig = $twig;
	}

	public function create($export){
		$html2pdf = new HTML2PDF('P','A4','de');
		$html = $this->twig->render('html/export/process.html', [
			'export' => $export,
			'today' => new DateTime
		]);
		$html2pdf->writeHTML($html);

		header('Content-Type: application/pdf');
		$html2pdf->Output('export.pdf');
	}

}