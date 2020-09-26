<?php

return [
    'LibMedia\\Model\\MediaSize' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'primary_key' => TRUE,
                    'auto_increment' => TRUE
                ],
                'index' => 1000
            ],
            'media' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'index' => 2000
            ],
            'size' => [
                'type' => 'VARCHAR',
                'length' => 100,
                'attrs' => [
                    'null' => false,
                ],
                'index' => 3000
            ],
            'urls' => [
                'type' => 'TEXT',
                'attrs' => [
                    'null' => false
                ],
                'index' => 4000
            ],
            'updated' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 10000
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 11000
            ]
        ],
        'indexes' => [
            'by_media' => [
                'fields' => [
                    'media' => []
                ]
            ]
        ]
    ]
];