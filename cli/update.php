<?php

use Bravicility\Failure\FailureHandler;
use FileSystem\Dearchiver;
use FileSystem\Directory;
use DataSource\XmlReader;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$db        = $container->getDb();
$logger    = $container->getErrorLogger();

FailureHandler::setup(function ($error) use ($logger) {
    $logger->error($error['message'], $error);
    fwrite(STDERR, "В процессе инициализации произошла ошибка:\n{$error['message']}\n");
    exit(1);
});

$db->start();

if ($_SERVER['argc'] == 2) {
    $path = $_SERVER['argv']['1'];
    if (!is_dir($path)) {
        $path = Dearchiver::extract($container->getFileDirectory(), $path);
    }

    $directory = new Directory($path);
} else {
    $loader    = $container->getUpdateLoader();
    $directory = $loader->load();
}

$oldVersionId = UpdateLogHelper::getLastVersionId($db);
$newVersionId = $directory->getVersionId();

if ($newVersionId != ($oldVersionId + 1)) {
    throw new \LogicException("Попытка обновления с версии {$oldVersionId} на версию {$newVersionId}.");
}

$db->execute('SET CONSTRAINTS "address_objects_parent_id_fkey", "houses_parent_id_fkey" DEFERRED');

$housesConfig         = $container->getHousesImportConfig();
$addressObjectsConfig = $container->getAddressObjectsImportConfig();

$deletedHouseFile = $directory->getDeletedHouseFile();
if ($deletedHouseFile && $housesConfig) {
    $houseRemover = new Remover(
        $db,
        $housesConfig['table_name'],
        $housesConfig['xml_key'],
        $housesConfig['database_key']
    );
    $houseRemover->remove(
        new XmlReader(
            $deletedHouseFile,
            $housesConfig['node_name'],
            [$housesConfig['primary_key']],
            []
        )
    );
}

$deletedAddressObjectsFile = $directory->getDeletedAddressObjectFile();
if ($deletedAddressObjectsFile) {
    $addressObjectsRemover = new Remover(
        $db,
        $addressObjectsConfig['table_name'],
        $addressObjectsConfig['xml_key'],
        $addressObjectsConfig['database_key']
    );
    $addressObjectsRemover->remove(
        new XmlReader(
            $deletedAddressObjectsFile,
            $addressObjectsConfig['node_name'],
            [$addressObjectsConfig['xml_key']],
            []
        )
    );
}

$addressObjectFields           = $addressObjectsConfig['fields'];
$addressObjectFields['PREVID'] = ['name' => 'previous_id', 'type' => 'uuid'];
$addressObjectsUpdater         = new AddressObjectsUpdater($db, $addressObjectsConfig['table_name'], $addressObjectFields);
$addressObjectsUpdater->update(
    new XmlReader(
        $directory->getAddressObjectFile(),
        $addressObjectsConfig['node_name'],
        array_keys($addressObjectFields),
        $addressObjectsConfig['filters']
    )
);

if ($housesConfig) {
    $houseFields           = $housesConfig['fields'];
    $houseFields['PREVID'] = ['name' => 'previous_id', 'type' => 'uuid'];
    $housesUpdater         = new HousesUpdater($db, $housesConfig['table_name'], $houseFields);
    $housesUpdater->update(new XmlReader(
        $directory->getHouseFile(),
        $housesConfig['node_name'],
        array_keys($houseFields),
        []
    ));
}

UpdateLogHelper::addVersionIdToLog($db, $directory->getVersionId());

$db->commit();
