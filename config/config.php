<?php
return [
    'app_url' => getenv('APP_URL') ?: '',
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'astrosport',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'currency' => 'CLP',
    'flow' => [
        'api_key' => getenv('FLOW_API_KEY') ?: '',
        'secret_key' => getenv('FLOW_SECRET_KEY') ?: '',
        'api_url' => (getenv('FLOW_ENV') ?: 'sandbox') === 'production' ? 'https://www.flow.cl/api' : 'https://sandbox.flow.cl/api',
        'http_timeout' => 10,
        'order_timeout' => 1800,
    ],
    'transbank' => [
        'commerce_code' => getenv('TRANSBANK_COMMERCE_CODE') ?: '',
        'api_key_secret' => getenv('TRANSBANK_API_KEY_SECRET') ?: '',
        'environment' => getenv('TRANSBANK_ENV') ?: 'sandbox',
        'http_timeout' => 15,
    ],
    'mail' => [
        'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'no-reply@astrosport.cl',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'AstroSport',
        'reply_to' => getenv('MAIL_REPLY_TO') ?: 'contacto@astrosport.cl',
    ],
    'demo_admin' => true,
];
