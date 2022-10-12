<?php

namespace Visitares;

class Util{

	/**
	 * @param  string $str
	 * @param  array  $params
	 * @return string
	 */
	public static function replace($str, array $params){
		return str_replace(
			array_keys($params),
			array_values($params),
			$str
		);
	}

	/**
	 * @param  array $array
	 * @param  array $keys
	 * @return array
	 */
	public static function removeKeys(array $array, array $keys){
		foreach($keys as $key){
			unset($array[$key]);
		}
		return $array;
	}

}