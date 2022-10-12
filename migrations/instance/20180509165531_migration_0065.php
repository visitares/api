<?php

use Phinx\Migration\AbstractMigration;

class Migration0065 extends AbstractMigration{
	public function change(){
		$this->table('usersubmitinstance')
			->addColumn('definition', 'text', [
        'null' => true,
        'after' => 'description',
			])
			->save();
	}
}
