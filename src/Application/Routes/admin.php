<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group([
    'prefix' => 'admin-panel',
    'middleware' => ['api.auth:system-admin'],
], function () use ($router) {
    // company accounts
    $router->group(['prefix' => 'company-accounts'], function () use ($router) {
        $router->put('/{companyId}/block', [
            'uses' => 'AdminPanel\CompanyAccountController@block',
        ]);

        $router->put('/{companyId}/unblock', [
            'uses' => 'AdminPanel\CompanyAccountController@unblock',
        ]);
    });
});
