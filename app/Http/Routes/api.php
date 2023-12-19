<?php

/**
 * @var $router NinjaTables\Framework\Http\Router\Router
 */

use NinjaTables\App\Http\Controllers\ExportTableController;
use NinjaTables\App\Http\Controllers\FluentFormsController;
use NinjaTables\App\Http\Controllers\ImportController;
use NinjaTables\App\Http\Controllers\PluginInstallerController;
use NinjaTables\App\Http\Controllers\SettingsController;
use NinjaTables\App\Http\Controllers\TableBuilderController;
use NinjaTables\App\Http\Controllers\TableItemsController;
use NinjaTables\App\Http\Controllers\TablesController;
use NinjaTables\App\Http\Controllers\ToolsController;
use NinjaTables\App\Http\Controllers\WPPostsController;

$router->withPolicy('UserPolicy')->group(function ($router) {
    $router->prefix('tables')->group(function ($route) {
        $route->get('/', [TablesController::class, 'index']);
        $route->post('/', [TablesController::class, 'store']);
        $route->post('/dismiss-fluent-suggest', [TablesController::class, 'dismissFluentSuggest']);

        $route->prefix('/{id}')->group(function ($route) {
            $route->delete('/', [TablesController::class, 'delete'])->int('id');
            $route->post('/duplicate', [TablesController::class, 'duplicate'])->int('id');
            $route->get('/table-inner-html', [TablesController::class, 'tableInnerHtml'])->int('id');
            $route->prefix('/item')->group(function ($route) {
                $route->get('/', [TableItemsController::class, 'index'])->int('id');
                $route->post('/', [TableItemsController::class, 'store'])->int('id');
                $route->put('/', [TableItemsController::class, 'update'])->int('id');
                $route->delete('/', [TableItemsController::class, 'delete'])->int('id');
            });
        });
    });

    $router->prefix('settings/{id}')->group(function ($route) {
        $route->get('/', [SettingsController::class, 'getTableSettings'])->int('id');
        $route->post('/', [SettingsController::class, 'updateTableSettings'])->int('id');
        $route->get('/button', [SettingsController::class, 'getButtonSettings'])->int('id');
        $route->put('/button', [SettingsController::class, 'updateButtonSettings'])->int('id');
        $route->get('/custom-styles', [SettingsController::class, 'getCustomCSSJS'])->int('id');
        $route->post('/custom-styles', [SettingsController::class, 'saveCustomCSSJS'])->int('id');
    });

    $router->prefix('tables/tools')->group(function ($route) {
        $route->get('/default-settings', [ToolsController::class, 'getDefaultSettings']);
        $route->post('/default-settings', [ToolsController::class, 'saveDefaultSettings']);
        $route->get('/permission', [ToolsController::class, 'getAccessRoles']);
        $route->get('/global-settings', [ToolsController::class, 'getGlobalSettings']);
        $route->post('/global-settings', [ToolsController::class, 'updateGlobalSettings']);
        $route->post('/clear-table-cache', [ToolsController::class, 'clearTableCache']);
        $route->post('/clear-external-cache', [ToolsController::class, 'clearExternalTableCache']);
    });

    $router->prefix('table-builder')->group(function ($route) {
        $route->get('/', [TableBuilderController::class, 'index']);
        $route->post('/', [TableBuilderController::class, 'store']);
        $route->patch('/{id}', [TableBuilderController::class, 'update'])->int('id');
        $route->get('/{id}', [TableBuilderController::class, 'show'])->int('id');
    });

    $router->prefix('fluent-forms')->group(function ($route) {
        $route->get('/', [FluentFormsController::class, 'index']);
        $route->post('/save', [FluentFormsController::class, 'store']);
        $route->get('/{id}', [FluentFormsController::class, 'getFormsFields'])->int('id');
    });

    $router->prefix('wp-posts')->group(function ($route) {
        $route->get('/', [WPPostsController::class, 'getPostTypes']);
        $route->get('/authors', [WPPostsController::class, 'getPostTypesAuthor']);
    });

    $router->prefix('import')->group(function ($route) {
        $route->post('/default', [ImportController::class, 'defaultImport']);
        $route->post('/table-builder', [ImportController::class, 'tableBuilderImport']);
        $route->post('/get-tables-from-other-plugin', [ImportController::class, 'getTablesFromOtherPlugin']);
        $route->post('/import-table-from-other-plugin', [ImportController::class, 'importTableFromOtherPlugin']);
        $route->post('/upload-csv-in-existing-table', [ImportController::class, 'uploadCsvInExistingTable']);
    });

    $router->prefix('install')->group(function ($route) {
        $route->post('/fluent-forms', [PluginInstallerController::class, 'installFluentForms']);
        $route->post('/ninja-charts', [PluginInstallerController::class, 'installNinjaCharts']);
    });
});

