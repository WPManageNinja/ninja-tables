<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\Post;
use NinjaTables\Database\Migrations\NinjaTableItemsMigrator;
use NinjaTables\Framework\Http\Request\Request;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;

class SettingsController extends Controller
{
    private $cptName = 'ninja-table';

    public function getTableSettings(Request $request, $id)
    {
        $tableID = intval($id);
        $table   = get_post($tableID);

        if ( ! $table || $table->post_type != $this->cptName) {
            $this->sendError(array(
                'message' => __('No Table Found', 'ninja-tables'),
                'route'   => 'home'
            ), 423);
        }
        $provider = ninja_table_get_data_provider($table->ID);

        $table = $this->app->applyFilters('ninja_tables_get_table_' . $provider, $table);

        $table->table_caption = get_post_meta($tableID, '_ninja_table_caption', true);

        $table->custom_css = get_post_meta($tableID, '_ninja_tables_custom_css', true);

        NinjaTableItemsMigrator::checkDBMigrations();

        $this->json(array(
            'preview_url' => site_url('?ninjatable_preview=' . $tableID),
            'columns'     => ninja_table_get_table_columns($tableID, 'admin'),
            'settings'    => ninja_table_get_table_settings($tableID, 'admin'),
            'table'       => $table,
        ), 200);
    }

    public function updateTableSettings(Request $request, $id)
    {
        $tableId         = intval($id);
        $rawColumns      = [];
        $tablePreference = '';

        if (Arr::get($request->all(), 'columns', [])) {
            $rawColumns = $this->app->applyFilters(
                'ninja_tables_before_update_settings',
                $this->sanitizeColumnData($request->columns, $id)
            );
        }

        if (Arr::get($request->all(), 'table_settings', [])) {
            $tablePreference = ninja_tables_sanitize_array($request->table_settings);
        }

        $data = Post::updatedSettings($tableId, $rawColumns, $tablePreference);

        $this->json($data, 200);
    }

    private function sanitizeColumnData($array, $tableId)
    {
        $tableSettings     = get_post_meta($tableId, '_ninja_table_settings', true);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sanitizeColumnData($value, $tableId);
            } else {
                if ($key === 'transformed_value') {
                    $hasFormulaSupport = Arr::get($tableSettings, 'formula_support', 'no') === 'yes';
                    $array[$key] = $this->sanitize_transform_value($value, $hasFormulaSupport);
                } else {
                    $array[$key] = wp_kses((string)$value, ninja_tables_allowed_html_tags());
                }
            }
        }

        return $array;
    }

    private function sanitize_transform_value($input, $allowFormulas = false)
    {
        if ( ! is_string($input) || empty($input)) {
            return '';
        }

        $input = wp_unslash($input);
        $input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');

        $isFormula = ($input[0] ?? '') === '=';

        if ($isFormula && $allowFormulas) {
            return $this->sanitize_excel_formula($input);
        }

        return wp_kses($input, ninja_tables_allowed_html_tags());
    }

    private function sanitize_excel_formula($input)
    {
        $dangerous = [
            'javascript:', 'vbscript:', 'data:', 'file:', 'ftp:',
            '<script', '<iframe', '<object', '<embed', '<form',
            'onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur',
            'eval(', 'alert(', 'setTimeout', 'setInterval', 'Function(',
            'document.', 'window.', 'location.', 'navigator.',
            'exec(', 'system(', 'shell_exec', 'passthru(',
            '<?php', '<?=', 'constructor', 'prototype'
        ];

        $input = str_ireplace($dangerous, '', $input);
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        return trim(substr($input, 0, 2000));
    }

    public function getButtonSettings(Request $request, $id)
    {
        $tableId             = absint($id);
        $tableButtonDefaults = array(
            'csv'              => array(
                'status'     => 'no',
                'label'      => 'CSV',
                'all_rows'   => 'no',
                'bg_color'   => 'rgb(0,0,0)',
                'text_color' => 'rgb(255,255,255)'
            ),
            'print'            => array(
                'status'           => 'no',
                'label'            => 'Print',
                'all_rows'         => 'no',
                'bg_color'         => 'rgb(0,0,0)',
                'text_color'       => 'rgb(255,255,255)',
                'header_each_page' => 'no',
                'footer_each_page' => 'no',
            ),
            'button_position'  => 'after_search_box',
            'button_alignment' => 'ninja_buttons_right'
        );

        $tableButtons = get_post_meta($tableId, '_ninja_custom_table_buttons', true);
        if ( ! $tableButtons) {
            $tableButtons = array();
        }

        $tableButtons = array_replace_recursive($tableButtonDefaults, $tableButtons);

        return $this->sendSuccess([
            'data' => [
                'button_settings' => $tableButtons
            ]
        ]);
    }

    public function updateButtonSettings(Request $request, $id)
    {
        ninja_tables_allowed_css_properties();
        $tableId        = absint($id);
        $buttonSettings = ninja_tables_sanitize_array(wp_unslash(Arr::get($request->all(), 'button_settings', [])));
        update_post_meta($tableId, '_ninja_custom_table_buttons', $buttonSettings);

        return $this->sendSuccess(array(
            'data' => array(
                'message' => __('Settings successfully updated', 'ninja-tables')
            )
        ), 200);
    }

    public function saveCustomCSSJS(Request $request, $id)
    {
        $tableId = intval($id);
        $css     = isset($_REQUEST['custom_css']) ? sanitize_textarea_field(wp_unslash($_REQUEST['custom_css'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $css     = wp_strip_all_tags($css);
        update_post_meta($tableId, '_ninja_tables_custom_css', $css);

        $this->app->doAction('ninja_tables_custom_code_before_save', $request->all());

        return $this->sendSuccess([
            'data' => [
                'message' => 'Code successfully saved'
            ]
        ], 200);
    }

    public function getCustomCSSJS(Request $request, $id)
    {
        $tableId = intval($id);

        return $this->sendSuccess([
            'data' => [
                'custom_css' => get_post_meta($tableId, '_ninja_tables_custom_css', true),
                'custom_js'  => get_post_meta($tableId, '_ninja_tables_custom_js', true)
            ]
        ], 200);
    }
}
