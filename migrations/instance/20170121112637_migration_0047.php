<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0047 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('media')
			->addColumn('master_id', 'integer', [
				'signed' => false,
				'null' => true,
				'after' => 'modificationDate'
			])
			->save();
	}
}