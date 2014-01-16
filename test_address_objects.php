<?php

namespace Fias;

use Fias\DataSource\Xml;
use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));
$db->execute('DROP TABLE IF EXISTS address_objects_xml_importer');

$importConfig = $config->getParam('import')['address_objects'];
$importer     = new Importer($db, 'address_objects', $importConfig['fields']);
$reader       = new Xml(
    '/home/dallone/Downloads/big_objects.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    $importConfig['filters']
);
$importer->import($reader);
