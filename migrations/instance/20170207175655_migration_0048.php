<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0048 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('user')
			->addColumn('metagroup_id', 'integer', [
				'signed' => true,
				'null' => true,
				'after' => 'modificationDate'
			])
			->addIndex(['metagroup_id'])
			->save();
	}
}