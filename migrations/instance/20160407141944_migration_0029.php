<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0029 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('category')
			->addColumn('icon_id', 'integer', [
				'null' => true,
				'after' => 'icon'
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