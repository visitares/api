<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0025 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		$this->table('form')
			->addColumn('shortDescriptionTranslation_id', 'integer', [
				'null' => true,
				'default' => null
			])
			->addForeignKey('shortDescriptionTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
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