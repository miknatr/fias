<?php
// STOPPER слить с init.php после ребеза доработки по init.php в мастер
// STOPPER проверки статусов
// STOPPER тотальное дублирование кода между init.php; index.php; update.php исправить.
// STOPPER проверить производительность DEFFERABLE на бОльшем объеме данных
// STOPPER контроль заливки апдейтов в должном порядке.
namespace Fias;

use Fias\DataSource\XmlReader;
use Fias\Loader\UpdateLoader;
use Grace\DBAL\ConnectionFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$configDir    = __DIR__ . '/config/';
$config       = Config::get($configDir.'config.php');
$importConfig = Config::get($configDir.'import.php');
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

    $db->execute('SET CONSTRAINTS "address_objects_parent_id_fkey", "houses_parent_id_fkey" DEFERRED');

    $housesConfig         = $importConfig->getParam('houses');
    $addressObjectsConfig = $importConfig->getParam('address_objects');

    $houseRemover = new Remover($db, $housesConfig['table_name'], $housesConfig('primary_key'));
    $houseRemover->remove(
        new XmlReader(
            $directory->getDeletedHousesFile(),
            $housesConfig['node_name'],
            array($housesConfig['primary_key']),
            array()
        )
    );

    $addressObjectsRemover = new Remover($db, $addressObjectsConfig['table_name'], $addressObjectsConfig('primary_key'));
    $addressObjectsRemover->remove(
        new XmlReader(
            $directory->getDeletedHousesFile(),
            $addressObjectsConfig['node_name'],
            array($addressObjectsConfig['primary_key']),
            array()
        )
    );

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

    $houses = new Importer($db, $housesConfig['table_name'], $housesConfig['fields']);
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
