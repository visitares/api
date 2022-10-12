<?php

use Phinx\Migration\AbstractMigration;

class Migration0062 extends AbstractMigration{
	public function change(){
		$this->table('attachment')
			->changeColumn('data', 'binary', [
				'null' => true,
				'limit' => 16777215, // mediumblob
			])
			->save();
	}
}
