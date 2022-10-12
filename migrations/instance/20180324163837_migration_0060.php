<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0060 extends AbstractMigration{

	/**
	 * @return void
	 */
	public function up(){
		$this->table('group_user')
			->addColumn('sub', 'boolean', [ 'null' => false, 'default' => true ])
			->save();
	}
}
