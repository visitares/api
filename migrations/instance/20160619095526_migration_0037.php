<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0037 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('client')
			->addColumn('isActive', 'boolean', [
				'default' => true
			])
			->addColumn('icon_id', 'integer', [
				'signed' => false,
				'null' => true
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