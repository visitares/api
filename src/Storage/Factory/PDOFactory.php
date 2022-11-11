<?php

namespace Visitares\Storage\Factory;

use PDO;

class PDOFactory{

	private $pool = [];

	public function __construct(){
		$this->config = require(APP_DIR_ROOT . '/config/db.php');
	}

	/**
	 * @param string $token
	 * @return \PDO
	 */
	public function fromToken(string $token){
		if(!array_key_exists($token, $this->pool)){
			$dsn = sprintf(
				'%s:dbname=%s;host=%s;port=%d;charset=%s',
				$this->config['default']['adapter'],
				$this->config['default']['db_prefix'] . $token,
				$this->config['default']['host'],
				$this->config['default']['port'],
				$this->config['default']['charset']
			);
			$this->pool[$token] = new PDO($dsn, $this->config['db_creator']['username'], $this->config['db_creator']['password'], [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION SQL_BIG_SELECTS=1',
			]);
		}
		return $this->pool[$token];
	}

}
