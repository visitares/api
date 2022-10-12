<?php

namespace Visitares\CLI;

use ErrorException;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

/** Make the root directory globally available through constant. */
define('APP_DEBUG', true);
define('APP_DIR_ROOT', realpath(__DIR__));
chdir(APP_DIR_ROOT);

/** Error reporting */
if(APP_DEBUG){
	error_reporting(E_ALL);
	ini_set('display_errors', true);
}

/** Enable autoloading */
require_once(APP_DIR_ROOT . '/vendor/autoload.php');

/** Create a dependency provider instance. */
$provider = include(APP_DIR_ROOT . '/config/di.php');

/** Create EntityManager */
$factory = $provider->make('Visitares\Factory\Doctrine\EntityManagerFactory');
$entityManager = $factory->getSystemEntityManager();
#$entityManager = $factory->getInstanceEntityManager('kwiy');
return ConsoleRunner::createHelperSet($entityManager);