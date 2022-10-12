<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0001 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		// Update `instance` table
		$this->table('instance')
			->addColumn('statsDayRange', 'integer', [
				'default' => 30
			])
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		// Update `instance` table
		$this->table('instance')
			->dropColumn('statsDayRange')
			->save();
	}
}