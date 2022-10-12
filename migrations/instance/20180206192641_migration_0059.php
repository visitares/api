<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0059 extends AbstractMigration{

	/**
	 * @return void
	 */
	public function up(){
		$this->table('input')
			->addColumn('required', 'boolean', [ 'null' => false, 'default' => true ])
			->save();
	}
}
