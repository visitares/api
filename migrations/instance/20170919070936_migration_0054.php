<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0054 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('form')
			->addColumn('url', 'string', ['null' => false])
			->save();
	}
}