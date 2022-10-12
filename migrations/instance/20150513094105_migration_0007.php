<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0007 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		/**
		 * Update `form` table
		 */
		$this->table('usergroup')
			->addColumn('isDefault', 'boolean', [
				'default' => false,
				'after' => 'modificationDate'
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