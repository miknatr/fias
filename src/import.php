<?php

return array(
    'address_objects' => array(
        'table_name'   => 'address_objects',
        'node_name'    => 'Object',
        'xml_key'      => 'AOID',
        'database_key' => 'id',
        'fields'       => array(
            'AOID'       => array('name' => 'id', 'type' => 'uuid'),
            'AOGUID'     => array('name' => 'address_id', 'type' => 'uuid'),
            'AOLEVEL'    => array('name' => 'address_level', 'type' => 'integer'),
            'PARENTGUID' => array('name' => 'parent_id', 'type' => 'uuid'),
            'FORMALNAME' => array('name' => 'title'),
            'POSTALCODE' => array('name' => 'postal_code', 'type' => 'integer'),
            'SHORTNAME'  => array('name' => 'prefix'),
            'REGIONCODE' => array('name' => 'region', 'type' => 'integer'),
        ),
        'filters' => array(
            array('field' => 'ACTSTATUS', 'type' => 'eq', 'value' => 1),
        ),
    ),
    'houses' => array(
        'table_name'   => 'houses',
        'node_name'    => 'House',
        'xml_key'      => 'HOUSEID',
        'database_key' => 'id',
        'fields'       => array(
            'HOUSEID'   => array('name' => 'id', 'type' => 'uuid'),
            'HOUSEGUID' => array('name' => 'house_id', 'type' => 'uuid'),
            'AOGUID'    => array('name' => 'address_id', 'type' => 'uuid'),
            'HOUSENUM'  => array('name' => 'number'),
            'BUILDNUM'  => array('name' => 'building'),
            'STRUCNUM'  => array('name' => 'structure'),
        ),
    ),
);
