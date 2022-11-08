<?php

defined('APP_DEV') || define('APP_DEV', false);
defined('APP_DEBUG') || define('APP_DEBUG', false);
defined('APP_DIR_ROOT') || define('APP_DIR_ROOT', realpath(__DIR__ . '/../'));

defined('APP_DIR_LOG') || define('APP_DIR_LOG', realpath(__DIR__ . '/../var/log'));

defined('CMS_URL') || define('CMS_URL' , 'https://cms.visitares.com');
defined('APP_URL') || define('APP_URL' , 'https://app.visitares.com');

defined('PHP_BIN') || define('PHP_BIN' , '/usr/bin/php8.1');
defined('FFMPEG_BIN') || define('FFMPEG_BIN', '/usr/bin/ffmpeg');
