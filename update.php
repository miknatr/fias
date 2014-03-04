<?php
// STOPPER слить с init.php после ребеза доработки по init.php в мастер
// STOPPER проверки статусов
// STOPPER тотальное дублирование кода между init.php; index.php; update.php исправить.
// STOPPER целостность, попробовать DEFFERABLE для малого количества данных.
namespace Fias;

use Fias\DataSource\XmlReader;
use Fias\Loader\UpdateLoader;
use Grace\DBAL\ConnectionFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$config       = Config::get('config');
$importConfig = Config::get('import');
$db           = ConnectionFactory::getConnection($config->getParam('database'));

$log = new Logger('cli');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/cli.log'));
set_error_handler(
    function ($errNo, $errStr, $errFile, $errLine) {
        $message = $errNo . "::"
            . $errStr . "\n"
            . $errFile . "::"
            . $errLine . "\n"
        ;
        throw new \Exception($message);
    }
);

try {
    $db->start();

    if ($_SERVER['argc'] == 2) {
        $path = $_SERVER['argv']['1'];
        if (!is_dir($path)) {
            $path = Dearchiver::extract($config->getParam('file_directory'), $path);
        }

        $directory = new Directory($path);
    } else {
        $loader    = new UpdateLoader($config->getParam('wsdl_url'), $config->getParam('file_directory'));
        $directory = $loader->load();
    }

    $addressObjectsConfig = $importConfig->getParam('address_objects');
    $fields               = $addressObjectsConfig['fields'];
    $fields['OPERSTATUS'] = array('name' => 'update_status', 'type' => 'integer');
    $addressObjects       = new Importer($db, $addressObjectsConfig['table_name'], $fields);
    $reader               = new XmlReader(
        $directory->getAddressObjectFile(),
        $addressObjectsConfig['node_name'],
        array_keys($fields),
        $addressObjectsConfig['filters']
    );

    $addressObjects->import($reader);

    $housesConfig = $importConfig->getParam('houses');
    $houses       = new Importer($db, $housesConfig['table_name'], $housesConfig['fields']);

    $reader = new XmlReader(
        $directory->getHousesFile(),
        $housesConfig['node_name'],
        array_keys($housesConfig['fields']),
        array()
    );

    $houses->import($reader);
    $db->commit();
} catch (\Exception $e) {
    $log->addError($e->getMessage());
    $db->rollback();
    fwrite(STDERR, "В процессе обновления произошла ошибка.\n");
    exit(1);
}
