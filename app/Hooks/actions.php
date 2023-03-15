<?php

use NinjaTables\App\Hooks\Handlers\AdminMenuHandler;
use NinjaTables\App\Hooks\Handlers\CPTHandler;
use NinjaTables\App\Hooks\Handlers\PublicDataHandler;
use NinjaTables\App\Hooks\Handlers\AjaxHandler;
use NinjaTables\App\Hooks\Handlers\PreviewHandler;
use NinjaTables\App\Hooks\Handlers\EditorBlockHandler;
use NinjaTables\App\Hooks\Handlers\StyleHandler;
use NinjaTables\App\Hooks\Handlers\DeactivationHandler;
use NinjaTables\App\Hooks\Handlers\NinjaTableAdminHandler;

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

$app->addAction('admin_menu', [AdminMenuHandler::class, 'add']);

$app->addAction('init', [CPTHandler::class, 'registerPostTypes']);

$app->addAction('init', [PublicDataHandler::class, 'registerTableRenderFunctions']);
$app->addAction('wp_enqueue_scripts', [PublicDataHandler::class, 'enqueueNinjaTableScript'], 100);
$app->addAction('ninja_tables-render-table-footable', [PublicDataHandler::class, 'runFooTable']);
$app->addAction('ninja_tables_inside_table_render', [PublicDataHandler::class, 'renderTableInsideTable'], 10, 2);

$app->addAction('wp_ajax_wp_ajax_ninja_tables_public_action', [AjaxHandler::class, 'registerAjaxRoutes'], 100);
$app->addAction('wp_ajax_nopriv_wp_ajax_ninja_tables_public_action', [AjaxHandler::class, 'registerAjaxRoutes'], 100);

$app->addAction('wp_loaded', [PreviewHandler::class, 'defaultTable']);
$app->addAction('wp_loaded', [PreviewHandler::class, 'dragAndDropTable']);

$app->addAction('init', [EditorBlockHandler::class, 'loadGutenBlock']);
$app->addAction('init', [EditorBlockHandler::class, 'addTablesToEditor']);

$app->addAction('admin_print_styles', [StyleHandler::class, 'adminMenuStyle']);

$app->addAction('ninja_table_check_db_integrity', function () {
    \NinjaTables\Database\Migrations\NinjaTableItemsMigrator::checkDBMigrations();
});

global $pagenow;
if ($pagenow == 'plugins.php') {
    $app->addAction('admin_footer', [DeactivationHandler::class, 'renderDeactivationFeedback']);
}
$app->addAction('wp_ajax_ninja-tables_deactivate_feedback', [DeactivationHandler::class, 'saveDeactivationFeedback']);

$app->addAction('wp_head', [NinjaTableAdminHandler::class, 'addNinjaTableAdminScript']);
$app->addAction('admin_notices', [NinjaTableAdminHandler::class, 'adminNotices']);
$app->addAction('init', [NinjaTableAdminHandler::class, 'remindMeLater']);

$app->addAction('init', [\NinjaTables\App\Modules\Lead\LeadFlow::class, 'boot']);
