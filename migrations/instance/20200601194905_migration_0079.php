<?php

use Phinx\Migration\AbstractMigration;

class Migration0079 extends AbstractMigration{
  public function up(){

    $config = require(__DIR__ . '/../../config/app.php');

    $pdo = $this->adapter->getConnection();
    $updatePasswordStmt = $pdo->prepare('UPDATE user SET password = :password WHERE id = :id');

    $users = $this->query('SELECT id, password_old FROM user WHERE password_old IS NOT NULL AND TRIM(password_old) != ""');
    while($user = $users->fetch(\PDO::FETCH_OBJ)){
      $hash = password_hash($user->password_old, $config->password->algo, $config->password->options);
      printf("Created hash for user %d: '%s' -> '%s' (%d)\n", $user->id, $user->password_old, $hash, mb_strlen($hash));
      $updatePasswordStmt->execute([
        ':id' => $user->id,
        ':password' => $hash,
      ]);
    }

  }
}