<?php

return [
    '__name' => 'lib-media',
    '__version' => '1.4.0',
    '__git' => 'git@github.com:getmim/lib-media.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-media' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-image' => NULL
            ],
            [
                'lib-model' => NULL
            ]
        ],
        'optional' => [
            [
                'lib-compress' => NULL
            ],
            [
                'lib-formatter' => NULL
            ],
            [
                'lib-upload' => NULL
            ]
        ]
    ],
    'autoload' => [
        'classes' => [
            'LibMedia\\Formatter' => [
                'type' => 'file',
                'base' => 'modules/lib-media/formatter'
            ],
            'LibMedia\\Iface' => [
                'type' => 'file',
                'base' => 'modules/lib-media/interface'
            ],
            'LibMedia\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-media/library'
            ],
            'LibMedia\\Object' => [
                'type' => 'file',
                'base' => 'modules/lib-media/object'
            ],
            'LibMedia\\Model' => [
                'type' => 'file',
                'base' => 'modules/lib-media/model'
            ]
        ],
        'files' => []
    ],
    'libMedia' => [
        'handlers' => [
            'local' => 'LibMedia\\Library\\Local'
        ]
    ],
    'libFormatter' => [
        'handlers' => [
            'media' => [
                'handler' => 'LibMedia\\Formatter\\Media::single',
                'collective' => TRUE
            ],
            'media-list' => [
                'handler' => 'LibMedia\\Formatter\\Media::multiple',
                'collective' => '_MD5_'
            ]
        ]
    ]
];
