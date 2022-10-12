<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0028 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('value')
			->addColumn('text', 'text', [
				'null' => true,
				'default' => null,
				'after' => 'checked'
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