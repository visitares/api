<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0053 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('user')
			->addColumn('defaultAppScreen', 'integer', ['null' => true])
			->save();
	}
}