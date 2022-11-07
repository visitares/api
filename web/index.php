<?php

namespace Visitares\Bootstrap;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Visitares\Entity\AbstractEntity;
use Visitares\Components\Http\Routing\Exception\RessourceNotFoundException;
use Visitares\ErrorHandler;

/** Make the root directory globally available through constant. */
require(__DIR__ . '/../config/config.php');
chdir(APP_DIR_ROOT);

/** Error reporting */
if(APP_DEBUG){
	error_reporting(E_ALL);
	ini_set('display_errors', true);
}

/** Enable autoloading */
require_once(APP_DIR_ROOT . '/vendor/autoload.php');

/** Global error and exception handling. */
ErrorHandler::register();

/** Create a dependency provider instance. */
$provider = include(APP_DIR_ROOT . '/config/di.php');

/** Get a fully initialized router object from configuration. */
$router = include(APP_DIR_ROOT . '/config/router.php');

/** Create a request object. */
$request = Request::createFromGlobals();
$uri = implode(' ', [
	$request->getMethod(),
	$request->getPathInfo()
]);

/** Check referer */
$validReferers = [
	'app.visitares.',
	'cms.visitares.'
];
$allowOrigin = '*';


$host = '';
foreach(['HTTP_ORIGIN', 'HTTP_REFERER', 'SERVER_NAME'] as $referer){
	if(isset($_SERVER[$referer])){
		$host = $_SERVER[$referer];
		break;
	}
}

foreach($validReferers as $referer){
	if(strpos($host, $referer)){
		$allowOrigin = rtrim($host, '/');
		break;
	}
}

/** Create the base response. */
$response = new \Symfony\Component\HttpFoundation\Response('OK', Response::HTTP_OK, [
	'Access-Control-Allow-Origin' => '*',
	'Access-Control-Allow-Credentials' => 'true',
	'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE',
	'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept'
]);

try{
	/** Match the route and call the controller. */
	$route = $router->match($uri, $params);
	$inputJsonFormData = ($route['input'] ?? null) === 'json-form-data';

	/** Add request params to provider. */
	foreach($params as $key => $value){
		$provider->defineParam($key, $value);
	}
	foreach($request->request->all() as $key => $value){
		if($inputJsonFormData){
			$value = json_decode($value);
		}
		$provider->defineParam($key, $value);
	}
	foreach($request->files->all() as $key => $value){
		$provider->defineParam($key, $value);
	}
	$data = json_decode($request->getContent(), true);
	if($data){
		foreach($data as $key => $value){
			$provider->defineParam($key, $value);
		}
	}
	$provider->delegate(Request::class, function() use($request){
		return $request;
	});

	/** Inject services into the entity base class.  */
	$systemStorageFacade = $provider->make('Visitares\Storage\Facade\SystemStorageFacade');
	AbstractEntity::setSystemStorage($systemStorageFacade);
	$translationService = $provider->make('Visitares\Service\Translation\TranslationService');
	AbstractEntity::setTranslationService($translationService);

	/** Call the controller action */
	list($class, $method) = explode(' :: ', $route['ctrl']);
	$controller = $provider->make($class);
	$result = $provider->execute([$controller, $method]);

	/** Create response body */
	$format = isset($route['format']) ? $route['format'] : 'json';
	switch($format){
		case 'custom':
			if(!isset($result['type']) || !isset($result['data'])){
				throw new RessourceNotFoundException;
			}
			$type = $result['type'];
			$data = $result['data'];
			$response->headers->set('Content-Type', $type . ';charset=utf-8');
			$response->setContent($data);
			break;

		case 'json':
		default:
			$response->headers->set('Content-Type', 'application/json;charset=utf-8');
			$response->setContent(json_encode($result));
			break;
	}
} catch(RessourceNotFoundException $e){
	$response->setContent(json_encode(Response::HTTP_NOT_FOUND));
	$response->setStatusCode(Response::HTTP_NOT_FOUND);
}

$response->prepare($request);
$response->send();
