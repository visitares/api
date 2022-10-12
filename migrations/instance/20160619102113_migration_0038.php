<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0038 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('client')
			->addColumn('sort', 'integer', [
				'null' => true
			])
			->addColumn('lineBreak', 'boolean', [
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