<?php

use Fias\Container;
use Fias\Loader\UpdateLoader;

require_once __DIR__ . '/vendor/autoload.php';

$container = new Container();

$loader = new UpdateLoader($container->getWsdlUrl(), $container->getFileDirectory());

echo "Последняя версия: ", $loader->getLastFileInfo()->getVersionId(), "\n";
