<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0057 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){

		$this->table('input')
			->addColumn('sort', 'integer', ['null' => false, 'default' => 1, 'after' => 'form_id'])
			->save();

		$this->table('inputoption')
			->addColumn('sort', 'integer', ['null' => false, 'default' => 1, 'after' => 'input_id'])
			->save();

	}
}
