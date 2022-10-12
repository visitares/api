<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0015 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('user')
			->addColumn('activeFrom', 'date', [
				'null' => true
			])
			->addColumn('activeUntil', 'date', [
				'null' => true
			])
			->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		// ..
	}
}