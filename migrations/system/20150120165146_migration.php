<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration extends AbstractMigration{
	/**
	 * @return void
	 */
	public function change(){
		$this->createLanguageTable();
		$this->createStringTable();
		$this->createEmoticonTable();
		$this->createDirtyWordTable();
		$this->createEventTable();
		$this->createSessionTable();
		$this->createRequestTable();
		$this->createInstanceTable();
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
	protected function createLanguageTable(){
		$this->createBaseTable('language')
			// Data
			->addColumn('code', 'string', [
				'limit' => 2
			])
			->addColumn('label', 'string', [
				'limit' => 250
			])
			->save();
	}

	/**
	 * Create the `string` table.
	 *
	 * @return void
	 */
	protected function createStringTable(){
		$this->createBaseTable('string')
			// Data
			->addColumn('language_id', 'integer')
			->addColumn('code', 'string', [
				'limit' => '250'
			])
			->addColumn('value', 'text')

			// Foreign Keys
			->addForeignKey('language_id', 'language', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `emoticon` table.
	 *
	 * @return void
	 */
	protected function createEmoticonTable(){
		$this->createBaseTable('emoticon')
			// Data
			->addColumn('emoticon', 'string', [
				'limit' => '50'
			])
			->addColumn('image', 'string', [
				'limit' => '250'
			])
			->save();
	}

	/**
	 * Create the `dirtyword` table.
	 *
	 * @return void
	 */
	protected function createDirtyWordTable(){
		$this->createBaseTable('dirtyword')
			// Data
			->addColumn('language_id', 'integer')
			->addColumn('word', 'string', [
				'limit' => '250'
			])

			// Foreign Keys
			->addForeignKey('language_id', 'language', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `event` table.
	 *
	 * @return void
	 */
	protected function createEventTable(){
		$table = $this->table('event');
		$table
			->addColumn('creationDate', 'datetime', [
				'default' => 'CURRENT_TIMESTAMP'
			])
			->addColumn('type', 'integer')
			->addColumn('message', 'text')
			->addColumn('dump', 'text', [
				'null' => true
			])
			->save();
	}

	/**
	 * Create the `session` table.
	 *
	 * @return void
	 */
	protected function createSessionTable(){
		$table = $this->table('session');
		$table
			->addColumn('creationDate', 'datetime', [
				'default' => 'CURRENT_TIMESTAMP'
			])
			->addColumn('lastAccess', 'datetime', [
				'null' => 'CURRENT_TIMESTAMP'
			])
			->addColumn('token', 'string', [
				'limit' => 128
			])
			->addColumn('data', 'text')
			->save();
	}

	/**
	 * Create the `request` table.
	 *
	 * @return void
	 */
	protected function createRequestTable(){
		$table = $this->table('request');
		$table
			->addColumn('creationDate', 'datetime', [
				'default' => 'CURRENT_TIMESTAMP'
			])
			->addColumn('resource', 'string', [
				'limit' => 1024
			])
			->addColumn('userAgent', 'string', [
				'limit' => 1024
			])
			->addColumn('headers', 'text', [
				'null' => true
			])
			->save();
	}

	/**
	 * Create the `instance` table.
	 *
	 * @return void
	 */
	protected function createInstanceTable(){
		$table = $this->createBaseTable('instance')
			->addColumn('isActive', 'boolean', [
				'null' => true
			])
			->addColumn('isTemplate', 'boolean', [
				'null' => true
			])
			->addColumn('token', 'string', [
				'limit' => 4
			])
			->addColumn('registrationToken', 'string', [
				'limit' => 32,
				'null' => true
			])
			->addColumn('domain', 'string', [
				'limit' => 50,
				'null' => true
			])
			->addColumn('name', 'string', [
				'limit' => 250,
				'null' => true
			])
			->addColumn('description', 'text', [
				'null' => true
			])
			->addColumn('country', 'string', [
				'limit' => 2,
				'null' => true
			])
			->addColumn('postalCode', 'string', [
				'limit' => 10,
				'null' => true
			])
			->addColumn('city', 'string', [
				'limit' => 100,
				'null' => true
			])
			->addColumn('street', 'string', [
				'limit' => 100,
				'null' => true
			])
			->addColumn('sector', 'string', [
				'limit' => 100,
				'null' => true
			])

			->addColumn('logo', 'string', [
				'limit' => 64,
				'null' => true
			])
			->addColumn('background', 'string', [
				'limit' => 64,
				'null' => true
			])
			->save();
	}
}