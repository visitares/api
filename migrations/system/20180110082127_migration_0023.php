<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0023 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('instance')
            ->addColumn('notifyEmail', 'string', ['null' => true])
            ->save();
	}
}
