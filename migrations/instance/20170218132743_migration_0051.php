<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0051 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('user')
			->addColumn('salutation', 'integer', ['null' => true, 'after' => 'password'])
			->save();

		$this->table('user')
			->addColumn('title', 'integer', ['null' => true, 'after' => 'salutation'])
			->save();
	}
}