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

		/**
		 * comment table
		 */
		$this->createBaseTable('comment')
			->addColumn('post_id', 'integer', [ 'null' => false ])
			->addColumn('user_id', 'integer', [ 'null' => false ])
			->addColumn('content', 'text', [ 'null' => false ])
			->addForeignKey('post_id', 'post', 'id', [ 'delete' => 'CASCADE' ])
			->addForeignKey('user_id', 'usercache', 'id', [ 'delete' => 'CASCADE' ])
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
		$this->dropTable('comment');
	}
}