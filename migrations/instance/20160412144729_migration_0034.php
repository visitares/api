<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0034 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('category')
			->changeColumn('icon', 'string', [
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