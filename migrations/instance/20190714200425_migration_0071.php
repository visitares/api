<?php

use Phinx\Migration\AbstractMigration;

class Migration0071 extends AbstractMigration{
  public function up(){
    $this->execute('
      UPDATE
        `input` i
      LEFT JOIN
        `form` f ON f.id = i.form_id
      SET
        i.coefficient = 0
      WHERE
        f.type = 5 AND i.coefficient = 1;
    ');
  }
}