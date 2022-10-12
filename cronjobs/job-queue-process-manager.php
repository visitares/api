#!/usr/bin/php7.4
<?php

namespace Visitares\Cronjobs;

use Visitares\ErrorHandler;

require(__DIR__ . '/../config/config.php');
require(APP_DIR_ROOT . '/vendor/autoload.php');
try{
  $provider = include(APP_DIR_ROOT . '/config/di.php');

  file_put_contents(__DIR__ . '/job-queue-process-manager.php.heartbeat', (new \DateTime)->format('Y-m-d H:i:s'));

  ErrorHandler::register();

  $processManager = $provider->make(\Visitares\JobQueue\ProcessManager::class);
  $processManager->run();
} catch(\Throwable $e){
  file_put_contents(__DIR__ . '/error.log', sprintf("[%s] %s in %s (%d)\n%s\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()) . PHP_EOL, FILE_APPEND);
}