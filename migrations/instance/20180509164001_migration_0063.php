<?php

use Phinx\Migration\AbstractMigration;

class Migration0063 extends AbstractMigration{
	public function change(){
		$this->table('category')
			->addColumn('enableProcessDefinitions', 'boolean', [
				'null' => false,
				'default' => false,
			])
			->save();
	}
}
