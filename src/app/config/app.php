<?php

return [
    'debug' => ENV('APP_ENV') === 'develop',
    'date' => [
        'full_format' => 'm/d/Y H:i:s',
        'csv_format' => 'm/d/Y H:i',
        'short_format' => 'm/d/Y',
    ],
    'locale' => $_ENV['locale'] ?? 'us_US',
];
