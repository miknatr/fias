<?php

namespace Fias;

use Fias\DataSource\Xml;
use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));
//STOPPER ошибки не падают в STDERR
DbHelper::runFile($config->getParam('database')['database'], __DIR__ . '/database/01_tables.sql');

$addressObjectsConfig = $config->getParam('import')['address_objects'];
$addressObjects       = new AddressObjectsImporter($db, 'address_objects', $addressObjectsConfig['fields']);
$reader = new Xml(
    '/home/dallone/Downloads/big_objects.xml',
    $addressObjectsConfig['node_name'],
    array_keys($addressObjectsConfig['fields']),
    $addressObjectsConfig['filters']
);

$addressObjects->import($reader);

DbHelper::runFile($config->getParam('database')['database'], __DIR__ . '/database/02_indexes.sql');

$addressObjects->modifyDataAfterImport();

DbHelper::runFile($config->getParam('database')['database'], __DIR__ . '/database/03_constraints.sql');
