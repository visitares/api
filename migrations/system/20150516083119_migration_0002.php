<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0002 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		// Update `instance` table
		$this->table('instance')
			->addColumn('statsMinUserCount', 'integer', [
				'default' => 5
			])
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		// Update `instance` table
		$this->table('instance')
			->dropColumn('statsMinUserCount')
			->save();
	}
}