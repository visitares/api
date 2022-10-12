<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0010 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		/**
		 * Update `form` table
		 */
		$this->table('form')
			->addColumn('isActive', 'boolean', [
				'null' => false,
				'default' => true,
				'after' => 'category_id'
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