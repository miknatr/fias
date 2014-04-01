<?php

namespace Fias;

use Fias\DataSource\XmlReader;
use Fias\Loader\UpdateLoader;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$container = new Container();
$db        = $container->getDb();

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
            $path = Dearchiver::extract($container->getFileDirectory(), $path);
        }

        $directory = new Directory($path);
    } else {
        $loader    = new UpdateLoader($container->getWsdlUrl(), $container->getFileDirectory());
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
    if ($deletedHouseFile) {
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
                array($housesConfig['primary_key']),
                array()
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
                array($addressObjectsConfig['xml_key']),
                array()
            )
        );
    }

    $addressObjectFields           = $addressObjectsConfig['fields'];
    $addressObjectFields['PREVID'] = array('name' => 'previous_id', 'type' => 'uuid');
    $addressObjectsUpdater         = new AddressObjectsUpdater($db, $addressObjectsConfig['table_name'], $addressObjectFields);
    $addressObjectsUpdater->update(
        new XmlReader(
            $directory->getAddressObjectFile(),
            $addressObjectsConfig['node_name'],
            array_keys($addressObjectFields),
            $addressObjectsConfig['filters']
        )
    );

    $houseFields           = $housesConfig['fields'];
    $houseFields['PREVID'] = array('name' => 'previous_id', 'type' => 'uuid');
    $housesUpdater         = new HousesUpdater($db, $housesConfig['table_name'], $houseFields);
    $housesUpdater->update(new XmlReader(
        $directory->getHousesFile(),
        $housesConfig['node_name'],
        array_keys($houseFields),
        array()
    ));

    UpdateLogHelper::addVersionIdToLog($db, $directory->getVersionId());

    $db->commit();
} catch (\Exception $e) {
    $log->addError($e->getMessage());
    $db->rollback();
    fwrite(STDERR, "В процессе обновления произошла ошибка.\n");
    exit(1);
}
