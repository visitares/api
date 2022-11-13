<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0001 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		// Update `form` table
		$this->table('form')
			->dropForeignKey('labelColumnTranslation_id')
			->removeColumn('labelColumnTranslation_id')
			->dropForeignKey('optionColumnTranslation_id')
			->removeColumn('optionColumnTranslation_id')
			->addColumn('sort', 'integer', [
				'null' => true,
				'after' => 'type'
			])
            ->save();

		// Update `submit` table
		$this->table('submit')
			->addColumn('message', 'text', [
				'null' => true
			])
            ->save();

		// Update `value` table
		$this->table('value')
			->changeColumn('option_id', 'integer', [
				'signed' => true,
				'null' => true
			])
			->removeColumn('message')
            ->save();
	}

	/**
	 * @return {void}
	 */
	public function down(){
		// ..
	}
}