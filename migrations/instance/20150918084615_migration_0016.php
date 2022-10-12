<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0016 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('category')
			->addColumn('lineBreak', 'boolean', [
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