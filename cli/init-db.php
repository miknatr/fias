<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$db        = $container->getDb();
$dbPath    = $container->getDatabaseSourcesDirectory();

$db->execute(file_get_contents($dbPath . '/01_tables.sql'));
$db->execute(file_get_contents($dbPath . '/02_indexes.sql'));
$db->execute(file_get_contents($dbPath . '/03_constraints.sql'));
$db->execute(file_get_contents($dbPath . '/04_clean_up.sql'));
$db->execute(file_get_contents($dbPath . '/05_fakes.sql'));
