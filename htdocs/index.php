<?php

namespace Fias;

use Grace\DBAL\ConnectionFactory;
use Fias\Action\Exception;
use Fias\Action\Handler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));
$log    = new Logger('http');
$log->pushHandler(new StreamHandler(__DIR__ . 'logs/http.log'));


try {
    Handler::handle($_SERVER['REQUEST_URI'], $db);
} catch (Exception $e) {
    switch ($e->getCode()) {
        case 404:
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
            break;

        case 320:
            header('Location: ' . $e->getMessage());
            break;
        default:
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
    }
} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    $log->addError($e->getMessage());
}
