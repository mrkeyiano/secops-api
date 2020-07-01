<?php

return

    [
        'rubies' => [
        'key' => env('RUBIES_OPEN_API_KEY'),
        'root_url' => env('RUBIES_OPEN_API_ROOT_URL'),
        'rubies_bvn_checker' => env('RUBIES_BVN_CHECKER'),
        ],
        'encryption' => [
            'root_url' => env('ENCRYPTION_API_ROOT_URL'),
        ],
        'smile' => [
            'api_key' => env('SMILE_API_KEY'),
            'root_url' => env('SMILE_API_ROOT_URL'),
            'partner_id' => env('SMILE_API_PARTNER_ID'),
            ],

        'status' => [
            'failed' => 'failed',
            'success' => 'success',
            'not_found' => 'notfound',
            'server_error' => 'servererror',
            'not_allowed' => 'notallowed',
            'duplicate_transaction' => 'duplicatetransaction',
    ],

    'code' => [
        'success' => '00',
        'notexist' => '02',
        'exists' => '03',
        'insufficient' => '04',
        'network_error' => '05',
        'failed' => '06',
        'not_found' => '404',
        'server_error' => '500',
        'not_allowed' => '403',
        'duplicate_transaction' => '07',
    ],
        ];


