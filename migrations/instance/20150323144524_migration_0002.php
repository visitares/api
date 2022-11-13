<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0002 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->createDirtyWordTable();
	}

	/**
	 * Creates a table object with default columns.
	 *
	 * @param  boolean $creationDate
	 * @param  boolean $modificationDate
	 * @return Table
	 */
	protected function createBaseTable($name, $options = [], $creationDate = true, $modificationDate = true){
		$table = $this->table($name, $options);
		if($creationDate){
			$table->addColumn('creationDate', 'datetime', [
				'null' => true
			]);
		}
		if($modificationDate){
			$table->addColumn('modificationDate', 'datetime', [
				'null' => true
			]);
		}
		return $table;
	}

	/**
	 * Create the `language` table.
	 *
	 * @return void
	 */
	protected function createDirtyWordTable(){
		$this->createBaseTable('dirtyword')
			->addColumn('wordTranslation_id', 'integer', ['signed' => true])
			->addForeignKey('wordTranslation_id', 'translation', 'id', [
				'delete' => 'CASCADE'
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