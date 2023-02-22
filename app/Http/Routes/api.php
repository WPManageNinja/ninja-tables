<?php

/**
 * @var $router NinjaTables\Framework\Http\Router\Router
 */

// common routes for common operations for every type of table
$router->prefix('tables')->group(function ($app) {
    $app->get('/', 'TablesController@index');
    $app->post('/', 'TablesController@store');

    $app->get('/{id}', 'TablesController@getTableSettings')->int('id');
    $app->post('/{id}', 'TablesController@updateTableSettings')->int('id');
    $app->delete('/{id}', 'TablesController@delete')->int('id');
    $app->post('/{id}/duplicate', 'TablesController@duplicate')->int('id');

    $app->get('/{id}/button-settings', 'TablesController@getButtonSettings')->int('id');
    $app->put('/{id}/button-settings', 'TablesController@updateButtonSettings')->int('id');

    $app->get('/{id}/items', 'TableItemsController@index')->int('id');
    $app->delete('/{id}/items', 'TableItemsController@delete')->int('id');
    $app->post('/{id}/items', 'TableItemsController@store')->int('id');
});

$router->prefix('table-builder')->group(function ($app) {
    $app->get('/', 'TableBuilderController@index');
    $app->post('/', 'TableBuilderController@store');
    $app->post('/import', 'TableBuilderController@import');
    $app->patch('/{id}', 'TableBuilderController@update')->int('id');
    $app->get('/{id}', 'TableBuilderController@show')->int('id');
});

$router->get('fluent-forms', 'FluentFormsController@index');

$router->get('wp-posts', 'WPPostsController@index');

$router->get('google-sheets', 'GoogleSheetsController@index');

$router->get('csv', 'ExternalCSVController@index');

$router->get('custom-sql', 'CustomSQLController@index');

$router->get('import', 'ImportController@index');

$router->get('woo', 'WooCommerceController@index');
