<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0021 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('value')
			->addColumn('coefficient', 'decimal', [
				'null' => true,
				'default' => null,
				'precision' => 9,
				'scale' => 2
			])
			->save();

		$this->table('input')
			->addColumn('coefficient', 'decimal', [
				'null' => true,
				'default' => null,
				'precision' => 9,
				'scale' => 2
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