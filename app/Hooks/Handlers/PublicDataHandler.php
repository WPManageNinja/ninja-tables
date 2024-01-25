<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;
use NinjaTables\App\Models\NinjaTableItem;
use NinjaTables\App\Modules\DataProviders\NinjaFooTable;
use NinjaTables\Framework\Support\Arr;

class PublicDataHandler
{
    public function runFooTable($tableArray)
    {
        NinjaFooTable::run($tableArray);
    }

    public function registerTableRenderFunctions()
    {
        // register the shortcode
        $shortCodeBase = apply_filters('ninja_tables_shortcode_base', 'ninja_tables');
        add_shortcode($shortCodeBase, [$this, 'renderTableShortcode']);

        add_shortcode('ninja_table_info', [$this, 'tableInfoShortcode']);
        add_shortcode('ninja_table_cell', [$this, 'tableCellShortcode']);
        add_shortcode('ninja_table_builder', [$this, 'ninjaTableBuilderShortCode']);
        add_action('ninja_tables_will_render_table', [$this, 'preRenderTableAssets']);
    }

    public function renderTableShortcode($atts, $content = '')
    {
        if (is_preview() && is_user_logged_in()) {
            $role = ninja_table_admin_role();
            if (!current_user_can($role)) {
                return '<div class="ninja-tables-preview-message">' . __('Please publish the page/post to see the table', 'ninja-tables') . '</div>';
            }
        }

        $shortCodeDefaults = [
            'id'               => false,
            'filter'           => false,
            'use_parent_width' => false,
            'info'             => ''
        ];

        $shortCodeDefaults = apply_filters('ninja_tables_shortcode_defaults', $shortCodeDefaults);
        $shortCodeData = shortcode_atts($shortCodeDefaults, $atts);
        $shortCodeData = apply_filters('ninja_tables_shortcode_data', $shortCodeData);

        $tableArray = $this->getTableArray($shortCodeData, $content);

        if (Arr::get($tableArray, 'settings.formula_support') == 'yes') {
            do_action('ninja_tables_require_formulajs', $tableArray);
        }

        $tableArray = apply_filters('ninja_table_js_config', $tableArray, $shortCodeData['filter']);

        if (defined('LSCWP_V')) {
            do_action('litespeed_tag_add', 'ninja_tables_light_speed_clear_cache');
        }

        ob_start();
        do_action('ninja_tables-render-table-' . Arr::get($tableArray, 'settings.library'), $tableArray);

        return ob_get_clean();
    }

    public function tableInfoShortcode($atts)
    {

        if (is_preview() && is_user_logged_in()) {
            $role = ninja_table_admin_role();
            if (!current_user_can($role)) {
                return '<div class="ninja-tables-preview-message">' . __('Please publish the page/post to see the table', 'ninja-tables') . '</div>';
            }
        }

        $shortCodeDefaults = [
            'id'    => false,
            'field' => ''
        ];
        $shortCodeData = shortcode_atts($shortCodeDefaults, $atts);
        extract($shortCodeData);
        if (!$id || !$field) {
            return;
        }

        $id = absint($id);
        $table = get_post($id);
        if (!$table) {
            return;
        }

        $validFields = [
            'title',
            'description',
            'total_rows',
            'last_modified',
            'last_editor'
        ];
        if (!in_array($field, $validFields)) {
            return;
        }

        switch ($field) {
            case 'title':
                return $table->post_title;
                break;
            case 'description':
                return $table->post_content;
                break;
            case 'last_modified':
                $getLastModifiedTime = get_post_meta($table->ID, '_last_edited_time', true);
                $lastEditedTime = apply_filters('ninja_tables_last_edited_time', $getLastModifiedTime, $table->ID);
                if (!$lastEditedTime) {
                    $lastEditedTime = $table->post_modified;
                }

                return $lastEditedTime;
                break;
            case 'last_editor':
                $lastEditorId = get_post_meta($table->ID, '_last_edited_by', true);
                if (!$lastEditorId) {
                    $lastEditorId = $table->post_author;
                }
                if ($lastEditorId) {
                    $user = get_userdata($lastEditorId);
                    if ($user) {
                        return $user->display_name;
                    }
                }
                break;
            case 'total_rows':
                $total = NinjaTableItem::where('table_id', $table->ID)
                    ->count();
                if ($total) {
                    return $total;
                }

                break;
            default:
                return '';
                break;
        }

        return '';
    }

    public function tableCellShortcode($atts)
    {
        if (is_preview() && is_user_logged_in()) {
            $role = ninja_table_admin_role();
            if (!current_user_can($role)) {
                return '<div class="ninja-tables-preview-message">' . __('Please publish the page/post to see the table', 'ninja-tables') . '</div>';
            }
        }

        $shortCodeDefaults = [
            'id'     => 0,
            'row'    => 0,
            'column' => '',
            'row_id' => false
        ];
        $shortCodeData = shortcode_atts($shortCodeDefaults, $atts);

        extract($shortCodeData);

        if (!$id || (!$row && !$row_id) || !$column) {
            return '';
        }

        $id = absint($id);
        $tableSettings = ninja_table_get_table_settings($id, 'public');

        if ($row_id) {
            $rowData = NinjaTableItem::where('table_id', $id)
                ->where('id', $row_id)
                ->first();
            if (!$rowData) {
                return '';
            }
            $data = json_decode($rowData->value, true);
            if (isset($data[$column])) {
                $value = $data[$column];
                if (is_array($value)) {
                    $value = $this->processCellInfoArray($value, $column, $id);
                }

                return do_shortcode($value);
            } else {
                $dataArray = array_values($data);

                if (isset($dataArray[$column])) {
                    $value = $data[$column];
                    if (is_array($value)) {
                        $value = $this->processCellInfoArray($value, $column, $id);
                    }

                    return do_shortcode($value);
                }
            }

            return '';
        }

        $tableColumns = ninja_table_get_table_columns($id, 'public');
        $data = ninjaTablesGetTablesDataByID($id, $tableColumns, $tableSettings['default_sorting'], false, 1, $row - 1);

        if ($data) {
            $data = $data[0];
        } else {
            return '';
        }

        $content = '';
        if (isset($data[$column])) {
            $content = $data[$column];
        } else {
            $arrayValues = array_values($data);
            if (isset($arrayValues[$column])) {
                $content = $arrayValues[$column];
            }
        }
        if (is_array($content)) {
            $content = $this->processCellInfoArray($content, $column, $id);
        }

        return do_shortcode($content);
    }

    /**
     * Render table specific assets to the header.
     *
     * @param $tableId
     */
    public function preRenderTableAssets($tableId)
    {
        $tableId = intval($tableId);

        $atts = [
            'id'               => $tableId,
            'filter'           => false,
            'use_parent_width' => false,
            'info'             => ''
        ];

        $tableArray = $this->getTableArray($atts, '');

        // No table array means the shortcode ID is invalid.
        if (!$tableArray) return;

        $columnContentCss = NinjaFooTable::getColumnsCss($tableArray['table_id'], $tableArray['columns']);

        $css = NinjaFooTable::generateCustomColorCSS($tableArray, $columnContentCss);

        // Keep a flag that this css has been loaded so that we won't duplicate assets.
        NinjaFooTable::$tableCssStatuses[$tableId] = true;

        add_action('wp_head', function () use ($css, $tableId) {
            ?>
            <style id='ninja_table_custom_css_<?php echo esc_attr($tableId); ?>' type='text/css'>
                <?php echo ninjaTablesEscCss($css); ?>
            </style>
            <?php
        }, 99);
    }

    public function ninjaTableBuilderShortCode($atts = [], $content = null, $tag = '')
    {
        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array)$atts, CASE_LOWER);
        // override default attributes with user attributes
        $short_code_atts = shortcode_atts([
            'id' => null,
        ], $atts, $tag);
        $id = $short_code_atts['id'];
        $table_id = absint($id);
        if (!$table_id) {
            return;
        }
        $table = get_post($table_id);

        if (!$table || $table->post_type != 'ninja-table') {
            return;
        }
        $ninja_table_builder_html = get_post_meta($table->ID, '_ninja_table_builder_table_html', true);
        $ninja_table_builder_table_data = get_post_meta($table->ID, '_ninja_table_builder_table_data', true);
        $ninja_table_builder_setting = get_post_meta($table->ID, '_ninja_table_builder_table_settings', true);
        $ninja_table_builder_responsive = get_post_meta($table->ID, '_ninja_table_builder_table_responsive', true);
        $html = do_shortcode($ninja_table_builder_html);
        $this->enqueueNinjaTableBuilderScript();

        do_action('ninja_table_builder_before_render', $table_id);

        $app = App::getInstance();

        return $app->view->make('public/drag-and-drop-html', [
            'ninja_table_builder_html' => $html,
            'table_data'               => $ninja_table_builder_table_data,
            'setting'                  => $ninja_table_builder_setting,
            'responsive'               => $ninja_table_builder_responsive,
            'table_id'                 => $table_id
        ]);
    }

    public function enqueueNinjaTableScript()
    {
        global $post;

        if (is_a($post, 'WP_Post') && $ids = get_post_meta($post->ID, '_has_ninja_tables', true)) {
            $styleSrc = NINJA_TABLES_DIR_URL . "assets/css/ninjatables-public.css";
            if (is_rtl()) {
                $styleSrc = NINJA_TABLES_DIR_URL . "assets/css/ninjatables-public-rtl.css";
            }
            wp_enqueue_style(
                'footable_styles',
                $styleSrc,
                [],
                '',
                'all'
            );

            // We have the parsed table IDs from the shortcodes.
            // We should prerender the table assets here.
            if (is_array($ids)) {
                foreach ($ids as $tableId) {
                    do_action('ninja_tables_will_render_table', $tableId);
                }
            }
        }
    }

    public function getTableArray($shortCodeData, $content = '')
    {
        extract($shortCodeData);

        $table_id = $shortCodeData['id'];

        if (!$table_id) {
            return;
        }

        $table = get_post($table_id);

        if (!$table || $table->post_type != 'ninja-table') {
            return;
        }

        $tableSettings = ninja_table_get_table_settings($table_id, 'public');

        $tableSettings = apply_filters(
            'ninja_tables_rendering_table_settings', $tableSettings, $shortCodeData, $table
        );

        $tableColumns = ninja_table_get_table_columns($table_id, 'public');

        if (!$tableSettings || !$tableColumns) {
            return;
        }

        $tableSettings['use_parent_width'] = $use_parent_width;

        if (isset($tableSettings['columns_only']) && is_array($tableSettings['columns_only'])) {
            $showingColumns = $tableSettings['columns_only'];
            $formattedColumns = [];
            foreach ($tableColumns as $columnIndex => $table_column) {
                if (isset($showingColumns[$table_column['key']])) {
                    $formattedColumns[] = $table_column;
                }
            }
            $tableColumns = $formattedColumns;
        }

        return [
            'table_id'      => $table_id,
            'columns'       => $tableColumns,
            'settings'      => $tableSettings,
            'table'         => $table,
            'content'       => $content,
            'shortCodeData' => $shortCodeData
        ];
    }

    public function enqueueNinjaTableBuilderScript()
    {
        $styleSrc = NINJA_TABLES_DIR_URL . "assets/js/ninja-table-builder-public.js";
        wp_enqueue_script(
            'ninja_table_builder_js',
            $styleSrc,
            ['jquery'],
            '',
            'all'
        );
        $styleSrc = NINJA_TABLES_DIR_URL . "assets/css/ninja-table-builder-public.css";
        wp_enqueue_style(
            'ninja_table_builder_style',
            $styleSrc,
            [],
            '',
            'all'
        );
    }

    public function renderTableInsideTable($table, $tableVars)
    {
        NinjaFooTable::getTableHTML($table, $tableVars);
    }

    private function processCellInfoArray($value, $column, $tableId)
    {
        $tableColumns = ninja_table_get_table_columns($tableId);
        $targetColumn = [];
        foreach ($tableColumns as $tableColumn) {
            if ($tableColumn['key'] == $column) {
                $targetColumn = $tableColumn;
            }
        }

        if ($tableColumn['data_type'] == 'image') {
            if (function_exists('nt_parse_image_column')) {
                return nt_parse_image_column($value, $targetColumn);
            }
        }

        return implode(', ', $value);
    }
}
