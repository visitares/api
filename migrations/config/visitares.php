<?php

/**
 * Migration configuration for the system database `visitares`.
 *
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
$config = include(__DIR__ . '/../../config/db.php');
return[
	'paths' => [
		'migrations' => '%%PHINX_CONFIG_DIR%%/../system'
	],
	'environments' => [
		'default_migration_table' => 'migration',
		'default_database' => 'development',
		'development' => [
			'adapter' => $config['default']['adapter'],
			'host' => $config['default']['host'],
			'name' => $config['default']['db_prefix'] . 'visitares',
			'user' => $config['db_creator']['username'],
			'pass' => $config['db_creator']['password'],
			'port' => $config['default']['port'],
			'charset' => $config['default']['charset'],
		]
	]
];