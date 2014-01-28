<?php

namespace Fias;

use Grace\DBAL\ConnectionFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));
$log    = new Logger('general');
$log->pushHandler(new StreamHandler(__DIR__ . 'logs/http.log'));


try {
    RequestHandler::handle($_SERVER['REQUEST_URI'], $db);
} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    $log->addError($e->getMessage());
}
