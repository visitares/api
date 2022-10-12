<?php

use Phinx\Migration\AbstractMigration;

class Migration0061 extends AbstractMigration{
	public function change(){

		$dbname = $this->query('SELECT database() AS dbname')->fetch(\PDO::FETCH_OBJ)->dbname;
		$token = null;
		if(preg_match('/([a-z]{4})$/', $dbname, $match)){
			list(, $token) = $match;
		}

		chdir(__DIR__);

		$path = '../../web/shared/user/' . $token . '/attachments';
		if(!file_exists($path)){
			@mkdir($path, 0777, true);
		}
		$path = realpath($path);

		$query = $this->query('SELECT * FROM attachment');
		while($row = $query->fetch(\PDO::FETCH_OBJ)){
			$file = sprintf('%s/%s', $path, hash('sha256', $row->id));
			file_put_contents($file, file_get_contents($row->data));
		}

	}
}
