<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0027 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('input')
			->addColumn('unit', 'integer', [
				'null' => true,
				'default' => null,
				'after' => 'coefficient'
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