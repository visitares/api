<?php

namespace Visitares\Cronjobs;

use ErrorException;
use PHPMailer\PHPMailer\PHPMailer;
use Visitares\Entity\Instance;
use Visitares\ErrorHandler;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Storage\Facade\SystemStorageFacade;

require(__DIR__ . '/config/config.php');
require(APP_DIR_ROOT . '/vendor/autoload.php');

ErrorHandler::register();

$hasErrors = false;
global $hasErrors;

printf("\nRunning system check..\n\n");

function check(string $name, callable $fn){
  global $hasErrors;
  try{

    $provider = include(APP_DIR_ROOT . '/config/di.php');
    call_user_func($fn, $provider);
    printf(" ✓ %s\n", $name);
  
  } catch(\Throwable $e){
    printf(" ❌ %s\n\n", $name);
    printf(" ** EXCEPTION **\n%s in %s (%s)\n%s\n\n", $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    $hasErrors = true;
  }
}

check('database', function($provider){
  $pdo = $provider->make(\PDO::class);
});

check('job queue process manager', function($provider){
  $pdo = $provider->make(\Visitares\JobQueue\ProcessManager::class);
});

check('email server', function($provider){
  $mailer = $provider->make(PHPMailer::class);
  $mail = clone $mailer;
  $mail->addAddress('rderheim@derheim-software.de');
  $mail->Subject = 'visitares test mail';
  $mail->From = 'noreply@visitares.com';
  $mail->Body = 'If you can read this, the test was successful :-)';
  if(!$mail->send()){
    throw new ErrorException(sprintf('Mail could not be sent: %s', $mail->ErrorInfo));
  }
});

check('SystemStorageFacade', function($provider){
  $storage = $provider->make(SystemStorageFacade::class);
  $em = $storage->getEntityManager();
});

printf("\n");

if($hasErrors){
  exit(0);
}