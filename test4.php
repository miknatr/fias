<?php

namespace Fias;

use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));


$db->execute('TRUNCATE TABLE pure_address_objects');

$importConfig = $config->getParam('import')['pure_address_objects'];
$importer     = new XmlImporter($db, 'pure_address_objects', $importConfig['fields']);
$reader       = new XmlReader(
    '/home/dallone/Downloads/big_objects.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    array()
);
$importer->import($reader);
