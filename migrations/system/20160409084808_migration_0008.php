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
		$this->table('instance')
			->addColumn('logoffTimer', 'integer', [
				'null' => true,
				'after' => 'messageAdministration'
			])
			->addColumn('cmsConfig', 'text', [
				'null' => true,
				'after' => 'settings'
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