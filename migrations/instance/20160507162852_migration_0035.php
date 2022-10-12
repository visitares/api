<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0035 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('form')
			->addColumn('media', 'text', [
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