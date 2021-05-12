<?php

return [
    'xunsearch' => [

        'index'   => null,

        'search'  => null,

        'charset' => 'utf-8',

        'storage'  => [

            'cache'   => [
                'path'    => app()->bootstrapPath('cache/xunsearch'),
            ],

            'schema' => [
                /**
                 * @see http://www.xunsearch.com/doc/php/guide/ini.guide
                 *
                 * env('SCOUT_PREFIX') . 'users' => [
                 *      'id' => [
                 *          'type' => 'id',
                 *      ],
                 *      'nickname' => [
                 *          'type' => 'string',
                 *          'index' => 'self',
                 *          'tokenizer' => 'default',
                 *          'cutlen' => 0
                 *      ],
                 * ],
                 */
            ],
        ],
    ],
];
