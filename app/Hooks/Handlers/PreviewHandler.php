<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;
use NinjaTables\App\Modules\DataProviders\NinjaFooTable;
use NinjaTables\Framework\Support\Arr;

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
            $app = App::getInstance();
            if (ninja_table_admin_role()) {
                wp_enqueue_style('ninja-tables-preview',
                    NINJA_TABLES_DIR_URL . "assets/css/ninja-tables-preview.css");

                $tableId = intval($_GET['ninjatable_builder_preview']);
                $table    = get_post($tableId);

                $ninja_table_builder_setting = get_post_meta($tableId, '_ninja_table_builder_table_settings', true);
                $custom_css = Arr::get($ninja_table_builder_setting, 'custom_css.value', '');
                if($custom_css !== '') {
                    $styleId = "ninja_table_builder_custom_css_$tableId";

                    $app->addAction('wp_head', function () use ($custom_css, $styleId) {
                        ?>
                        <style id="<?php echo $styleId; ?>" type='text/css'>
                            <?php echo ninjaTablesEscCss($custom_css); ?>
                        </style>
                        <?php
                    });
                }

                if ($table) {
                    $app->view->render('/admin/preview/drag-and-drop', [
                        'table_id' => $tableId
                    ]);
                    exit();
                }
            }
        }
    }
}
