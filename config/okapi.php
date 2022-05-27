<?php

return [
    'available_fields' => [
        'string' => [
            'name' => 'String',
            'column_type' => 'string',
        ],
        'text' => [
            'name' => 'Text',
            'column_type' => 'text',
        ],
//        'rich_text' => [
//            'name' => 'Rich Text',
//            'column_type' => 'longText',
//        ],
        'email' => [
            'name' => 'Email',
            'column_type' => 'string',
        ],
        'password' => [
            'name' => 'Password',
            'column_type' => 'string',
        ],
        'integer' => [
            'name' => 'Integer',
            'column_type' => 'integer',
        ],
        'double' => [
            'name' => 'Double',
            'column_type' => 'double',
        ],
        'enum' => [
            'name' => 'Enum',
            'column_type' => 'string',
        ],
        'date' => [
            'name' => 'Date',
            'column_type' => 'dateTimeTz',
        ],
        'hour' => [
            'name' => 'Hour',
            'column_type' => 'time',
        ],
        'file' => [
            'name' => 'File',
            'column_type' => 'string',
        ],
        'boolean' => [
            'name' => 'Boolean',
            'column_type' => 'boolean',
        ],
    ],

    'available_relationships' => [
        'has one' => 'One to one',
        'has many' => 'One to many',
        'belongs to one' => 'Many to one',
        'belongs to many' => 'Many to many',
    ]
];
