<?php

namespace Fias;

use Fias\DataSource\Xml;
use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));

$db->execute('DROP TABLE IF EXISTS house_intervals_xml_importer');


$importConfig = $config->getParam('import')['house_intervals'];
$importer     = new Importer($db, 'house_intervals', $importConfig['fields']);
$reader       = new Xml(
    '/home/dallone/Downloads/house_big.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    array()
);
$importer->import($reader);

