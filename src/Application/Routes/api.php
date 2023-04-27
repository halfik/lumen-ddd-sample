<?php

use Illuminate\Routing\Router;

/** @var Router $router */

// passwords
$router->group([
    'prefix' => 'password',
    'middleware' => ['api.auth'],
], function () use ($router) {
    $router->put('/', [
        'uses' => 'Accounts\PasswordController@changeOwn'
    ]);
});

// users
$router->group(['prefix' => 'users'], function () use ($router) {
    $router->post('/', [
        'uses' => 'Accounts\UserController@register'
    ]);
});

$router->post('/login', [
    'uses' => 'Accounts\AuthController@authenticate'
]);

// workflows
/** @var Router $router */
$router->group([
    'prefix' => 'workflows',
    'middleware' => ['api.auth'],
], function () use ($router) {
    // leads
    $router->group(['prefix' => '/{workflowId}/leads'], function () use ($router) {
        $router->post('/', [
            'uses' => 'Sales\LeadController@create'
        ]);
        $router->delete('/{leadId}', [
            'uses' => 'Sales\LeadController@delete'
        ]);
        $router->patch('/{leadId}/close', [
            'uses' => 'Sales\LeadController@close'
        ]);
        $router->patch('/{leadId}/reopen', [
            'uses' => 'Sales\LeadController@reopen'
        ]);
    });


    // stages
    $router->group(['prefix' => '/{workflowId}/stages'], function () use ($router) {
        $router->post('/', [
            'uses' => 'Sales\StageController@create'
        ]);
    });
});
