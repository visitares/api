<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0039 extends AbstractMigration{
	/**
	 * @return {void}
	 */
	public function up(){
		// Benötigte Felder:
		// Gruppe:		"Startbildschirm" (integer)
		// 						Standardkonfigurationsgruppe?
		// Benutzer:	"Konfigurationsgruppe" (Zuordnung)
		// 						"Firma"
		// 						"Abteilung"
		//
		
		$this->table('usergroup')
			->addColumn('defaultAppScreen', 'integer', [
				'default' => 0,
				'null' => false,
				'after' => 'isDefault'
			])
			->addColumn('isDefaultConfig', 'boolean', [
				'default' => false,
				'null' => false,
				'after' => 'isDefault'
			])
			->save();

		$this->table('user')
			->addColumn('configGroup_id', 'integer', [
				'signed' => true,
				'null' => true,
				'after' => 'language_id'
			])
			->addColumn('department', 'string', [
				'null' => true,
				'length' => 200,
				'after' => 'lastname'
			])
			->addColumn('company', 'string', [
				'null' => true,
				'length' => 200,
				'after' => 'lastname'
			])
			->addForeignKey('configGroup_id', 'usergroup', 'id', [
				'delete' => 'SET_NULL'
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