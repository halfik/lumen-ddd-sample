<?php

require_once __DIR__.'/../../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/
$appPath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);
$app = new Laravel\Lumen\Application($appPath);
$app->withFacades();

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

// configs
$app->configure('app');
$app->configure('cors');
$app->configure('database');
$app->configure('filesystems');
$app->configure('frontend');
$app->configure('logging');
$app->configure('mail');
$app->configure('migrations');
$app->configure('swagger-lume');

$app->setLocale(config('app.locale') ?? 'us_US');

$app->register(Bschmitt\Amqp\LumenServiceProvider::class);

$app->register(LaravelDoctrine\ORM\DoctrineServiceProvider::class);
$app->register(Illuminate\Database\MigrationServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(Fruitcake\Cors\CorsServiceProvider::class);
$app->register(SwaggerLume\ServiceProvider::class);

$app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\DoctrineServiceProvider::class);
$app->register(App\Providers\CommonServiceProvider::class);
$app->register(PulkitJalan\Google\GoogleServiceProvider::class);

$app->routeMiddleware([
    'api.auth' => App\Http\Middleware\ApiAuthMiddleware::class,
]);

$app->middleware([
    App\Http\Middleware\TrustProxiesMiddleware::class,
    App\Http\Middleware\TrimStringsMiddleware::class,
    \App\Http\Middleware\ConvertEmptyStringsToNull::class,
    \App\Http\Middleware\ConvertStringBooleans::class,
    Fruitcake\Cors\HandleCors::class,
]);

$app->router->group([
    'prefix' => 'api',
    'namespace' => 'App\Http\Controllers',
], function($router) {
    require __DIR__.'/../../Application/Routes/api.php';
    require __DIR__.'/../../Application/Routes/admin.php';
});

$app->router->get('/', function() {
    return redirect()->route('swagger-lume.api');
});

require_once __DIR__.'/helpers.php';

return $app;
