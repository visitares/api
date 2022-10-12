<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0017 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){

		$this->table('metagroup')
			->addColumn('creationDate', 'datetime', ['null' => true])
			->addColumn('modificationDate', 'datetime', ['null' => true])
			->addColumn('name', 'text', ['null' => true])
			->addColumn('description', 'text', ['null' => true])
			->save();

		$this->table('master_metagroup')
			->addColumn('master_id', 'integer', ['null' => false])
			->addColumn('metagroup_id', 'integer', ['null' => false])
			->addForeignKey('master_id', 'master', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('metagroup_id', 'metagroup', 'id', [
				'delete' => 'CASCADE'
			])
			->save();

	}

	/**
	 * @return {void}
	 */
	public function down(){
		$this->dropTable('metagroup');
	}
}