<?php

return [
    /**
     * URL for the React app. Ie for generating URLs that are rendered by the
     * client react app, eg password-reset mails.
     */
    'base_url' => env('FRONTEND_URL', 'https://app.dummy.com'),
    'allow_redirect_uri_override' => env('ALLOW_REDIRECT_URI_OVERRIDE', false),

    'sign_in' => '/signin?token=:token',
    'password_reset' => '/reset-password?token=:token',
    'verify' => '/verify?token=:token',
];
