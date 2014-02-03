<?php
// STOPPER слить с init.php после ребеза доработки по init.php в мастер
// STOPPER убрать лишние array_shift -- переделать на fetchResult
namespace Fias;

use Fias\DataSource\XmlReader;
use Fias\Loader\InitLoader;
use Fias\Loader\UpdateLoader;
use Grace\DBAL\ConnectionFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$config       = Config::get('config');
$importConfig = Config::get('import');
$db           = ConnectionFactory::getConnection($config->getParam('database'));

$dataBaseName = $config->getParam('database')['database'];

// STOPPER вообще вынести в отдельный файл, поскольку используется кроме как здесь еще в init.php и index.php
$log    = new Logger('cli');
$log->pushHandler(new StreamHandler(__DIR__ . '/logs/cli.log'));
set_error_handler(
    function ($errNo, $errStr, $errFile, $errLine)
    {
        $message = $errNo . "::"
            . $errStr . "\n"
            . $errFile . "::"
            . $errLine . "\n"
        ;
        throw new \Exception($message);
    }
);

try {
    if ($argc == 2) {
        $path = $argv['1'];
        if (!is_dir($path)) {
            $path = Dearchiver::extract($config->getParam('file_directory'), $path);
        }

        $directory = new Directory($path);
    } else {
        $loader    = new UpdateLoader($config->getParam('wsdl_url'), $config->getParam('file_directory'));
        $directory = $loader->load();
    }

    $addressObjectsConfig = $importConfig->getParam('address_objects');
    $addressObjects       = new AddressObjectsUpdateImporter($db, $addressObjectsConfig['table_name'], $addressObjectsConfig['fields']);
    $reader               = new XmlReader(
        $directory->getAddressObjectFile(),
        $addressObjectsConfig['node_name'],
        array_keys($addressObjectsConfig['fields']),
        $addressObjectsConfig['filters']
    );

    $addressObjects->import($reader);

    $housesConfig = $importConfig->getParam('houses');
    $houses       = new HousesUpdateImporter($db, $housesConfig['table_name'], $housesConfig['fields']);

   $reader    = new XmlReader(
        $directory->getHousesFile(),
        $housesConfig['node_name'],
        array_keys($housesConfig['fields']),
        array()
    );

    $houses->import($reader);

    $addressObjects->modifyDataAfterImport();
    $houses->modifyDataAfterImport();

    $db->execute('ROLLBACK');
} catch (\Exception $e) {
    $log->addError($e->getMessage());
    $db->execute('ROLLBACK');
    echo "В процессе инициализации произошла ошибка.\n";
    exit(1);
}
