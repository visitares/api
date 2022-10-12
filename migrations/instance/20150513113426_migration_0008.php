<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0008 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		/**
		 * Update `form` table
		 */
		$this->table('user')
			->addColumn('instances', 'string', [
				'null' => true,
				'length' => 200,
				'after' => 'role'
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