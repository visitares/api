<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0010 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('instance')
			->addColumn('messageModule', 'boolean', [
				'default' => true
			])
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		$this->dropTable('config');
	}
}