<?php

namespace Visitares\Service\Database;

use InvalidArgumentException;
use PDO;

/**
 * This services is able to create or drop databases.
 *
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class DatabaseManager{
	/**
	 * @var PDO
	 */
	protected $pdo = null;

	/**
	 * @var string
	 */
	protected $dbPrefix = null;

	/**
	 * @param string $dbPrefix
	 * @param PDO $pdo
	 */
	public function __construct(
		$dbPrefix,
		PDO $pdo
	){
		$this->dbPrefix = $dbPrefix;
		$this->pdo = $pdo;
	}

	/**
	 * @param  string $name
	 * @return boolean
	 */
	public function create($name){
		$name = $this->escape($this->dbPrefix . $name);
		$this->pdo->exec('CREATE DATABASE IF NOT EXISTS ' . $name);
		return true;
	}

	/**
	 * @param  string $name
	 * @return boolean
	 */
	public function drop($name){
		$name = $this->escape($this->dbPrefix . $name);
		$this->pdo->exec('DROP DATABASE IF EXISTS ' . $name);
		return true;
	}

	/**
	 * @param  string $name
	 * @return string
	 */
	protected function escape($name){
		if(!is_string($name)){
			throw new InvalidArgumentException('`$name` should be a string.');
		}
		return sprintf('`%s`', str_replace('`', '``', $name));
	}
}