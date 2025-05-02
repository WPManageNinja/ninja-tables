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

        add_action('admin_footer', [$this, 'addFootableAdminScript']);
    }

    public function enqueueAssets()
    {
        wp_enqueue_script(
            'ninja-tables-gutenberg-table-block',
            NINJA_TABLES_DIR_URL . 'assets/gutenberg/gutenberg-table-block.js',
            array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch', 'jquery'),
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

    /**
     * Add Footable initialization script to admin footer
     */
    public function addFootableAdminScript() {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || !method_exists($screen, 'is_block_editor') || !$screen->is_block_editor()) {
            return;
        }

        ?>
        <script type="text/javascript">
            (function($) {
                // Store initializedTables to prevent multiple initializations
                var initializedTables = {};
                var initializationInProgress = false;

                // Global function to initialize Footable
                window.initNinjaTableFootable = function(specificSelector) {
                    // Prevent overlapping initialization calls
                    if (initializationInProgress) {
                        console.log('Ninja Tables: Initialization already in progress, skipping call');
                        return;
                    }

                    initializationInProgress = true;

                    // Default selector if none provided
                    var selector = specificSelector || '.ninja-tables-gutenberg-preview table.ninja_footable';

                    if (typeof $ !== 'function' || typeof $.fn.footable !== 'function') {
                        console.error('Ninja Tables: jQuery or Footable not available');
                        initializationInProgress = false;
                        return;
                    }

                    // Find all tables matching the selector
                    var $tables = $(selector);

                    if ($tables.length) {
                        console.log('Ninja Tables: Found ' + $tables.length + ' tables to initialize');

                        // Process each table
                        $tables.each(function() {
                            var $table = $(this);
                            var tableId = $table.attr('id');

                            // Skip if already initialized
                            if (initializedTables[tableId]) {
                                console.log('Ninja Tables: Table already initialized: ' + tableId);
                                return;
                            }

                            try {
                                // Make sure table is visible for initialization
                                $table.css({
                                    'width': '100%',
                                    'min-width': '400px',
                                    'table-layout': 'fixed',
                                    'visibility': 'visible',
                                    'display': 'table'
                                });

                                // Find parent container and remove loading class
                                var $parent = $table.closest('.footable_parent');
                                if ($parent.length) {
                                    $parent.removeClass('loading_ninja_table');
                                }

                                // Initialize with proper config
                                var config = {
                                    toggleColumn: 'first',
                                    breakpoints: {
                                        phone: 480,
                                        tablet: 767
                                    },
                                    filtering: {
                                        enabled: true,
                                        placeholder: 'Search',
                                        delay: 100
                                    },
                                    paging: {
                                        enabled: true,
                                        size: 10,
                                        limit: 10,
                                        countFormat: '{CP} of {TP}'
                                    },
                                    sorting: {
                                        enabled: true
                                    }
                                };

                                // Initialize FooTable
                                $table.footable(config);

                                // Mark as initialized
                                initializedTables[tableId] = true;

                                console.log('Ninja Tables: Successfully initialized table ' + tableId);
                            } catch(e) {
                                console.error('Ninja Tables: Error initializing footable', e);
                            }
                        });
                    }

                    // Reset flag when done
                    initializationInProgress = false;
                };

                // Only initialize once when the initial DOM is ready
                $(document).ready(function() {
                    setTimeout(function() {
                        window.initNinjaTableFootable();
                    }, 1000);
                });

                // For Gutenberg block selection, use a more efficient approach
                if (wp && wp.data && wp.data.subscribe) {
                    var blockChangeDebounce = null;
                    var lastSelectedBlockClientId = null;

                    wp.data.subscribe(function() {
                        var selectedBlock = wp.data.select('core/block-editor')
                            ? wp.data.select('core/block-editor').getSelectedBlock()
                            : wp.data.select('core/editor')?.getSelectedBlock();

                        if (selectedBlock &&
                            selectedBlock.name === 'ninja-tables/table-block' &&
                            selectedBlock.clientId !== lastSelectedBlockClientId) {

                            lastSelectedBlockClientId = selectedBlock.clientId;

                            // Debounce the initialization to prevent multiple calls
                            clearTimeout(blockChangeDebounce);
                            blockChangeDebounce = setTimeout(function() {
                                window.initNinjaTableFootable();
                            }, 500);
                        }
                    });
                }
            })(jQuery);
        </script>
        <?php
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
