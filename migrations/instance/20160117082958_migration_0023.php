<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0023 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('user')
			->addColumn('resetToken', 'string', [
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