<?php

/**
 * @var $router NinjaTables\Framework\Http\Router\Router
 */

// common routes for common operations for every type of table
$router->prefix('table')->group(function ($app) {
    $app->get('/all', 'TablesController@index');
    $app->delete('/{id}', 'TablesController@deleteTable')->int('id');
    $app->post('/{id}/duplicate', 'TablesController@duplicateTable')->int('id');
});

$router->post('default', 'DefaultController@store');

$router->prefix('table-builder')->group(function ($app) {
    $app->get('/', 'TableBuilderController@index');
    $app->post('/', 'TableBuilderController@store');
    $app->put('/{id}', 'TableBuilderController@update')->int('id');
    $app->get('/{id}', 'TableBuilderController@show')->int('id');
});

$router->get('table-builder', 'TableBuilderController@index');

$router->get('fluent-forms', 'FluentFormsController@index');

$router->get('wp-posts', 'WPPostsController@index');

$router->get('google-sheets', 'GoogleSheetsController@index');

$router->get('csv', 'ExternalCSVController@index');

$router->get('custom-sql', 'CustomSQLController@index');

$router->get('import', 'ImportController@index');

$router->get('woo', 'WooCommerceController@index');
