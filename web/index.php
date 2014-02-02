<?php

namespace Fias;

use Grace\DBAL\ConnectionFactory;
use Fias\ApiAction\HttpException;
use Fias\ApiAction\Handler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));
$log    = new Logger('http');

$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/http.log'));
set_error_handler(
    function ($errNo, $errStr, $errFile, $errLine)
    {
        $message = $errNo . "::"
            . $errStr . "\n"
            . $errFile . "::"
            . $errLine . "\n";
        throw new \Exception($message);
    }
);

try {
    $result = Handler::handle($_SERVER['REQUEST_URI'], $_GET, $db);

    header('Content-type: application/json');
    echo json_encode($result);
} catch (HttpException $e) {
    switch ($e->getCode()) {
        case 400:
            $message = $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request';
            break;
        case 404:
            $message = $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found';
            break;

        default:
            $message = $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error';
    }

    header($message, true, $e->getCode());
} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    $log->addError($e->getMessage());
}
