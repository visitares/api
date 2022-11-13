<?php

use Phinx\Migration\AbstractMigration;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration0058 extends AbstractMigration{


	private function createTranslation($string){
		$pdo = $this->getAdapter()->getConnection();
		$insertTranslationQuery = $pdo->prepare('INSERT INTO translation VALUES()');
		$insertTranslatedQuery = $pdo->prepare('INSERT INTO translated (creationDate, language_id, translation_id, content) VALUES (NOW(), 1, :translationId, :content)');

		$insertTranslationQuery->execute();
		$translationId = $pdo->lastInsertId();

		$insertTranslatedQuery->execute([
			':translationId' => $translationId,
			':content' => $string
		]);
		$translatedId = $pdo->lastInsertId();

		return $translationId;
	}

	/**
	 * @return void
	 */
	public function up(){

		$this->table('client')
			->addColumn('nameTranslation_id', 'integer', ['null' => true, 'signed' => false])
			->addColumn('descriptionTranslation_id', 'integer', ['null' => true, 'signed' => false])
			->addForeignKey('nameTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->addForeignKey('descriptionTranslation_id', 'translation', 'id', [
				'delete' => 'RESTRICT'
			])
			->save();

		$pdo = $this->getAdapter()->getConnection();
		$clients = $this->fetchAll('SELECT id, name, description FROM client');

		foreach($clients as $client){
			$pdo
				->prepare('UPDATE client SET nameTranslation_id = :nameTranslation_id, descriptionTranslation_id = :descriptionTranslation_id WHERE id = :id')
				->execute([
					':id' => $client['id'],
					':nameTranslation_id' =>  $this->createTranslation($client['name']),
					':descriptionTranslation_id' => $this->createTranslation($client['description']),
				]);
		}

	}
}
