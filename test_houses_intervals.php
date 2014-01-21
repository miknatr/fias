<?php

namespace Fias;

use Fias\DataSource\IntervalGenerator;
use Fias\DataSource\Xml;
use Grace\DBAL\ConnectionFactory;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$db     = ConnectionFactory::getConnection($config->getParam('database'));

$db->execute('DROP TABLE IF EXISTS house_intervals_xml_importer');


$importConfig = $config->getParam('import')['house_intervals'];
$importer     = new Importer($db, 'house_intervals', $importConfig['fields']);
$generator = new IntervalGenerator((new Xml(
    '/home/dallone/Downloads/house_int.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    array()
)), 'INTSTART', 'INTEND', 'INTSTATUS', 'HOUSENUM');

$importer->import($generator);

