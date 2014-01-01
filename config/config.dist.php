<?php

return array(
    'wdsl_url' => 'http://fias.nalog.ru/WebServices/Public/DownloadService.asmx?WSDL',
    'file_folder'     => '/var/www/upload',
    'database'        => array(
        'adapter'  => 'pgsql',
        'host'     => '127.0.0.1',
        'port'     => '5432',
        'database' => 'fias',
        'user'     => 'postgres',
        'password' => '1',
    ),
);
