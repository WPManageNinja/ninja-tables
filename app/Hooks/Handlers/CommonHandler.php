<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;
use NinjaTables\Framework\Support\Arr;


class CommonHandler
{
    public function addCustomCssSupport($tableId)
    {
        $ninja_table_builder_setting = get_post_meta($tableId, '_ninja_table_builder_table_settings', true);
        $custom_css                  = Arr::get($ninja_table_builder_setting, 'custom_css.value', '');
        if ($custom_css !== '') {
            $styleId = "ninja_table_builder_custom_css_$tableId";
            $app     = App::getInstance();
            $app->addAction('wp_head', function () use ($custom_css, $styleId) {
                ?>
                <style id="<?php echo $styleId; ?>" type='text/css'>
                    <?php echo ninjaTablesEscCss($custom_css); ?>
                </style>
                <?php
            });
        }
    }
}
