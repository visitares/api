<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0049 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('media')
			->addColumn('instance_token', 'string', [
				'null' => true,
				'length' => '4',
				'after' => 'master_id'
			])
			->save();
	}
}