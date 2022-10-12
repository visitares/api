<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0014 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('user')
			->changeColumn('firstname', 'string', [
				'null' => true
			])
			->changeColumn('lastname', 'string', [
				'null' => true
			])
			->changeColumn('email', 'string', [
				'null' => true
			])
			->changeColumn('phone', 'string', [
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