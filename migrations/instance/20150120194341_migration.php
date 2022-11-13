<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration extends AbstractMigration{
	/**
	 * @return void
	 */
	public function up(){
		$this->createLanguageTable();
		$this->createTranslationTable();
		$this->createTranslatedTable();
		$this->createClientTable();
		$this->createCategoryTable();
		$this->createGroupTable();
		$this->createCategoryGroupJoinTable();
		$this->createUserTable();
		$this->createGroupUserJoinTable();
		$this->createFormTable();
		$this->createInputTable();
		$this->createOptionTable();
		$this->createSubmitTable();
		$this->createValueTable();
		$this->createMessageTable();
		$this->createUnreadTable();
	}

	/**
	 * @return void
	 */
	public function down(){
		$tables = [
			'unread',
			'message',
			'value',
			'submit',
			'inputoption',
			'input',
			'form',
			'group_user',
			'user',
			'category_group',
			'usergroup',
			'category',
			'client',
			'translated',
			'translation',
			'language'
		];
		foreach($tables as $table){
			if($this->hasTable($table)){
				$this->dropTable($table);
			}
		}
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
			->addColumn('isDefault', 'boolean', [
				'default' => false
			])
			->addColumn('code', 'string', [
				'limit' => 2
			])
			->addColumn('label', 'string', [
				'limit' => 250
			])
			->save();
	}

	/**
	 * Create the `language` table.
	 *
	 * @return void
	 */
	protected function createTranslationTable(){
		$this->createBaseTable('translation')
			->save();
	}

	/**
	 * Create the `translated` table.
	 *
	 * @return void
	 */
	protected function createTranslatedTable(){
		$this->createBaseTable('translated')
			// Data
			->addColumn('language_id', 'integer', ['signed' => true])
			->addColumn('translation_id', 'integer', ['signed' => true])
			->addColumn('content', 'text')

			// Foreign Keys
			->addForeignKey('language_id', 'language', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('translation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Creat the `client` table.
	 *
	 * @return void
	 */
	protected function createClientTable(){
		$this->createBaseTable('client')
			// Data
			->addColumn('name', 'string', [
				'limit' => 100,
				'null' => true,
			])
			->addColumn('description', 'text', [
				'null' => true
			])
			->save();
	}

	/**
	 * Creat the `category` table.
	 *
	 * @return void
	 */
	protected function createCategoryTable(){
		$this->createBaseTable('category')
			// Data
			->addColumn('client_id', 'integer', ['signed' => true])
			->addColumn('isActive', 'boolean')
			->addColumn('isCopy', 'boolean')
			->addColumn('icon', 'string', [
				'limit' => 250
			])
			->addColumn('nameTranslation_id', 'integer', ['signed' => true])
			->addColumn('descriptionTranslation_id', 'integer', ['signed' => true])

			// Foreign Keys
			->addForeignKey('client_id', 'client', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('nameTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('descriptionTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `group` table.
	 *
	 * @return void
	 */
	protected function createGroupTable(){
		$this->createBaseTable('usergroup')
			// Data
			->addColumn('nameTranslation_id', 'integer', ['signed' => true])
			->addColumn('descriptionTranslation_id', 'integer', ['signed' => true])

			// Foreign Keys
			->addForeignKey('nameTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('descriptionTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `category_group` join table.
	 *
	 * @return void
	 */
	protected function createCategoryGroupJoinTable(){
		$this->createBaseTable('category_group', [
			'id' => false,
			'primary_key' => ['category_id', 'group_id']
		])
			->addColumn('category_id', 'integer', ['null' => false, 'signed' => true])
			->addColumn('group_id', 'integer', ['null' => false, 'signed' => true])
			->addForeignKey('category_id', 'category', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('group_id', 'usergroup', 'id', [
				'delete' => 'CASCADE'
			])
			->addIndex([
				'category_id',
				'group_id'
			], [
				'unique' => true
			])
			->save();
	}

	/**
	 * Create the `user` table.
	 *
	 * @return void
	 */
	protected function createUserTable(){
		$this->createBaseTable('user')
			// Meta
			->addColumn('lastLogin', 'datetime', [
				'null' => true,
				'default' => null
			])

			// Data
			->addColumn('language_id', 'integer', ['signed' => true])
			->addColumn('isActive', 'boolean')
			->addColumn('role', 'integer')
			->addColumn('username', 'string', [
				'limit' => 100
			])
			->addColumn('password', 'string', [
				'limit' => 100
			])
			->addColumn('firstname', 'string', [
				'limit' => 100
			])
			->addColumn('lastname', 'string', [
				'limit' => 100
			])
			->addColumn('email', 'string', [
				'limit' => 100
			])
			->addColumn('phone', 'string', [
				'limit' => 100
			])

			// Foreign Keys
			->addForeignKey('language_id', 'language', 'id', [
				'delete' => 'CASCADE'
			])
			->save();
	}

	/**
	 * Create the `group_user` join table.
	 *
	 * @return void
	 */
	protected function createGroupUserJoinTable(){
		$this->createBaseTable('group_user', [
			'id' => false,
			'primary_key' => ['group_id', 'user_id']
		])
			->addColumn('group_id', 'integer', ['null' => false, 'signed' => true])
			->addColumn('user_id', 'integer', ['null' => false, 'signed' => true])
			->addForeignKey('group_id', 'usergroup', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('user_id', 'user', 'id', [
				'delete' => 'CASCADE'
			])
			->addIndex([
				'group_id',
				'user_id'
			], [
				'unique' => true
			])
			->save();
	}

	/**
	 * Create the `form` table.
	 *
	 * @return void
	 */
	protected function createFormTable(){
		$this->createBaseTable('form')
			// Data
			->addColumn('category_id', 'integer', ['signed' => true])
			->addColumn('type', 'integer')
			->addColumn('nameTranslation_id', 'integer', ['signed' => true])
			->addColumn('descriptionTranslation_id', 'integer', ['signed' => true])
			->addColumn('labelColumnTranslation_id', 'integer', ['signed' => true])
			->addColumn('optionColumnTranslation_id', 'integer', ['signed' => true])

			// Foreign Keys
			->addForeignKey('category_id', 'category', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('nameTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('descriptionTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('labelColumnTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('optionColumnTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `input` table.
	 *
	 * @return void
	 */
	protected function createInputTable(){
		$this->createBaseTable('input')
			// Data
			->addColumn('form_id', 'integer', ['signed' => true])
			->addColumn('labelTranslation_id', 'integer', ['signed' => true])

			// Foreign Keys
			->addForeignKey('form_id', 'form', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('labelTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `option` table.
	 *
	 * @return void
	 */
	protected function createOptionTable(){
		$this->createBaseTable('inputoption')
			// Data
			->addColumn('input_id', 'integer', ['signed' => true])
			->addColumn('labelTranslation_id', 'integer', ['signed' => true])

			// Foreign Keys
			->addForeignKey('input_id', 'input', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('labelTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `submit` table.
	 *
	 * @return void
	 */
	protected function createSubmitTable(){
		$this->createBaseTable('submit')
			// Data
			->addColumn('form_id', 'integer', ['signed' => true])
			->addColumn('user_id', 'integer', [
				'signed' => true,
				'null' => true
			])
			->addColumn('language_id', 'integer', ['signed' => true])

			// Foreign Keys
			->addForeignKey('form_id', 'form', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('user_id', 'user', 'id', [
				'delete' => 'SET_NULL'
			])
			->addForeignKey('language_id', 'language', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();
	}

	/**
	 * Create the `value` table.
	 *
	 * @return void
	 */
	protected function createValueTable(){
		$this->createBaseTable('value')
			// Data
			->addColumn('submit_id', 'integer', ['signed' => true])
			->addColumn('input_id', 'integer', ['signed' => true])
			->addColumn('option_id', 'integer', ['signed' => true])
			->addColumn('checked', 'boolean', [
				'null' => true
			])
			->addColumn('message', 'text', [
				'null' => true
			])

			// Foreign Keys
			->addForeignKey('submit_id', 'submit', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('input_id', 'input', 'id', [
				'delete' => 'CASCADE'
			])
			->addForeignKey('option_id', 'inputoption', 'id', [
				'delete' => 'CASCADE'
			])
			->save();
	}

	/**
	 * Create the `message` table.
	 *
	 * @return void
	 */
	protected function createMessageTable(){
		$this->createBaseTable('message')
			// Data
			->addColumn('user_id', 'integer', [
				'signed' => true,
				'null' => true
			])
			->addColumn('message', 'text')

			// Foreign Keys
			->addForeignKey('user_id', 'user', 'id', [
				'delete' => 'CASCADE'
			])
			->save();
	}

	/**
	 * Create the `unread` table.
	 *
	 * @return void
	 */
	protected function createUnreadTable(){
		$this->createBaseTable('unread')
			// Data
			->addColumn('user_id', 'integer', ['signed' => true])
			->addColumn('message_id', 'integer', ['signed' => true])

			// Foreign Keys
			->addForeignKey('user_id', 'user', 'id')
			->addForeignKey('message_id', 'message', 'id', [
				'delete' => 'CASCADE'
			])
			->save();
	}
}