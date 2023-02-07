<?php

/**
 * @var $router NinjaTables\Framework\Http\Router\Router
 */

$router->prefix('default')->group(function ($app) {
    $app->get('/', 'DefaultController@index');
    $app->post('/', 'DefaultController@store');
    $app->get('show/{id}', 'DefaultController@show')->int('id');
    $app->put('update/{id}', 'DefaultController@update')->int('id');
    $app->delete('remove/{id}', 'DefaultController@destroy')->int('id');
});

$router->prefix('table-builder')->group(function ($app) {
    $app->get('/', 'TableBuilderController@index');
});

$router->prefix('fluent-forms')->group(function ($app) {
    $app->get('/', 'FluentFormsController@index');
});

$router->prefix('wp-posts')->group(function ($app) {
    $app->get('/', 'WPPostsController@index');
});

$router->prefix('google-sheets')->group(function ($app) {
    $app->get('/', 'GoogleSheetsController@index');
});

$router->prefix('csv')->group(function ($app) {
    $app->get('/', 'ExternalCSVController@index');
});

$router->prefix('sql')->group(function ($app) {
    $app->get('/', 'CustomSQLController@index');
});

$router->prefix('import')->group(function ($app) {
    $app->get('/', 'ImportController@index');
});

$router->prefix('woo')->group(function ($app) {
    $app->get('/', 'WooCommerceController@index');
});