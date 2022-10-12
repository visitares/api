<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0021 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('instance')
			->addColumn('showMyProcesses', 'boolean', [
				'null' => false,
				'default' => true
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