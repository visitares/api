<?php

use Phinx\Migration\AbstractMigration;

class Migration0070 extends AbstractMigration{
  public function up(){
    $this->execute('
      UPDATE
        usersubmitinstance si
      LEFT JOIN
        category c ON c.id = si.category_id
      SET
        si.isDone = 1
      WHERE
            si.score = c.maxScore
        AND si.isDone = 0;
    ');
  }
}