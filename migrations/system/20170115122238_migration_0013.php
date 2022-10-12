<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0013 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){

		$this->table('instance')
			->addColumn('master_id', 'integer', [
				'null' => true,
				'after' => 'modificationDate'
			])
			->addForeignKey('master_id', 'master', 'id', [
				'delete' => 'SET_NULL'
			])
			->save();

	}

	/**
	 * @return {void}
	 */
	public function down(){
		// ..
	}
}