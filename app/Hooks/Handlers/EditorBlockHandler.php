<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;
use NinjaTables\Framework\Support\Arr;

class EditorBlockHandler
{
    public $cpt_name = 'ninja-table';

    public function loadGutenBlock()
    {
        add_action('enqueue_block_editor_assets', function () {
            wp_enqueue_script(
                'ninja-tables-gutenberg-block',
                NINJA_TABLES_DIR_URL . 'assets/js/ninja-tables-gutenblock.js',
                array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor')
            );

            wp_enqueue_style(
                'ninja-tables-gutenberg-block',
                NINJA_TABLES_DIR_URL . 'assets/css/ninja-tables-gutenblock.css',
                array('wp-edit-blocks')
            );
        });
    }

    public function addTablesToEditor()
    {
        $pages_with_editor_button = array('post.php', 'post-new.php');
        foreach ($pages_with_editor_button as $editor_page) {
            add_action("load-{$editor_page}", array($this, 'initNinjaMceButtons'));
        }
    }

    public function initNinjaMceButtons()
    {

        if ( ! user_can_richedit()) {
            return;
        }
        add_filter("mce_external_plugins", array($this, 'NinjaTablesAddButtons'));
        add_filter('mce_buttons', array($this, 'NinjaTablesRegisterButton'));
        add_action('admin_footer', array($this, 'pushNinjaTablesToEditorFooter'));
    }

    public function NinjaTablesAddButtons($plugin_array)
    {
        $plugin_array['ninja_table'] = NINJA_TABLES_DIR_URL . 'assets/js/ninja-table-tinymce-button.js';

        return $plugin_array;
    }

    public function NinjaTablesRegisterButton($buttons)
    {
        array_push($buttons, 'ninja_table');

        return $buttons;
    }

    public function pushNinjaTablesToEditorFooter()
    {
        $tables = $this->getAllTablesForMce();
        ?>
        <script type="text/javascript">
            window.ninja_tables_tiny_mce = {
                label: '<?php _e('Select a Table to insert', 'ninja-tables') ?>',
                title: '<?php _e('Insert Ninja Tables Shortcode', 'ninja-tables') ?>',
                select_error: '<?php _e('Please select a table'); ?>',
                insert_text: '<?php _e('Insert Shortcode', 'ninja-tables'); ?>',
                tables: <?php echo json_encode($tables); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped $tables is already escaped before being passed in. ?>,
                logo: '<?php echo esc_url(NINJA_TABLES_DIR_URL . 'assets/img/ninja-table-editor-button-2x.png'); ?>'
            }
        </script>
        <?php
    }

    private function getAllTablesForMce()
    {
        $args = array(
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_type'      => $this->cpt_name,
            'post_status'    => 'any'
        );

        $tables    = get_posts($args);
        $formatted = array();

        $title = __('Select a Table', 'ninja-tables');
        if ( ! $tables) {
            $title = __('No Tables found. Please add a table first');
        }
        $formatted[] = array(
            'text'  => $title,
            'value' => ''
        );

        foreach ($tables as $table) {
            $formatted[] = array(
                'text'        => esc_attr($table->post_title),
                'value'       => $table->ID,
                'data_source' => esc_attr(ninja_table_get_data_provider($table->ID))
            );
        }

        return $formatted;
    }

    public function addCustomCss($tableId)
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
