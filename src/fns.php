<?php

namespace Visitares;

class fns{

	/**
	 * @param boolean $condition
	 * @param mixed $trueExpr
	 * @param mixed $falseExpr
	 * @return mixed
	 */
	public static function if(bool $condition, $trueExpr, $falseExpr){
		return $condition ? $trueExpr : $falseExpr;
	}

	/**
	 * @param string $prop
	 * @return callable
	 */
	public static function getter(string $prop){
		return function($arrayOrObject, $default = null) use($prop){
			if(is_array($arrayOrObject)){
				return $arrayOrObject[$prop] ?? $default;
			}
			if(is_object($arrayOrObject)){
				return $arrayOrObject->{$prop} ?? $default;
			}
			return $default;
		};
	}

	/**
	 * @param array $array
	 * @param callable $fn
	 * @param mixed $value
	 * @return mixed
	 */
	public static function fold(array $array, callable $fn, $value){
		return array_reduce($array, $fn, $value);
	}

	/**
	 * @param array $array
	 * @param callable $fn
	 * @return mixed
	 */
	public static function reduce(array $array, callable $fn){
		return array_reduce($array, $fn, $array);
	}

	/**
	 * @param array $array
	 * @param callable $fn
	 * @return mixed
	 */
	public static function map(array $array, callable $fn){
		return array_map($fn, $array, array_keys($array));
	}

	/**
	 * @param array $array
	 * @param callable $fn
	 * @return mixed
	 */
	public static function mapMap(array $array, callable $fn){
		$map = [];
		foreach($array as $key => $value){
			$map[$key] = $fn($value, $key);
		}
		return $map;
	}

	/**
	 * @param array $array
	 * @param mixed $needle
	 * @return void
	 */
	public static function findIndex(array $array, $needle){
		return fns::fold(array_keys($array), function($result, $index) use($array, $needle){
			return $needle === $array[$index] ? $index : $result;
		}, null);
	}

	/**
	 * @param array $array
	 * @param callable $fn
	 * @return array
	 */
	public static function filter(array $array, callable $fn){
		return array_filter($array, $fn);
	}

	/**
	 * @param array $array
	 * @param callable $fn
	 * @return mixed
	 */
	public static function find(array $array, callable $fn){
		return array_filter($array, $fn)[0] ?? null;
	}

	/**
	 * @param array $array
	 * @param callable $fn
	 * @return array
	 */
	public static function groupBy(array $array, callable $fn){
		return array_reduce($array, function($grouped, $element) use($fn){
			$key = $fn($element);
			if(!array_key_exists($key, $grouped)){
				$grouped[$key] = [];
			}
			$grouped[$key][] = $element;
			return $grouped;
		}, []);
	}

}
