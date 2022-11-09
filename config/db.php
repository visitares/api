<?php

namespace Visitares\Config;

/**
 * Database configuration
 *
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
return array(
	/**
	 * Default settings which can be applyed for all database connections.
	 */
	'default' => array(
		'driver' => 'pdo_mysql',
		'adapter' => 'mysql',
		'host' => '127.0.0.1',
		'port' => '3366',
		'charset' => 'utf8',
		'db_prefix' => 'db12298321-'
	),

	/**
	 * This account is used to create, drop or modify database schemas while running
	 * migrations or creating new instances. For security reasons it default CRUD
	 * actions will not be performed with this account at all. Please make sure the
	 * required permissions are granted.
	 */
	'db_creator' => array(
		'username' => 'root',
		'password' => 'other'
	),

	/**
	 * This account is used to perform simple CRUD actions. It should not be able to
	 * modify the database schema at all.
	 */
	'db_client' => array(
		'username' => 'root',
		'password' => 'other'
	)
);