<?php

namespace Visitares\Cronjobs;

use Visitares\ErrorHandler;

require(__DIR__ . '/../config/config.php');
require(APP_DIR_ROOT . '/vendor/autoload.php');

$provider = include(APP_DIR_ROOT . '/config/di.php');

try{
  list(, $jobId) = $argv;

  file_put_contents(__DIR__ . '/job-queue-run-worker.php.heartbeat', (new \DateTime)->format('Y-m-d H:i:s'));

  ErrorHandler::register();

  $workerFactory = $provider->make(\Visitares\JobQueue\WorkerFactory::class);
  $workerFactory->spawn($jobId);
} catch(\Throwable $e){
  file_put_contents(__DIR__ . '/error.log', sprintf("[%s] %s in %s (%d)\n%s\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()) . PHP_EOL, FILE_APPEND);
}