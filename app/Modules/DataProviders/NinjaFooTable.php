<?php

namespace NinjaTables\App\Modules\DataProviders;

use NinjaTables\App\App;
use NinjaTables\App\Models\NinjaTableItem;
use NinjaTables\Framework\Support\Arr;
use NinjaTablesPro\App\Modules\DataProviders\CsvProvider;
use NinjaTablesPro\App\Modules\DataProviders\WoocommercePostsProvider;

class NinjaFooTable
{
    public static $version = NINJA_TABLES_VERSION;

    public static $tableInstances = [];
    /**
     * Table specfic css prerender status.
     *
     * @var array
     */
    public static $tableCssStatuses = [];

    public static function run($tableArray)
    {
        global $ninja_table_current_rendering_table;
        $tableInstance            = 'ninja_table_instance_' . count(static::$tableInstances);
        static::$tableInstances[] = $tableInstance;

        $tableArray['uniqueID'] = 'ninja_table_unique_id_' . rand() . '_' . $tableArray['table_id'];

        $ninja_table_current_rendering_table = $tableArray;

        static::enqueuePublicCss();

        if ( ! Arr::get($tableArray, 'settings.table_color_type')) {
            if (Arr::get($tableArray, 'settings.table_color') == 'ninja_table_custom_color') {
                $tableArray['settings']['table_color_type'] = 'custom_color';
            } else {
                $tableArray['settings']['table_color_type'] = 'pre_defined_color';
            }
        }

        $tableArray['table_instance_name'] = $tableInstance;
        $table_provider                    = ninja_table_get_data_provider($tableArray['table_id']);
        $tableArray['provider']            = $table_provider;
        do_action('ninja_rendering_table_' . $table_provider, $tableArray);
        self::enqueue_assets();
        self::render($tableArray);
    }

    private static function enqueue_assets()
    {
        $app = App::getInstance();

        $assets = $app['url.assets'];

        wp_enqueue_script('footable',
            $assets . "libs/footable/js/footable.min.js",
            array('jquery'), '3.1.5', true
        );

        wp_enqueue_script('footable_init',
            $assets . "js/ninja-tables-footable.js",
            array('footable'), self::$version, true
        );

        $localizeData = array(
            'ajax_url'                 => admin_url('admin-ajax.php'),
            'tables'                   => array(),
            'ninja_version'            => NINJA_TABLES_VERSION,
            'i18n'                     => array(
                'search_in'      => __('Search in', 'ninja-tables'),
                'search'         => __('Search', 'ninja-tables'),
                'empty_text'     => __('No Result Found', 'ninja-tables'),
                'clear_all'      => __('Clear All', 'ninja-tables'),
                'caption_format' => __('Selected', 'ninja-tables'),
            ),
            'ninja_table_public_nonce' => wp_create_nonce('ninja_table_public_nonce'),
            'site_url'                 => site_url(),
        );

        if (defined('NINJAPROPLUGIN_VERSION')) {
            $localizeData['pro_version'] = NINJAPROPLUGIN_VERSION;
        }

        wp_localize_script('footable_init', 'ninja_footables', $localizeData);
    }

    /**
     * Set the table header colors.
     *
     * @param array $tableArray
     *
     * @param string $extra_css
     *
     * @return void
     */
    private static function addCustomColorCSS($tableArray, $extra_css = '')
    {
        $css = self::generateCustomColorCSS($tableArray, $extra_css);
        if ($css) {
            $tableId = $tableArray['table_id'];
            add_action('ninja_tables_after_table_print', function () use ($css, $tableId) {
                ?>
                <style type="text/css" id='ninja_table_custom_css_<?php echo esc_attr($tableId); ?>'>
                    <?php echo ninjaTablesEscCss($css); ?>
                </style>
                <?php
            });
        }
    }

    /**
     * Generate custom css for the table.
     *
     * @param array $tableArray
     * @param string $extra_css
     *
     * @return mixed
     */
    public static function generateCustomColorCSS($tableArray, $extra_css = '')
    {
        $tableId       = intval($tableArray['table_id']);
        $cellStyles    = array();
        $tableProvider = ninja_table_get_data_provider($tableId);
        if ($tableProvider == 'default' && get_option('_ninja_tables_settings_migration')) {
            ob_start();
            $cellStyles = NinjaTableItem::select(array('id', 'settings'))
                                        ->where('table_id', $tableId)
                                        ->whereNotNull('settings')
                                        ->get();
            $maybeError = ob_get_clean();
        }

        $css_prefix   = '#footable_' . $tableId;
        $hasStackable = false;
        if (Arr::get($tableArray, 'settings.stackable') == 'yes') {
            $hasStackable = true;
            $stackPrefix  = '#footable_' . $tableId . ' .footable-details';
        }

        $customColumnCss = '';
        if (defined('NINJATABLESPRO')) {
            $columns = Arr::get($tableArray, 'columns');
            foreach ($columns as $index => $column) {
                $bgColor   = Arr::get($column, 'background_color');
                $textColor = Arr::get($column, 'text_color');
                if ($bgColor || $textColor) {
                    if ($bgColor && $textColor) {
                        $customColumnCss .= $css_prefix . ' thead tr th.ninja_column_' . $index . ',' . $css_prefix . ' tbody tr td.ninja_column_' . $index . '{ background-color: ' . $bgColor . '; color: ' . $textColor . '; }';
                    } elseif ($bgColor) {
                        $customColumnCss .= $css_prefix . ' thead tr th.ninja_column_' . $index . ',' . $css_prefix . ' tbody tr td.ninja_column_' . $index . '{ background-color: ' . $bgColor . '; }';
                    } elseif ($textColor) {
                        $customColumnCss .= $css_prefix . ' thead tr th.ninja_column_' . $index . ',' . $css_prefix . ' tbody tr td.ninja_column_' . $index . '{ color: ' . $textColor . '; }';
                    }
                }
            }
        }

        $colors     = false;
        $custom_css = str_replace('NT_ID', $tableId, get_post_meta($tableId, '_ninja_tables_custom_css', true));

        $fonts = array(
            'table_font_family' => Arr::get($tableArray, 'settings.table_font_family'),
            'table_font_size'   => Arr::get($tableArray, 'settings.table_font_size'),
        );
        if (Arr::get($tableArray, 'settings.table_color_type') == 'custom_color'
            && defined('NINJATABLESPRO')
        ) {
            $colorSettings = $tableArray['settings'];
            $colors        = array(
                'table_color_primary'   => Arr::get($colorSettings, 'table_color_primary'),
                'table_color_secondary' => Arr::get($colorSettings, 'table_color_secondary'),
                'table_color_border'    => Arr::get($colorSettings, 'table_color_border'),

                'table_color_primary_hover'   => Arr::get($colorSettings, 'table_color_primary_hover'),
                'table_color_secondary_hover' => Arr::get($colorSettings, 'table_color_secondary_hover'),
                'table_color_border_hover'    => Arr::get($colorSettings, 'table_color_border_hover'),

                'table_search_color_primary'   => Arr::get($colorSettings, 'table_search_color_primary'),
                'table_search_color_secondary' => Arr::get($colorSettings, 'table_search_color_secondary'),
                'table_search_color_border'    => Arr::get($colorSettings, 'table_search_color_border'),

                'table_header_color_primary'   => Arr::get($colorSettings, 'table_header_color_primary'),
                'table_color_header_secondary' => Arr::get($colorSettings, 'table_color_header_secondary'),
                'table_color_header_border'    => Arr::get($colorSettings, 'table_color_header_border'),

                'alternate_color_status' => Arr::get($colorSettings, 'alternate_color_status'),

                'table_alt_color_primary'   => Arr::get($colorSettings, 'table_alt_color_primary'),
                'table_alt_color_secondary' => Arr::get($colorSettings, 'table_alt_color_secondary'),
                'table_alt_color_hover'     => Arr::get($colorSettings, 'table_alt_color_hover'),

                'table_alt_2_color_primary'   => Arr::get($colorSettings, 'table_alt_2_color_primary'),
                'table_alt_2_color_secondary' => Arr::get($colorSettings, 'table_alt_2_color_secondary'),
                'table_alt_2_color_hover'     => Arr::get($colorSettings, 'table_alt_2_color_hover'),

                'table_footer_bg'     => Arr::get($colorSettings, 'table_footer_bg'),
                'table_footer_active' => Arr::get($colorSettings, 'table_footer_active'),
                'table_footer_border' => Arr::get($colorSettings, 'table_footer_border'),
            );
        }

        $custom_css .= $extra_css . $customColumnCss;

        if ( ! $fonts['table_font_size'] && ! $colors && ! $custom_css && ! $cellStyles) {
            return;
        }
        ob_start();
        include NINJA_TABLES_DIR_PATH . 'app/Views/public/ninja-footable-css.php';

        return ob_get_clean();
    }

    private static function render($tableArray)
    {
        extract($tableArray);
        if ( ! count($columns)) {
            return;
        }

        $renderType = Arr::get($settings, 'render_type', 'ajax_table');

        $formatted_columns = array();
        $sortingType       = Arr::get($settings, 'sorting_type', 'by_created_at');

        $globalSorting = (bool)Arr::get($settings, 'column_sorting', false);

        $customCss = array();

        foreach ($columns as $index => $column) {
            $columnType      = self::getColumnType($column);
            $cssColumnName   = 'ninja_column_' . $index;
            $columnClasses   = array($cssColumnName);
            $columnClasses[] = 'ninja_clmn_nm_' . $column['key'];
            if (isset($column['classes'])) {
                $userClasses   = explode(' ', $column['classes']);
                $columnClasses = array_unique(array_merge($columnClasses, $userClasses));
            }
            $customCss[$cssColumnName] = array();
            if ($columnWidth = Arr::get($column, 'width')) {
                $customCss[$cssColumnName]['width'] = $columnWidth . Arr::get($column, 'maxWidthUnit', 'px');
            }

            $columnTitle = $column['name'];
            if (Arr::get($column, 'enable_html_content') == 'true') {
                if ($columnContent = Arr::get($column, 'header_html_content')) {
                    $columnTitle = do_shortcode($columnContent);
                }
            }

            $formatted_column = array(
                'name'        => $column['key'],
                'key'         => $column['key'],
                'title'       => $columnTitle,
                'breakpoints' => $column['breakpoints'],
                'type'        => $columnType,
                'visible'     => ($column['breakpoints'] == 'hidden') ? false : true,
                'classes'     => $columnClasses,
                'filterable'  => (isset($column['unfilterable']) && $column['unfilterable'] == 'yes') ? false : true,
                'sortable'    => (isset($column['unsortable']) && $column['unsortable'] == 'yes') ? false : $globalSorting,
            );

            // We will remove it after few versions
            if (defined('NINJAPROPLUGIN_VERSION') && isset($column['transformed_value'])) {
                $formatted_column['transformed_value'] = $column['transformed_value'];
            }

            if ($columnType == 'date') {
                wp_enqueue_script(
                    'moment',
                    NINJA_TABLES_DIR_URL . "assets/libs/moment/moment.min.js",
                    [],
                    '2.29.4',
                    true
                );
                $formatted_column['formatString']   = $column['dateFormat'] ?: 'MM/DD/YYYY';
                $formatted_column['showTime']       = isset($column['showTime']) && $column['showTime'] === 'yes';
                $formatted_column['firstDayOfWeek'] = isset($column['firstDayOfWeek']) && $column['firstDayOfWeek'] ? $column['firstDayOfWeek'] : 0;

                if ($formatted_column['showTime'] && isset($column['timeFormat']) && $column['timeFormat']) {
                    $formatted_column['formatString'] .= ' ' . $column['timeFormat'];
                }
            }
            if ($sortingType == 'by_column' && $column['key'] == Arr::get($settings, 'sorting_column')) {
                $formatted_column['sorted']    = true;
                $formatted_column['direction'] = Arr::get($settings, 'sorting_column_by');
            }

            if ($columnType == 'numeric') {
                $formatted_column['thousandSeparator'] = isset($column['thousandSeparator'])
                    ? $column['thousandSeparator'] : ',';
                $formatted_column['decimalSeparator']  = isset($column['decimalSeparator'])
                    ? $column['decimalSeparator'] : '.';
            }

            if ($columnType == 'image') {
                $linkType = Arr::get($column, 'link_type');
                if ($linkType == 'image_light_box' || $linkType == 'iframe_ligtbox') {
                    $settings['load_lightbox'] = true;
                    if ($linkType == 'iframe_ligtbox') {
                        $settings['iframe_lightbox'] = true;
                    }
                }
            }

            if ($tableArray['provider'] == 'wp_woo' && Arr::get($column, 'image_permalink_type') == 'lightbox') {
                $settings['load_lightbox'] = true;
            };

            $formatted_columns[] = apply_filters(
                'ninja_table_column_attributes', $formatted_column, $column, $table_id, $tableArray
            );
        }

        if (Arr::get($settings, 'show_all')) {
            $pagingSettings = false;
        } else {
            $pagingSettings = Arr::get($settings, 'perPage', 20);
        }

        $enableSearch = Arr::get($settings, 'enable_search', false);

        $default_sorting = false;
        if ($sortingType == 'manual_sort') {
            $default_sorting = 'manual_sort';
        } elseif (isset($settings['default_sorting'])) {
            $default_sorting = $settings['default_sorting'];
        }

        $configSettings = array(
            'filtering'             => $enableSearch,
            'togglePosition'        => Arr::get($settings, 'togglePosition', 'first'),
            'paging'                => $pagingSettings,
            'pager'                 => ! ! Arr::get($settings, 'show_pager'),
            'page_sizes'            => explode(',', Arr::get($settings, 'paze_sizes', '10,20,50,100')),
            'sorting'               => true,
            'default_sorting'       => $default_sorting,
            'defualt_filter'        => isset($default_filter) ? $default_filter : false,
            'defualt_filter_column' => Arr::get($settings, 'filter_column'),
            'expandFirst'           => (isset($settings['expand_type']) && $settings['expand_type'] == 'expandFirst')
                ? true : false,
            'expandAll'             => (isset($settings['expand_type']) && $settings['expand_type'] == 'expandAll') ? true
                : false,
            'i18n'                  => array(
                'search_in'      => (isset($settings['search_in_text']))
                    ? sanitize_text_field($settings['search_in_text']) : __('Search in', 'ninja-tables'),
                'search'         => (isset($settings['search_placeholder']))
                    ? sanitize_text_field($settings['search_placeholder']) : __('Search', 'ninja-tables'),
                'no_result_text' => (isset($settings['no_result_text']))
                    ? sanitize_text_field($settings['no_result_text']) : __('No Result Found', 'ninja-tables'),
            ),
            'shouldNotCache'        => isset($settings['shouldNotCache']) ? $settings['shouldNotCache'] : false,
            'skip_rows'             => Arr::get($settings, 'skip_rows', 0),
            'limit_rows'            => Arr::get($settings, 'limit_rows', 0),
            'use_parent_width'      => Arr::get($settings, 'use_parent_width', false),
            'info'                  => Arr::get($tableArray, 'shortCodeData.info', ''),
            'enable_html_cache'     => Arr::get($settings, 'enable_html_cache'),
            'html_caching_minutes'  => Arr::get($settings, 'html_caching_minutes')
        );

        $settings['info'] = Arr::get($tableArray, 'shortCodeData.info', '');
        $table_classes    = self::getTableCssClass($settings);

        $tableHasColor = '';

        $configSettings['extra_css_class'] = '';
        if ((Arr::get($settings, 'table_color_type') == 'pre_defined_color'
             && Arr::get($settings, 'table_color')
             && Arr::get($settings, 'table_color') != 'ninja_no_color_table')
        ) {
            $tableHasColor                     = 'colored_table';
            $configSettings['extra_css_class'] = 'inverted';
        }
        if (Arr::get($settings, 'table_color_type') == 'custom_color') {
            $tableHasColor                     = 'colored_table';
            $table_classes                     .= ' ninja_custom_color';
            $configSettings['extra_css_class'] = 'inverted';
        }

        $table_classes .= ' ' . $configSettings['extra_css_class'];

        if ($pagingPosition = Arr::get($settings, 'pagination_position')) {
            $table_classes .= ' footable-paging-' . $pagingPosition;
        } else {
            $table_classes .= ' footable-paging-right';
        }

        if (isset($settings['hide_all_borders']) && $settings['hide_all_borders']) {
            $table_classes .= ' hide_all_borders';
        }

        if (isset($settings['hide_header_row']) && $settings['hide_header_row']) {
            $table_classes .= ' ninjatable_hide_header_row';
        }

        $isStackable = Arr::get($settings, 'stackable', 'no');
        $isStackable = $isStackable == 'yes';

        if ($isStackable && count(Arr::get($settings, 'stacks_devices', []))) {
            $stackDevices                   = Arr::get($settings, 'stacks_devices', array());
            $configSettings['stack_config'] = array(
                'stackable'      => $isStackable,
                'stacks_devices' => $stackDevices
            );
            $stackApearances                = Arr::get($settings, 'stacks_appearances', array());
            if (is_array($stackApearances) && $stackApearances) {
                $extraStackClasses = implode(' ', $stackApearances);
                $table_classes     .= ' ' . $extraStackClasses;
            }
        }

        if ( ! $enableSearch) {
            $table_classes .= ' ninja_table_search_disabled';
        }

        if (defined('NINJATABLESPRO')) {
            $table_classes .= ' ninja_table_pro';
            if (Arr::get($settings, 'hide_on_empty')) {
                $configSettings['hide_on_empty'] = true;
            }

            if (Arr::get($settings, 'paginate_to_top')) {
                $configSettings['paginate_to_top'] = true;
            }

            $configSettings['disable_sticky_on_mobile'] = Arr::get($settings, 'disable_sticky_on_mobile');
        }

        $advancedFilterSettings = get_post_meta($table_id, '_ninja_custom_filter_styling', true);
        $advancedFilters        = get_post_meta($table_id, '_ninja_table_custom_filters', true);
        if ($advancedFilterSettings && $advancedFilters) {
            $defaultStyling         = array(
                'filter_display_type' => 'inline',
                'filter_columns'      => 'columns_2',
                'filter_column_label' => 'new_line'
            );
            $advancedFilterSettings = wp_parse_args($advancedFilterSettings, $defaultStyling);
            if ($advancedFilterSettings['filter_display_type'] == 'inline') {
                $table_classes .= ' ninja_table_afd_inline';
            } else {
                $table_classes .= ' ninja_table_afd_' . $advancedFilterSettings['filter_display_type'];
                $table_classes .= ' ninja_table_afcs_' . $advancedFilterSettings['filter_columns'];
                $table_classes .= ' ninja_table_afcl_' . $advancedFilterSettings['filter_column_label'];
            }
            $table_classes .= ' ninja_table_has_custom_filter';
        } elseif ($configSettings['defualt_filter']) {
            $table_classes .= ' ninja_has_filter';
        }

        $configSettings['has_formula'] = Arr::get($settings, 'formula_support', 'no');

        $tableCaption = get_post_meta($table_id, '_ninja_table_caption', true);

        $table_vars = array(
            'table_id'         => $table_id,
            'title'            => $table->post_title,
            'caption'          => $tableCaption,
            'columns'          => $formatted_columns,
            'original_columns' => $columns,
            'settings'         => $configSettings,
            'render_type'      => $renderType,
            'custom_css'       => $customCss,
            'instance_name'    => $table_instance_name,
            'table_version'    => NINJA_TABLES_VERSION,
            'provider'         => $tableArray['provider'],
            'uniqueID'         => $uniqueID
        );

        $table_vars = apply_filters('ninja_table_rendering_table_vars', $table_vars, $table_id, $tableArray);

        if ($tableArray['provider'] == 'wp_woo') {
            $table_vars['wc_ajax_url'] = add_query_arg(array(
                'wc-ajax'     => 'add_to_cart',
                'ninja_table' => $tableArray['table_id']
            ), home_url('/', 'relative'));

            add_action('wp_footer', function () {
                $cartItems = WC()->cart->get_cart();
                ?>
                <script type="text/javascript">
                    window['ninjaTableCartItems'] = <?php echo json_encode($cartItems); ?>;
                </script>
                <?php
            });
        }

        if ($renderType == 'ajax_table') {

            if ($tableArray['provider'] === 'fluent-form') {
                $ff        = new FluentFormProvider;
                $rows      = $ff->data([], $table_id, false);
                $totalSize = count($rows);
            } elseif (($tableArray['provider'] === 'google-csv' || $tableArray['provider'] === 'csv') && defined('NINJATABLESPRO')) {
                $gc        = new CsvProvider;
                $rows      = $gc->data([], $table_id, false);
                $totalSize = count($rows);
            } elseif ($tableArray['provider'] == 'wp_woo') {
                $woo       = new WoocommercePostsProvider;
                $rows      = $woo->data([], $table_id, false);
                $totalSize = count($rows);
            } else {
                $totalSizeQuery = NinjaTableItem::where('table_id', $table_id);
                $totalSizeQuery = apply_filters('ninja_tables_total_size_query', $totalSizeQuery, $table_vars);
                $totalSize      = $totalSizeQuery->count();
            }

            $perChunk = ninjaTablePerChunk($table_id);

            if ($totalSize > $perChunk) {
                $table_vars['chunks'] = ceil($totalSize / $perChunk) - 1;
            }
        }

        $table_vars['init_config'] = self::getNinjaTableConfig($table_vars);

        self::addInlineVars($table_vars, $table_id, $table_instance_name);
        $foo_table_attributes = self::getFootableAtrributes($table_vars);

        // We have to check if these css already rendered
        if ( ! isset(static::$tableCssStatuses[$tableArray['table_id']])) {
            $columnContentCss = static::getColumnsCss($tableArray['table_id'], $columns);

            static::addCustomColorCSS($tableArray, $columnContentCss);
        }

        do_action('ninja_table_before_render_table_source', $table, $table_vars, $tableArray);
        include NINJA_TABLES_DIR_PATH . 'app/Views/public/ninja-footable.php';
    }

    /**
     * Generate column specific custom css.
     *
     * @param $tableId
     * @param $columns
     *
     * @return string
     */
    public static function getColumnsCss($tableId, $columns)
    {
        $columnContentCss = '';

        foreach ($columns as $index => $column) {
            if ($contentAlign = Arr::get($column, 'contentAlign')) {
                $columnContentCss .= '#footable_' . $tableId . ' td.ninja_column_' . $index
                                     . ' { text-align: ' . $contentAlign . '; }';
            }

            if ($textAlign = Arr::get($column, 'textAlign')) {
                $columnContentCss .= '#footable_' . $tableId . ' th.ninja_column_' . $index
                                     . ' { text-align: ' . $textAlign . '; }';
            }
        }

        return $columnContentCss;
    }

    public static function getTableHTML($table, $table_vars)
    {
        if ($table_vars['render_type'] == 'ajax_table') {
            return;
        }
        if ($table_vars['render_type'] == 'legacy_table') {
            self::generateLegacyTableHTML($table, $table_vars);

            return;
        }
    }

    private static function generateLegacyTableHTML($table, $table_vars)
    {
        $tableId = $table->ID;

        $limitRows    = Arr::get($table_vars, 'settings.limit_rows', false);
        $skipRows     = Arr::get($table_vars, 'settings.skip_rows', false);
        $tableColumns = $table_vars['columns'];
        $ownOnly      = false;

        if (Arr::get($table_vars, 'editing.own_data_only') == 'yes') {
            $ownOnly = true;
        }

        $isHtmlCacheEnabled = Arr::get($table_vars, 'settings.enable_html_cache', true) == 'yes' &&
                              Arr::get($table_vars, 'settings.shouldNotCache', true) != 'yes';

        if ( ! $ownOnly && $isHtmlCacheEnabled) {
            $cachedTableData = self::getTableCachedHTML($tableId, $table_vars);
            if ($cachedTableData) {
                ninjaTablesPrintSafeVar($cachedTableData);

                return;
            }
        }

        $formatted_data = ninjaTablesGetTablesDataByID(
            $tableId,
            $tableColumns,
            $table_vars['settings']['default_sorting'],
            false,
            $limitRows,
            $skipRows,
            $ownOnly
        );

        $formatted_data = apply_filters('ninja_tables_get_public_data', $formatted_data, $table->ID);

        $tableHtml = self::loadView('app/Views/public/table-inner-html', array(
            'table_columns' => $tableColumns,
            'table_rows'    => $formatted_data
        ));

        if ($isHtmlCacheEnabled) {
            update_post_meta($tableId, '__last_ninja_table_last_cached_time', time());
        }
        update_post_meta($tableId, '__ninja_cached_table_html', $tableHtml);
        ninjaTablesPrintSafeVar($tableHtml);

        return;
    }

    private static function getTableCachedHTML($tableId, $table_vars)
    {
        $lastCachedTime         = intval(get_post_meta($tableId, '__last_ninja_table_last_cached_time', true));
        $cacheValidationMinutes = floatval(Arr::get($table_vars, 'settings.html_caching_minutes', true));

        if (time() >= $lastCachedTime + ($cacheValidationMinutes * 60)) {
            return false;
        }
        // Get the cached data now
        $cachedTableHtml = get_post_meta($tableId, '__ninja_cached_table_html', true);

        if (strpos($cachedTableHtml, 'ninja_tobody_rendering_done')) {
            return $cachedTableHtml . '<!--ninja_cached_data-->';
        }

        return false;
    }

    private static function loadView($file, $data)
    {
        $file = NINJA_TABLES_DIR_PATH . $file . '.php';
        ob_start();
        extract($data);
        include $file;

        return ob_get_clean();
    }

    private static function getTableCssClass($settings)
    {

        $tableCassClasses = array(
            self::getTableClassByLib($settings['css_lib']),
            Arr::get($settings, 'extra_css_class', '')
        );

        if (Arr::get($settings, 'load_lightbox')) {
            $tableCassClasses[] = 'nt_has_lightbox';
            if (Arr::get($settings, 'iframe_lightbox')) {
                $tableCassClasses[] = 'nt_has_iframe_lightbox';
            }
            do_action('ninja_tables_load_lightbox', $settings);
        } elseif (in_array('nt_has_lightbox', $tableCassClasses)) {
            do_action('ninja_tables_load_lightbox', $settings);
        }

        if (Arr::get($settings, 'info')) {
            $tableCassClasses[] = 'ninja_has_count_format';
        }

        if (
            Arr::get($settings, 'table_color_type') == 'pre_defined_color'
            && Arr::get($settings, 'table_color') != 'ninja_no_color_table'
        ) {
            $tableCassClasses[] = Arr::get($settings, 'table_color');
        }

        if ($searchBarPosition = Arr::get($settings, 'search_position')) {
            $tableCassClasses[] = 'ninja_search_' . $searchBarPosition;
        }

        if (Arr::get($settings, 'hide_responsive_labels')) {
            $tableCassClasses[] = 'nt_hide_breakpoint_labels';
        }

        if (Arr::get($settings, 'nt_search_full_width')) {
            $tableCassClasses[] = 'nt_search_full_width';
        }

        $tableCassClasses[] = 'nt_type_' . Arr::get($settings, 'render_type');

        $definedClasses = Arr::get($settings, 'css_classes', array());
        $classArray     = array_merge($tableCassClasses, $definedClasses);
        $uniqueCssArray = array_unique($classArray);

        return implode(' ', $uniqueCssArray);
    }

    private static function getTableClassByLib($lib = 'bootstrap3')
    {
        switch ($lib) {
            case 'bootstrap3':
            case 'bootstrap4':
                return 'table';
            case 'semantic_ui':
                return 'ui table';
            default:
                return '';
        }
    }

    private static function addInlineVars($vars, $table_id, $table_instance_name)
    {
        add_action('wp_footer', function () use ($vars, $table_id, $table_instance_name) {
            ?>
            <script type="text/javascript">
                window['<?php echo esc_attr($table_instance_name);?>'] = <?php echo json_encode($vars, true); ?>
            </script>
            <?php
        });
    }

    public static function getColumnType($column)
    {
        $type          = (isset($column['data_type'])) ? $column['data_type'] : 'text';
        $acceptedTypes = array(
            'text',
            'number',
            'date',
            'html',
            'image'
        );
        if (in_array($type, $acceptedTypes)) {
            if ($type == 'number') {
                return 'numeric';
            }

            return $type;
        }

        return 'text';
    }

    private static function getFootableAtrributes($tableVars)
    {
        $tableID    = $tableVars['table_id'];
        $delay_time = apply_filters('ninja_table_search_time_delay', 1000, $tableID);
        $atts       = array(
            'data-footable_id'  => $tableID,
            'data-filter-delay' => $delay_time
        );

        if ($tableVars['title'] && ! $tableVars['caption']) {
            $atts['aria-label'] = $tableVars['title'];
        }

        $atts = apply_filters('ninja_table_attributes', $atts, $tableID);

        $atts_string = '';
        if ($atts) {
            foreach ($atts as $att_name => $att) {
                $atts_string .= $att_name . '="' . esc_attr($att) . '" ';
            }
        }

        return (string)$atts_string;
    }

    public static function getFormattedColumn($column, $index, $settings, $globalSorting, $sortingType)
    {
        $columnType    = self::getColumnType($column);
        $cssColumnName = 'ninja_column_' . $index;
        $columnClasses = array($cssColumnName);
        if (isset($column['classes'])) {
            $userClasses   = explode(' ', $column['classes']);
            $columnClasses = array_unique(array_merge($columnClasses, $userClasses));
        }
        $customCss[$cssColumnName] = array();
        if ($columnWidth = Arr::get($column, 'width')) {
            $customCss[$cssColumnName]['width'] = $columnWidth . 'px';
        }
        if ($textAlign = Arr::get($column, 'textAlign')) {
            $customCss[$cssColumnName]['textAlign'] = $textAlign;
        }
        $columnTitle = $column['name'];
        if (Arr::get($column, 'enable_html_content') == 'true') {
            if ($columnContent = Arr::get($column, 'header_html_content')) {
                $columnTitle = do_shortcode($columnContent);
            }
        }
        $formatted_column = array(
            'name'        => $column['key'],
            'key'         => $column['key'],
            'title'       => $columnTitle,
            'breakpoints' => $column['breakpoints'],
            'type'        => $columnType,
            'sortable'    => $globalSorting,
            'visible'     => ($column['breakpoints'] == 'hidden') ? false : true,
            'classes'     => $columnClasses,
            'filterable'  => (isset($column['unfilterable']) && $column['unfilterable'] == 'yes') ? false : true,
            'column'      => $column
        );
        if ($columnType == 'date') {
            wp_enqueue_script(
                'moment',
                NINJA_TABLES_DIR_URL . "assets/libs/moment/moment.min.js",
                [],
                '2.29.4',
                true
            );
            $formatted_column['formatString'] = $column['dateFormat'] ?: 'MM/DD/YYYY';
        }
        if ($sortingType == 'by_column' && $column['key'] == $settings['sorting_column']) {
            $formatted_column['sorted']    = true;
            $formatted_column['direction'] = $settings['sorting_column_by'];
        }

        return $formatted_column;
    }

    public static function getNinjaTableConfig($tableConfig)
    {

        $tableId = $tableConfig['table_id'];
        // Prepare Table Init Configuration
        $tableSettings = $tableConfig['settings'];
        $initConfig    = array(
            "toggleColumn"   => Arr::get($tableSettings, 'togglePosition'),
            "cascade"        => true,
            "useParentWidth" => ! ! Arr::get($tableSettings, 'use_parent_width'),
            "columns"        => Arr::get($tableConfig, 'columns'),
            "expandFirst"    => Arr::get($tableSettings, 'expandFirst'),
            "expandAll"      => Arr::get($tableSettings, 'expandAll'),
            'empty'          => Arr::get($tableSettings, 'i18n.no_result_text'),
            "sorting"        => array(
                'enabled' => ! ! Arr::get($tableSettings, 'sorting')
            )
        );

        if (Arr::get($tableConfig, 'render_type') !== 'legacy_table') {

            $rowRequestUrlParams = array(
                'action'                   => 'wp_ajax_ninja_tables_public_action',
                'table_id'                 => $tableId,
                'target_action'            => 'get-all-data',
                'default_sorting'          => Arr::get($tableSettings, 'default_sorting'),
                'skip_rows'                => Arr::get($tableSettings, 'skip_rows'),
                'limit_rows'               => Arr::get($tableSettings, 'limit_rows'),
                'ninja_table_public_nonce' => wp_create_nonce('ninja_table_public_nonce')
            );

            if (Arr::get($tableConfig, 'editing.check_editing') == 'yes' && Arr::get($tableConfig,
                    'editing.own_data_only') == 'yes') {
                $rowRequestUrlParams['own_only'] = 'yes';
            }


            $chucks = Arr::get($tableConfig, 'chunks', 0);
            if ($chucks > 0) {
                $rowRequestUrlParams['chunk_number'] = 0;
            }
            $initConfig['data_request_url'] = add_query_arg($rowRequestUrlParams, admin_url('admin-ajax.php'));

        }

        $enabledSearch = ! ! Arr::get($tableSettings, 'filtering');
        $defaultFilter = Arr::get($tableSettings, 'defualt_filter');

        if ($enabledSearch || $defaultFilter) {
            $enabledSearch = true;
        }

        $initConfig['filtering'] = array(
            "enabled"       => $enabledSearch,
            "delay"         => 1,
            "dropdownTitle" => Arr::get($tableSettings, 'i18n.search_in'),
            "placeholder"   => Arr::get($tableSettings, 'i18n.search'),
            "connectors"    => false,
            "ignoreCase"    => true
        );

        if ($defaultFilter) {
            if ($defaultFilter == "'0'") {
                $defaultFilter = "0";
            }
            $filterColumns = Arr::get($tableSettings, 'defualt_filter_column');
            $validColumns  = array();
            if ($filterColumns && count($filterColumns)) {
                $columns = $tableConfig['columns'];
                foreach ($columns as $column) {
                    $columnName = Arr::get($column, 'name');
                    if (in_array($columnName, $filterColumns)) {
                        $validColumns[] = $columnName;
                    }
                }
            }
            $initConfig['filtering']['filters'] = array(
                array(
                    "name"    => "ninja_table_default_filter",
                    "hidden"  => Arr::get($tableSettings, 'hide_default_filter') == 'yes',
                    "query"   => $defaultFilter,
                    "columns" => $validColumns
                )
            );
        }

        $pageSize = Arr::get($tableSettings, 'paging');


        $initConfig['paging'] = array(
            "enabled"     => ! ! $pageSize,
            "position"    => "right",
            "size"        => $pageSize,
            "container"   => "#footable_parent_" . $tableId . " .paging-ui-container",
            "countFormat" => Arr::get($tableSettings, 'info', ' ')
        );

        $config = apply_filters('ninja_tables_js_init_config', $initConfig, $tableConfig, $tableId);

        return apply_filters('ninja_tables_js_init_config_' . $tableId, $config, $tableConfig, $tableId);
    }

    /**
     * Enqueue main public css.
     */
    public static function enqueuePublicCss()
    {
        $styleSrc = NINJA_TABLES_DIR_URL . "assets/css/ninjatables-public.css";

        if (is_rtl()) {
            $styleSrc = NINJA_TABLES_DIR_URL . "assets/css/ninjatables-public-rtl.css";
        }

        wp_enqueue_style(
            'footable_styles',
            $styleSrc,
            array(),
            '',
            'all'
        );
    }
}
