<?php

namespace Fias;

use Fias\Loader\UpdateLoader;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::get(__DIR__ . '/config/config.php');
$loader = new UpdateLoader($config->getParam('wsdl_url'), $config->getParam('file_directory'));

echo "Последняя версия: ", $loader->getLastFileInfo()->getVersionId(), "\n";
