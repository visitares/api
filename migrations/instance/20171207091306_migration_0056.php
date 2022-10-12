<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0056 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('form')
			->changeColumn('url', 'string', ['null' => true])
			->save();
	}
}
