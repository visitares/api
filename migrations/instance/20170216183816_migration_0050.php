<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0050 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('user')
			->removeColumn('metagroup_id')
			->save();
	}
}