<?php

namespace Fias;

use Fias\DataSource\Xml;
use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));
$db->execute('TRUNCATE TABLE houses');

$importConfig = $config->getParam('import')['houses'];
$importer     = new HousesImporter($db, $importConfig['table_name'], $importConfig['fields']);
$reader       = new Xml(
    '/home/dallone/Downloads/house_big.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    array()
);
$importer->import($reader);
