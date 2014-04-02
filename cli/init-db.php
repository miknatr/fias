<?php

require_once __DIR__ . '/../vendor/autoload.php';

$db = (new Container())->getDb();
$db_path = __DIR__ . '/../database/';
$db->execute(file_get_contents($db_path . '01_tables.sql'));
$db->execute(file_get_contents($db_path . '02_indexes.sql'));
$db->execute(file_get_contents($db_path . '03_constraints.sql'));
$db->execute(file_get_contents($db_path . '04_clean_up.sql'));
