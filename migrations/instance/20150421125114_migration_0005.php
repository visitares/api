<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0005 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		/**
		 * Update `user` table
		 */
		$this->table('category')
			->addColumn('sort', 'integer', [
				'null' => true,
				'after' => 'isCopy'
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