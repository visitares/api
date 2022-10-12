<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0022 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('media')
			->addColumn('length', 'string', ['null' => true, 'after' => 'filesize'])
			->save();
	}
}