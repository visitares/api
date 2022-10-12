<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0024 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('user')
			->addColumn('resetTokenExpire', 'datetime', [
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