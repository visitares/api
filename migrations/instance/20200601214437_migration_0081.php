<?php

use Phinx\Migration\AbstractMigration;

class Migration0081 extends AbstractMigration{
  public function up(){
    $pdo = $this->adapter->getConnection();
    $pdo->exec('UPDATE form_media fm LEFT JOIN form f ON f.id = fm.form_id LEFT JOIN media m ON m.id = fm.media_id SET fm.creationDate = IF(f.creationDate > m.creationDate, f.creationDate, m.creationDate);');
  }
}