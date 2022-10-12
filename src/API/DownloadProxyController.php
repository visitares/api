<?php

namespace Visitares\API;

class DownloadProxyController{

	/**
	 * @param  string $filename
	 * @return mixed
	 */
	public function download($filename){

		$url = $_GET['url'] ?? null;

		if(!$url){
			exit;
		}

		$location = realpath(APP_DIR_ROOT . '/web' . $_GET['url']);

		if(strpos($location, realpath(APP_DIR_ROOT . '/web')) === false){
			return null;
		}

		header('Content-Type: ' . $_GET['mime']);
		readfile($location);
		exit;
	}

}