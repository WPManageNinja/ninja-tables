<?php

namespace NinjaTables\App\Modules\Gutenberg;

use NinjaTables\App\App;

class GutenbergModule
{
    public function register()
    {
        add_action('enqueue_block_assets', [$this, 'blockEditorAssets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueAssets']);
        add_action('rest_api_init', [$this, 'registerRestRoutes']);
    }

    public function enqueueAssets()
    {
        wp_enqueue_script(
            'ninja-tables-gutenberg-table-block',
            NINJA_TABLES_DIR_URL . 'assets/gutenberg/gutenberg-table-block.js',
            array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch'),
            NINJA_TABLES_VERSION,
            true
        );

        // Pass table data to JavaScript
        wp_localize_script(
            'ninja-tables-gutenberg-table-block',
            'ninjaTablesGutenberg',
            [
                'availableTables' => $this->getAvailableTables(),
                'nonce'           => wp_create_nonce('ninja_tables_gutenberg_nonce'),
                'ajaxUrl'         => admin_url('admin-ajax.php')
            ]
        );
    }
    public function registerRestRoutes()
    {
        register_rest_route('ninja-tables/v1', '/get-table-preview', [
            'methods'             => 'GET',
            'callback'            => [$this, 'getTablePreview'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args'                => [
                'table_id' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
    }

    public function getTablePreview($request)
    {
        $tableId  = $request->get_param('table_id');
        $provider = ninja_table_get_data_provider($tableId);

        if (empty($tableId)) {
            return new \WP_Error('missing_table_id', 'Table ID is required', ['status' => 400]);
        }

        if ($provider == 'drag_and_drop') {
            $tableHtml = do_shortcode('[ninja_table_builder id="' . esc_attr($tableId) . '"]');
        } else {
            $tableHtml = do_shortcode('[ninja_tables id="' . esc_attr($tableId) . '"]');
        }

        return [
            'success' => true,
            'html'    => $tableHtml
        ];
    }

    private function getAvailableTables()
    {
        $args = array(
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_type'      => 'ninja-table',
            'post_status'    => 'any'
        );

        $tables    = get_posts($args);
        $formatted = array();

        $title = __('Select a Table', 'ninja-tables');
        if (!$tables) {
            $title = __('No Tables found. Please add a table first');
        }
        $formatted[] = array(
            'text'  => $title,
            'value' => ''
        );

        foreach ($tables as $table) {
            $formatted[] = array(
                'label'       => esc_attr($table->post_title),
                'value'       => $table->ID,
                'data_source' => esc_attr(ninja_table_get_data_provider($table->ID))
            );
        }

        return $formatted;
    }

    public function blockEditorAssets()
    {
        $app = App::getInstance();

        $assets = $app['url.assets'];

        wp_enqueue_style('ninja-tables-footable', $assets . "css/ninjatables-public.css");
    }
}
