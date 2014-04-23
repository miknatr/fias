<?php

use Bravicility\Failure\FailureHandler;
use FileSystem\Dearchiver;
use FileSystem\Directory;
use DataSource\XmlReader;

require_once __DIR__ . '/../vendor/autoload.php';

$container    = new Container();
$db           = $container->getDb();
$dataBaseName = $container->getDatabaseName();
$logger       = $container->getErrorLogger();
$dbPath       = $container->getDatabaseSourcesDirectory();

FailureHandler::setup(function ($error) use ($logger) {
    $logger->error($error['message'], $error);
    fwrite(STDERR, "В процессе инициализации произошла ошибка:\n{$error['message']}\n");
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
    $loader    = $container->getInitLoader();
    $directory = $loader->load();
}
// Получаем VersionId поскольку если его не окажется, то сообщение об этом мы получим только в самом конце 15-ти минутного процесса, что не очень приятно.
$versionId = $directory->getVersionId();

DbHelper::runFile($dataBaseName, $dbPath . '/01_tables.sql');
DbHelper::runFile($dataBaseName, $dbPath . '/02_system_data.sql');

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

$reader    = new XmlReader(
    $directory->getHousesFile(),
    $housesConfig['node_name'],
    array_keys($housesConfig['fields'])
);

$houses->import($reader);

DbHelper::runFile($dataBaseName, $dbPath . '/03_indexes.sql');

$addressObjects->modifyDataAfterImport();
$houses->modifyDataAfterImport();

DbHelper::runFile($dataBaseName, $dbPath . '/04_constraints.sql');
DbHelper::runFile($dataBaseName, $dbPath . '/05_clean_up.sql');

UpdateLogHelper::addVersionIdToLog($db, $versionId);
