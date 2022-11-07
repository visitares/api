<?php

namespace Visitares\Components\Http\Routing;

use ArrayAccess;
use Visitares\Components\Http\Routing\Exception\RessourceNotFoundException;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Router implements ArrayAccess{
	/**
	 * @var array
	 */
	protected $routes = [];

	/**
	 * @var boolean
	 */
	protected $compiled = false;

	/**
	 * @var string
	 */
	protected $defaultType = 'string';

	/**
	 * @var array
	 */
	protected $types = [];

	/**
	 * Initializes the router
	 */
	public function __construct(){
		$this->registerBuiltInTypes();
	}

	/**
	 * @return void
	 */
	protected function registerBuiltInTypes(){
		// Any
		$this->types['*'] = [
			'.*?',
			function($value){
				return (string)$value;
			}
		];

		// String (default)
		$this->types['string'] = [
			'[a-zA-Z]*',
			function($value){
				return (string)$value;
			}
		];

		// Integer
		$this->types['int'] = [
			'\d+',
			function($value){
				return (int)$value;
			}
		];

		// Alphanumeric
		$this->types['alnum'] = [
			'[A-Za-z0-9_-]*',
			function($value){
				return (string)$value;
			}
		];

		$this->types['username'] = [
			'[A-Za-z0-9_\-\.@]*',
			function($value){
				return (string)$value;
			}
		];

	}

	/**
	 * @param  string $name
	 * @param  string $pattern
	 * @param  Closure $formatter
	 * @return void
	 */
	public function alias($name, $pattern, $formatter = null){
		$this->types[$name] = [
			$pattern,
			$formatter
		];
	}

	/**
	 * @param  string $uri
	 * @return boolean
	 */
	public function offsetExists($uri): bool {
		return array_key_exists($uri, $this->routes);
	}

	/**
	 * @param  string $uri
	 * @return array|null
	 */
	public function offsetGet($uri) : mixed {
		return $this->routes[$uri];
	}

	/**
	 * @param  string $uri
	 * @param  array $values
	 * @return void
	 */
	public function offsetSet($uri, $values): void {
		$this->routes[$uri] = $values;
	}

	/**
	 * @param  string $uri
	 * @return void
	 */
	public function offsetUnset($uri): void {
		unset($this->routes[$uri]);
	}

	/**
	 * @return void
	 */
	protected function compileRoutes(){
		foreach($this->routes as $route => $values){
			$pattern = $route;
			$routeParams = [];

			// Extract the parameters
			preg_match_all('/{(.*?)}/', $route, $matches);
			if($matches){
				$params = $matches[1];

				// Parse types
				$this->routeParams[$route] = [];
				foreach($params as $index => $param){
					list($name, $type) = explode(':', $param);
					if(!$type){
						$type = $this->defaultType;
					}
					$routeParams[$name] = $type;
					$pattern = str_replace($matches[0][$index], ':' . $name, $pattern);
				}
			}

			// Quote the pattern
			$pattern = preg_quote($pattern, '~');
			$pattern = str_replace('\:', ':', $pattern);

			// Replace param with regular expressions
			foreach($routeParams as $name => $typeName){
				$typePattern = $this->types[$typeName][0];
				$pattern = str_replace(':' . $name, sprintf('(%s)', $typePattern), $pattern);
			}
			$routeParams = array_keys($routeParams);

			// Add delimiters
			$pattern = sprintf('~^%s$~', $pattern);

			// Replace local route with compiled version
			$this->routes[$pattern] = [
				'values' => $values,
				'routeParams' => $routeParams
			];
			unset($this->routes[$route]);
		}
	}

	/**
	 * @param  string $uri
	 * @param  array &$routeParams
	 * @return array
	 */
	public function match($uri, &$routeParams){
		if(!$this->compiled){
			$this->compileRoutes();
		}

		foreach($this->routes as $pattern => $data){
			if(preg_match($pattern, $uri, $matches)){
				array_shift($matches);
				$routeParams = [];
				foreach($data['routeParams'] as $index => $name){
					$routeParams[$name] = $matches[$index];
				}
				return $data['values'];
			}
		}

		throw new RessourceNotFoundException(sprintf('No match found for "%s"', $uri));
	}
}