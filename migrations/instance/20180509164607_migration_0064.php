<?php

use Phinx\Migration\AbstractMigration;

class Migration0064 extends AbstractMigration{
	public function change(){
		$this->table('categoryprocess')
			->addColumn('definition', 'text', [
        'null' => true,
        'after' => 'description',
			])
			->save();
	}
}
