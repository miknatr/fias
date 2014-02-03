<?php

namespace Fias;

use Fias\DataSource\XmlReader;
use Fias\Loader\InitLoader;
use Grace\DBAL\ConnectionFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$config       = Config::get('config');
$importConfig = Config::get('import');
$db           = ConnectionFactory::getConnection($config->getParam('database'));

$dataBaseName = $config->getParam('database')['database'];

$log = new Logger('cli');
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
        $loader    = new InitLoader($config->getParam('wsdl_url'), $config->getParam('file_directory'));
        $directory = $loader->load();
    }

    DbHelper::runFile($dataBaseName, __DIR__ . '/database/01_tables.sql');

    $addressObjectsConfig = $importConfig->getParam('address_objects');
    $addressObjects       = new AddressObjectsImporter($db, $addressObjectsConfig['table_name'], $addressObjectsConfig['fields']);
    $reader               = new XmlReader(
        $directory->getAddressObjectFile(),
        $addressObjectsConfig['node_name'],
        array_keys($addressObjectsConfig['fields']),
        $addressObjectsConfig['filters']
    );

    $addressObjects->import($reader);

    $housesConfig = $importConfig->getParam('houses');
    $houses       = new HousesImporter($db, $housesConfig['table_name'], $housesConfig['fields']);

    // Если не отсекать записи исходя из региона придется грузить 21 млн записей вместо полутора.
    $addresses = $db->execute('SELECT address_id, address_id second_id FROM address_objects')->fetchHash();

    $filters   = array(
        array(
            'field' => 'AOGUID',
            'type'  => 'hash',
            'value' => $addresses
        )
    );
    $reader    = new XmlReader(
        $directory->getHousesFile(),
        $housesConfig['node_name'],
        array_keys($housesConfig['fields']),
        $filters
    );

    $houses->import($reader);

    DbHelper::runFile($dataBaseName, __DIR__ . '/database/02_indexes.sql');

    $addressObjects->modifyDataAfterImport();
    $houses->modifyDataAfterImport();

    DbHelper::runFile($dataBaseName, __DIR__ . '/database/03_constraints.sql');
    DbHelper::runFile($dataBaseName, __DIR__ . '/database/04_clean_up.sql');
} catch (\Exception $e) {
    $log->addError($e->getMessage());
    echo "В процессе инициализации произошла ошибка.\n";
    exit(1);
}
