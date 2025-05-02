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
        // Main block script
        wp_enqueue_script(
            'ninja-tables-gutenberg-table-block',
            NINJA_TABLES_DIR_URL . 'assets/gutenberg/gutenberg-table-block.js',
            array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch', 'jquery'),
            NINJA_TABLES_VERSION,
            true
        );

        // Footable integration script for Gutenberg
        wp_enqueue_script(
            'ninja-tables-footable-gutenberg',
            NINJA_TABLES_DIR_URL . 'assets/gutenberg/footable-gutenberg.js',
            array('jquery', 'footable'),
            NINJA_TABLES_VERSION,
            true
        );

        // Pass data to JavaScript
        wp_localize_script(
            'ninja-tables-gutenberg-table-block',
            'ninjaTablesGutenberg',
            [
                'availableTables' => $this->getAvailableTables(),
                'nonce'           => wp_create_nonce('ninja_tables_gutenberg_nonce'),
                'ajaxUrl'         => admin_url('admin-ajax.php'),
                'assetsUrl'       => NINJA_TABLES_DIR_URL . 'assets/'
            ]
        );
    }

    public function blockEditorAssets()
    {
        $app = App::getInstance();
        $assets = $app['url.assets'];

        wp_enqueue_style(
            'ninja-tables-footable',
            $assets . "css/ninjatables-public.css"
        );

        wp_enqueue_script(
            'footable',
            $assets . "libs/footable/js/footable.min.js",
            array('jquery'),
            '3.1.5',
            false
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
        $tableId = $request->get_param('table_id');
        $provider = ninja_table_get_data_provider($tableId);

        if (empty($tableId)) {
            return new \WP_Error('missing_table_id', 'Table ID is required', ['status' => 400]);
        }

        // Force editor mode for table
        add_filter('ninja_tables_item_attributes', function($atts) {
            $atts['editor_mode'] = true;
            return $atts;
        });

        // Enable search, sorting, and pagination
        add_filter('ninja_tables_settings', function($settings) {
            $settings['enable_search'] = true;
            $settings['column_sorting'] = true;
            $settings['show_all'] = 0; // Enable pagination
            $settings['perPage'] = 10; // 10 items per page
            return $settings;
        });

        // Get table HTML
        if ($provider == 'drag_and_drop') {
            $tableHtml = do_shortcode('[ninja_table_builder id="' . esc_attr($tableId) . '"]');
        } else {
            $tableHtml = do_shortcode('[ninja_tables id="' . esc_attr($tableId) . '"]');
        }

        // Add special wrapper with data attributes
        $tableHtml = '<div class="ninja-tables-gutenberg-preview" data-table-id="' . esc_attr($tableId) . '">' . $tableHtml . '</div>';

        // Note: We're not adding any inline JS here - it all goes in the JS file

        return [
            'success' => true,
            'html'    => $tableHtml,
            'table_id' => $tableId
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
            'label' => $title,
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
}
