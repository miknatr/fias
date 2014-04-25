<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$loader    = $container->getUpdateLoader();

echo "Последняя версия: ", $loader->getLastFileInfo()->getVersionId(), "\n";
