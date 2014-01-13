<?php

namespace Fias;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get('config');
$importConfig = $config->getParam('import')['pure_address_objects'];
$reader       = new XmlReader(
    '/home/dallone/Downloads/big_objects.xml',
    $importConfig['node_name'],
    array_keys($importConfig['fields']),
    array()
);

while($reader->getRows()) {
    // do nothing;
}
