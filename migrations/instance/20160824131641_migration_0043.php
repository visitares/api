<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0043 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('media')
			->addColumn('ext', 'string', [
				'length' => 20,
				'null' => true
			])
			->save();
	}
}