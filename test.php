<?php

namespace Fias;

use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));

$db->execute('TRUNCATE TABLE address_objects CASCADE');
$db->execute('TRUNCATE TABLE pure_address_objects');
$db->execute('TRUNCATE TABLE houses');

$db->execute('START TRANSACTION');
$db->execute('SET CONSTRAINTS address_objects_parent_id_fkey DEFERRED');

$importConfig = $config->getParam('import')['address_objects'];
$importer     = new XmlImporter($db, 'address_objects', $importConfig['fields']);
$reader       = new XmlReader(
    '/home/dallone/Downloads/big_objects.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    $importConfig['filters']
);
$importer->import($reader);

$importConfig = $config->getParam('import')['houses'];
$importer     = new XmlImporter($db, 'houses', $importConfig['fields']);
$reader       = new XmlReader(
    '/home/dallone/Downloads/house_big.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    array()
);
$importer->import($reader);

$db->execute('COMMIT');
