<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0033 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('category')
			->addColumn('processesEnabled', 'boolean', [
				'null' => false,
				'default' => false
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