<?php

return [
    '__name' => 'lib-media',
    '__version' => '0.1.0',
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
                'collective' => false
            ],
            'media-list' => [
                'handler' => 'LibMedia\\Formatter\\Media::multiple',
                'collective' => false
            ]
        ]
    ]
];
