<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Support\Arr;

class AjaxHandler
{
    public function registerAjaxRoutes()
    {
        $validRoutes = array(
            'get-all-data' => 'getAllData',
        );

        $requestedRoute = esc_attr($_REQUEST['target_action']);

        if (isset($validRoutes[$requestedRoute])) {
            $this->{$validRoutes[$requestedRoute]}();
        }
        wp_die();
    }

    public function getAllData()
    {
        $tableId = intval(Arr::get($_REQUEST, 'table_id'));
        do_action('ninja_table_doing_ajax_table_data', $tableId);
        $defaultSorting = sanitize_text_field(Arr::get($_REQUEST, 'default_sorting'));
        $tableSettings = ninja_table_get_table_settings($tableId, 'public');
        $is_ajax_table = true;
        if (Arr::get($tableSettings, 'render_type') == 'legacy_table') {
            $is_ajax_table = false;
        }
        $is_ajax_table = apply_filters('ninja_table_is_public_ajax_table', $is_ajax_table, $tableId);

        if (!$tableSettings || !$is_ajax_table) {
            wp_send_json_success([], 200);
        }

        $skip = Arr::get($_REQUEST, 'skip_rows', 0);
        $limit = Arr::get($_REQUEST, 'limit_rows', false);

        if (!$limit && !$skip && isset($_REQUEST['chunk_number'])) {
            $chunkNumber = Arr::get($_REQUEST, 'chunk_number', 0);
            $perChunk = ninjaTablePerChunk($tableId);
            $skip = $chunkNumber * $perChunk;
            $limit = $perChunk;
        }

        $ownOnly = false;
        if (isset($_REQUEST['own_only']) && sanitize_text_field($_REQUEST['own_only']) == 'yes') {
            $ownOnly = true;
        }

        $tableColumns = ninja_table_get_table_columns($tableId);
        $formatted_data = ninjaTablesGetTablesDataByID($tableId, $tableColumns, $defaultSorting, false, $limit, $skip, $ownOnly);

        $formatted_data = apply_filters('ninja_tables_get_public_data', $formatted_data, $tableId);

        $dataProvider = ninja_table_get_data_provider($tableId);
        if ($dataProvider == 'default') {
            $newStyledData = [];
            $counter = $skip;
            foreach ($formatted_data as $index => $datum) {
                $datum = array_map(function ($value) {
                    if (is_string($value)) {
                        return do_shortcode($value);
                    }

                    return $value;
                }, $datum);
                $newStyledData[] = [
                    'options' => [
                        'classes' => (isset($datum['___id___'])) ? 'ninja_table_row_' . $counter . ' nt_row_id_' . $datum['___id___'] : 'ninja_table_row_' . $counter,
                    ],
                    'value' => $datum
                ];
                $counter = $counter + 1;
            }
            $formatted_data = $newStyledData;
        }

        wp_send_json($formatted_data, 200);
        wp_die();
    }
}
