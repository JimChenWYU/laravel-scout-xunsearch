<?php

return [
    'xunsearch' => [

        'index'   => null,

        'search'  => null,

        'tokenizer' => [
            'class' => \JimChen\LaravelScout\XunSearch\Tokenizers\ScwsTokenizer::class,
        ],

        'charset' => 'utf-8',

        'options' => [

            'schema_prefix' => env('SCOUT_PREFIX'),

            'schemas' => [
                /**
                 * @see http://www.xunsearch.com/doc/php/guide/ini.guide
                 *
                 * 'users' => [
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
