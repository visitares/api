<?php

namespace Visitares\API;

use RandomLib\Generator;

class CsvController{
	/**
	 * @var Generator
	 */
	protected $generator = null;

	/**
	 * @param Generator $generator,
	 */
	public function __construct(
		Generator $generator
	){
		$this->generator = $generator;
	}

	/**
	 * @param  array $data
	 * @return string
	 */
	public function create($token, array $data, $wrap = true){
		$csv = '';
		$count = count($data);
		foreach($data as $index => $row){
			$csv .= implode(';', array_map(function($value) use($wrap){
				if(!$wrap){
					return $value;
				}
				if(is_numeric($value)){
					$value = str_replace([',', '.'], ['', ','], $value);
					return $value;
				}
				return '="' . $value . '"';
			}, $row));
			if($index < $count - 1){
				$csv .= "\n";
			}
		}

		$dir = sprintf('%s/var/user/csv/%s', APP_DIR_ROOT, $token);
		$id = hash('sha512', $csv);

		if(!file_exists($dir)){
			@mkdir($dir);
		}
		file_put_contents(sprintf('%s/%s.csv', $dir, $id), $csv);

		return $id;
	}

	/**
	 * @param  string $token
	 * @param  string $id
	 * @return void
	 */
	public function download($token, $id){
		$location = sprintf('%s/var/user/csv/%s/%s.csv', APP_DIR_ROOT, $token, $id);
		if(file_exists($location)){
			header('Content-Type: application/csv;charset=utf-8');
			header('Content-Disposition: attachment; filename="export.csv"');
			$content = file_get_contents($location);
			$content = mb_convert_encoding($content, 'UTF-16LE', 'UTF-8');
			echo $content;
			unlink($location);
			exit;
		} else{
			return null;
		}
	}
}
