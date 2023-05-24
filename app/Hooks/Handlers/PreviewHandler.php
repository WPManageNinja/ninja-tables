<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;
use NinjaTables\App\Modules\DataProviders\NinjaFooTable;

class PreviewHandler
{
    public function defaultTable()
    {
        $tableId = null;

        if (isset($_GET['ninjatable_preview'])) {
            $tableId = intval($_GET['ninjatable_preview']);
        }

        if ($tableId) {
            if (ninja_table_admin_role()) {
                do_action('ninja_tables_will_render_table', $tableId);

                wp_enqueue_style('ninja-tables-preview',
                    NINJA_TABLES_DIR_URL . "assets/css/ninja-tables-preview.css");

                NinjaFooTable::enqueuePublicCss();
                $table = get_post($tableId);

                if ($table) {
                    $app = App::getInstance();
                    $app->view->render('/admin/preview/default-table', [
                        'table_id' => $tableId
                    ]);
                    exit();
                }
            }
        }
    }

    public function dragAndDropTable()
    {
        if (isset($_GET['ninjatable_builder_preview']) && $_GET['ninjatable_builder_preview']) {
            if (ninja_table_admin_role()) {
                $tableId = intval($_GET['ninjatable_builder_preview']);

                do_action('ninja_tables_will_render_table', $tableId);

                wp_enqueue_style('ninja-tables-preview',
                    NINJA_TABLES_DIR_URL . "assets/css/ninja-tables-preview.css");

                $table   = get_post($tableId);

                if ($table) {
                    $app = App::getInstance();
                    $app->view->render('/admin/preview/drag-and-drop', [
                        'table_id' => $tableId
                    ]);
                    exit();
                }
            }
        }
    }
}
