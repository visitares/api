<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0055 extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->table('form')
			->addColumn('embedUrl', 'boolean', ['null' => false, 'default' => false])
			->save();
	}
}