<?php

/**
 * @var $router NinjaTables\Framework\Http\Router\Router
 */

// common routes for common operations for every type of table
$router->prefix('tables')->group(function ($app) {
    $app->get('/', 'TablesController@index');
    $app->post('/', 'TablesController@store');

    $app->get('/{id}', 'TablesController@getTableSettings')->int('id');
    $app->put('/{id}', 'TablesController@updateTableSettings')->int('id');

    $app->delete('/{id}', 'TablesController@delete')->int('id');
    $app->post('/{id}/duplicate', 'TablesController@duplicate')->int('id');

    $app->get('/{id}/items', 'TableItemsController@index')->int('id');
    $app->delete('/{id}/items', 'TableItemsController@delete')->int('id');
    $app->post('/{id}/store-item', 'TableItemsController@store')->int('id');
});

$router->prefix('table-builder')->group(function ($app) {
    $app->get('/', 'TableBuilderController@index');
    $app->post('/', 'TableBuilderController@store');
    $app->put('/{id}', 'TableBuilderController@update')->int('id');
    $app->get('/{id}', 'TableBuilderController@show')->int('id');
});

$router->get('fluent-forms', 'FluentFormsController@index');

$router->get('wp-posts', 'WPPostsController@index');

$router->get('google-sheets', 'GoogleSheetsController@index');

$router->get('csv', 'ExternalCSVController@index');

$router->get('custom-sql', 'CustomSQLController@index');

$router->get('import', 'ImportController@index');

$router->get('woo', 'WooCommerceController@index');