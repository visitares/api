<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0026 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('category')
			->addColumn('maxScore', 'decimal', [
				'null' => true,
				'default' => null,
				'precision' => 9,
				'scale' => 2,
				'after' => 'lineBreak'
			])
			->save();

		$this->table('form')
			->addColumn('maxScore', 'decimal', [
				'null' => true,
				'default' => null,
				'precision' => 9,
				'scale' => 2,
				'after' => 'publicStats'
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