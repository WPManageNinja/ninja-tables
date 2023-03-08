<?php

/**
 * All registered action's handlers should be in app\Hooks\Handlers,
 * addAction is similar to add_action and addCustomAction is just a
 * wrapper over add_action which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomAction('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_action('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app NinjaTables\Framework\Foundation\Application
 */

$app->addAction('admin_menu', 'AdminMenuHandler@add');

/**
 * Enable this line if you want to use custom post types
 */

$app->addAction('init', 'CPTHandler@registerPostTypes');

$app->addAction('init', 'PublicDataHandler@registerTableRenderFunctions');

//$app->addAction('wp_enqueue_scripts', 'PublicDataHandler@enqueueNinjaTableScript', 100);

$app->addAction('ninja_tables-render-table-footable','PublicDataHandler@runFooTable');

$app->addAction('wp_ajax_wp_ajax_ninja_tables_public_action', 'AjaxHandler@registerAjaxRoutes', 100);
$app->addAction('wp_ajax_nopriv_wp_ajax_ninja_tables_public_action', 'AjaxHandler@registerAjaxRoutes', 100);

$app->addAction('ninja_tables_inside_table_render', 'PublicDataHandler@renderTableInsideTable', 10, 2);

$app->addAction('wp_loaded', 'PreviewHandler@defaultTable');
$app->addAction('wp_loaded', 'PreviewHandler@dragAndDropTable');
