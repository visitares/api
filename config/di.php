<?php

namespace Visitares\Config;

use Auryn\Injector;
use PDO;
use Auryn\Provider;
use PHPMailer;
use Twig\{ Environment, Loader\FilesystemLoader };

/**
 * The dependency injection configuration will set up the autowiring.
 *
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
$provider = new Injector;

$provider->delegate('Auryn\Injector', function() use($provider){
	return $provider;
});

/**
 * Load local configurations.
 */
$dbConfig = include(__DIR__ . '/db.php');
$mailConfig = include(__DIR__ . '/mail.php');
$migrationConfig = include(__DIR__ . '/migration.php');

/**
 * Global params
 */
$provider->defineParam('useExistingDbs', true);

/**
 * Provider
 */
$provider->delegate(Provider::class, function() use($provider){
	return $provider;
});


/**
 * PDO
 */
$provider->define('Visitares\Service\Database\Migration', [
	':migrationConfig' => $migrationConfig
]);
$provider->define('PDO', [
	':dsn' => sprintf(
		'%s:dbname=%s;host=%s;port=%d;charset=%s',
		$dbConfig['default']['adapter'],
		$dbConfig['default']['db_prefix'] . 'visitares',
		$dbConfig['default']['host'],
		$dbConfig['default']['port'],
		$dbConfig['default']['charset']
	),
	':username' => $dbConfig['db_creator']['username'],
	':passwd' => $dbConfig['db_creator']['password'],
	':options' => [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION SQL_BIG_SELECTS=1'
	]
]);

/**
 * DatabaseManager
 */
$provider->define('Visitares\Service\Database\DatabaseManager', [
	':dbPrefix' => $dbConfig['default']['db_prefix']
]);

$provider->define('Visitares\UseCase\Instance\CreateInstance', [
	':dbPrefix' => $dbConfig['default']['db_prefix']
]);

/**
 * Random Generator
 */
$provider->define('RandomLib\Generator', [
	':sources' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
]);
$provider->delegate('RandomLib\Generator', function(){
	$factory = new \RandomLib\Factory;
	return $factory->getLowStrengthGenerator();
});

/**
 * EntityManagerFactory
 */
$provider->share('Visitares\Service\Database\DatabaseFacade');
$provider->share('Visitares\Storage\Facade\SystemStorageFacade');
$provider->share('Visitares\Storage\Facade\InstanceStorageFacade');
$provider->share('Visitares\Factory\Doctrine\EntityManagerFactory');
$provider->define('Visitares\Service\Database\DatabaseManager', [
	':dbPrefix' => $dbConfig['default']['db_prefix']
]);
$provider->delegate('Visitares\Factory\Doctrine\EntityManagerFactory', function() use($dbConfig){
	$systemConfig = [
		'mappingFiles' => APP_DIR_ROOT . '/orm/system',
		'database' => $dbConfig['default']['db_prefix'] . 'visitares',
		'proxyDir' => APP_DIR_ROOT . '/src/Entity/Proxy/System',
		'proxyNamespace' => 'Visitares\Entity\Proxy\System',
		'autogenerateProxyClasses' => false
	];
	$instanceConfig = [
		'mappingFiles' => APP_DIR_ROOT . '/orm/instance',
		'proxyDir' => APP_DIR_ROOT . '/src/Entity/Proxy/Instance',
		'proxyNamespace' => 'Visitares\Entity\Proxy\Instance',
		'autogenerateProxyClasses' => false
	];
	return new \Visitares\Factory\Doctrine\EntityManagerFactory($dbConfig, $systemConfig, $instanceConfig);
});

/**
 * Twig Template Engine
 */
$provider->define(FilesystemLoader::class, [
	':paths' => [
		APP_DIR_ROOT . '/res'
	]
]);
$provider->define(Environment::class, [
	'loader' => FilesystemLoader::class,
	':options' => [
		#'cache' => APP_DIR_ROOT . '/var/cache/twig'
	]
]);

/**
 * PHPMailer
 */
$provider->delegate('PHPMailer', function() use($mailConfig){
	$mailer = new PHPMailer;
	$mailer->isSMTP();
	$mailer->Host = $mailConfig['smtp']['host'];
	$mailer->Port = $mailConfig['smtp']['port'];
	$mailer->Username = $mailConfig['smtp']['username'];
	$mailer->Password = $mailConfig['smtp']['password'];
	$mailer->SMTPAuth = $mailConfig['smtp']['auth'];
	$mailer->SMTPSecure = $mailConfig['smtp']['secure'];
	$mailer->CharSet = $mailConfig['smtp']['charset'];
	$mailer->isHTML(true);
	$mailer->Subject = $mailConfig['smtp']['subject'];
	return $mailer;
});

/**
 * PhinxApplication
 */
$provider->define('Phinx\Console\PhinxApplication', [
	':version' => '1'
]);

// ..

/**
 * Return the provider object.
 */
return $provider;
