<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0036 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('submit')
			->addColumn('token', 'string', [
				'length' => 16,
				'null' => true,
				'default' => null
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