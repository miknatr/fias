<?php

use Bravicility\Failure\FailureHandler;
use FileSystem\Dearchiver;
use FileSystem\Directory;
use Loader\InitLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$container    = new Container();
$db           = $container->getDb();
$dataBaseName = $container->getDatabaseName();
$logger       = $container->getErrorLogger();

FailureHandler::setup(function ($error) use ($logger) {
    $logger->error($error['message'], $error);
    fwrite(STDERR, "В процессе инициализации произошла ошибка.\n");
    exit(1);
});

set_time_limit(0);

if ($_SERVER['argc'] == 2) {
    $path = $_SERVER['argv']['1'];
    if (!is_dir($path)) {
        $path = Dearchiver::extract($container->getFileDirectory(), $path);
    }

    $directory = new Directory($path);
} else {
    $loader    = new InitLoader($container->getWsdlUrl(), $container->getFileDirectory());
    $directory = $loader->load();
}

DbHelper::runFile($dataBaseName, __DIR__ . '/database/01_tables.sql');

$addressObjectsConfig = $container->getAddressObjectsImportConfig();
$addressObjects       = new AddressObjectsImporter($db, $addressObjectsConfig['table_name'], $addressObjectsConfig['fields']);
$reader               = new XmlReader(
    $directory->getAddressObjectFile(),
    $addressObjectsConfig['node_name'],
    array_keys($addressObjectsConfig['fields']),
    $addressObjectsConfig['filters']
);

$addressObjects->import($reader);

$housesConfig = $container->getHousesImportConfig();
$houses       = new HousesImporter($db, $housesConfig['table_name'], $housesConfig['fields']);

// Если не отсекать записи исходя из региона придется грузить 21 млн записей вместо полутора.
$addresses = $db->execute('SELECT address_id, address_id second_id FROM address_objects')->fetchHash();

$filters   = array(
    array(
        'field' => 'AOGUID',
        'type'  => 'hash',
        'value' => $addresses,
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

UpdateLogHelper::addVersionIdToLog($db, $directory->getVersionId());

