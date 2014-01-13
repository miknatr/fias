<?php

namespace Fias;

use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));


$db->execute('TRUNCATE TABLE address_objects');

$importConfig = $config->getParam('import')['address_objects'];
$importer     = new XmlImporter($db, 'address_objects', $importConfig['fields']);
$reader       = new XmlReader(
    '/home/dallone/Downloads/big_objects.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    $importConfig['filters']
);
$importer->import($reader);
