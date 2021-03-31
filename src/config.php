<?php

return [
    'db' => [
        'host' => '127.0.0.1:27017',
        'name' => 'casein_db',
        'user' => '',
        'pass' => '',
    ],
    'api' => [
        'v' => '1.0',
        'url' => (!empty($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']."/"
    ]
];
