<?php

return [
    'address_objects' => [
        'table_name'   => 'address_objects',
        'node_name'    => 'Object',
        'xml_key'      => 'AOID',
        'database_key' => 'id',
        'fields'       => [
            'AOID'       => ['name' => 'id', 'type' => 'uuid'],
            'AOGUID'     => ['name' => 'address_id', 'type' => 'uuid'],
            'AOLEVEL'    => ['name' => 'address_level', 'type' => 'integer'],
            'PARENTGUID' => ['name' => 'parent_id', 'type' => 'uuid'],
            'FORMALNAME' => ['name' => 'title'],
            'POSTALCODE' => ['name' => 'postal_code', 'type' => 'integer'],
            'SHORTNAME'  => ['name' => 'prefix'],
            'REGIONCODE' => ['name' => 'region', 'type' => 'integer'],
        ],
        'filters' => [
            ['field' => 'ACTSTATUS', 'type' => 'eq', 'value' => 1],
        ],
    ],
    // При простановке false вместо массива загрузка домов не будет производиться.
    'houses' => [
        'table_name'   => 'houses',
        'node_name'    => 'House',
        'xml_key'      => 'HOUSEID',
        'database_key' => 'id',
        'fields'       => [
            'HOUSEID'   => ['name' => 'id', 'type' => 'uuid'],
            'HOUSEGUID' => ['name' => 'house_id', 'type' => 'uuid'],
            'AOGUID'    => ['name' => 'address_id', 'type' => 'uuid'],
            'HOUSENUM'  => ['name' => 'number'],
            'BUILDNUM'  => ['name' => 'building'],
            'STRUCNUM'  => ['name' => 'structure'],
        ],
    ],
];
