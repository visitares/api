<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0012 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){

		/**
		 * mediagroup table
		 */
		$this->table('mediagroup')
			->addColumn('master_id', 'integer', [ 'signed' => true, 'null' => false ])
			->addForeignKey('master_id', 'master', 'id', [ 'delete' => 'CASCADE' ])
			->save();

		/**
		 * update post foreign keys
		 */
		$this->table('post')
			->addForeignKey('user_id', 'usercache', 'id', [ 'delete' => 'CASCADE' ])
			->save();

		/**
		 * update post foreign keys
		 */
		$this->table('post_group')
			->addForeignKey('group_id', 'groupcache', 'id', [ 'delete' => 'CASCADE' ])
			->save();
	}

	/**
	 * @param  boolean $creationDate
	 * @param  boolean $modificationDate
	 * @return Table
	 */
	protected function createBaseTable($name, $options = [], $creationDate = true, $modificationDate = true){
		$table = $this->table($name, $options);
		if($creationDate){
			$table->addColumn('creationDate', 'datetime', [ 'null' => true ]);
		}
		if($modificationDate){
			$table->addColumn('modificationDate', 'datetime', [ 'null' => true ]);
		}
		return $table;
	}

	/**
	 * @return {void}
	 */
	public function down(){
		$this->dropTable('usercache');
	}
}