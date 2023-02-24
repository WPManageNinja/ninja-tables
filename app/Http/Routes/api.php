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
    $app->get('/{id}/custom-styles', 'TablesController@getCustomCSSJS')->int('id');
    $app->post('/{id}/custom-styles', 'TablesController@saveCustomCSSJS')->int('id');

    $app->get('/{id}/items', 'TableItemsController@index')->int('id');
    $app->delete('/{id}/items', 'TableItemsController@delete')->int('id');
    $app->post('/{id}/items', 'TableItemsController@store')->int('id');
    $app->put('/{id}/edit', 'TableItemsController@update')->int('id');

    $app->post('/dismiss-fluent-suggest', 'TablesController@dismissFluentSuggest');
});

$router->prefix('tables/tools')->group(function ($app) {
    $app->get('/default-settings', 'ToolsController@getDefaultSettings');
    $app->post('/default-settings', 'ToolsController@saveDefaultSettings');
    $app->get('/permission', 'ToolsController@getAccessRoles');
    $app->get('/global-settings', 'ToolsController@getGlobalSettings');
    $app->post('/global-settings', 'ToolsController@updateGlobalSettings');
    $app->post('/clear-cache', 'ToolsController@clearTableCache');
});

$router->prefix('table-builder')->group(function ($app) {
    $app->get('/', 'TableBuilderController@index');
    $app->post('/', 'TableBuilderController@store');
    $app->post('/import', 'TableBuilderController@import');
    $app->patch('/{id}', 'TableBuilderController@update')->int('id');
    $app->get('/{id}', 'TableBuilderController@show')->int('id');
});

$router->prefix('fluent-forms')->group(function ($app) {
    $app->post('/', 'FluentFormsController@install');
});

$router->get('wp-posts', 'WPPostsController@index');

$router->get('google-sheets', 'GoogleSheetsController@index');

$router->get('csv', 'ExternalCSVController@index');

$router->get('custom-sql', 'CustomSQLController@index');

$router->get('import', 'ImportController@index');

$router->get('woo', 'WooCommerceController@index');
