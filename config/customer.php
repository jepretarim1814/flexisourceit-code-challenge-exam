<?php

return [
    'importer_default_driver' => env('IMPORTER_DRIVER', 'default'),

    'importer_drivers' => [
        'default' => [
            'driver' => 'default',
            'url' => 'https://randomuser.me/api/',
            'version' => '1.3',
            'nationalities' => [
                'au'
            ],
            'fields' => [
                'name', // Where first and last name
                'email',
                'login', // Where username
                'gender',
                'location', // Where country and city
                'phone',
            ],
            'count' => 100 // How many results to import
        ]
    ]
];
