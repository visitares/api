<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0031 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('unit')
			->changeColumn('modificationDate', 'datetime', [
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