<?php

namespace Visitares;

class ErrorHandler {

  public static $isRegistred = false;

  public static function register(){

    if(self::$isRegistred){
      return false;
    }

    // Convert all notices, warnings, and errors to exceptions
    set_error_handler(function($code, $message, $file, $line){

      // ignore deprecation warnings
    	if(strpos(strtolower($message), 'deprecated') !== false){
    		return;
      }

    	throw new \ErrorException($message, $code, 1, $file, $line);
    });

    // Catch any unhandled exception, log it and suppress the output
    set_exception_handler(function($e){
    	file_put_contents(APP_DIR_ROOT . '/error.log', implode(' ', [
    		PHP_EOL . PHP_EOL . '----------' .
    		PHP_EOL . sprintf('[%s]', date('Y-m-d H:i:s')),
    		sprintf('%s (%s):', $e->getFile(), $e->getLine()),
    		PHP_EOL . $e->getMessage(),
    		PHP_EOL . $e->getTraceAsString()
    	]), FILE_APPEND);
    });

    self::$isRegistred = true;
    return true;

  }

}