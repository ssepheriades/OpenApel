<?php

declare(strict_types=1);

if (!file_exists(dirname(__DIR__).'/vendor/autoload.php')) {
    throw new LogicException('Autoload not found. Did you forget to run "composer install"?');
}

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/.env.test.local')) {
    require dirname(__DIR__).'/.env.test.local';
} elseif (file_exists(dirname(__DIR__).'/.env.test')) {
    require dirname(__DIR__).'/.env.test';
}
