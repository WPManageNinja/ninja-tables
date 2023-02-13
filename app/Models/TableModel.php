<?php

namespace NinjaTables\App\Models;

use NinjaTables\Framework\Foundation\App;

class TableModel extends Model
{
    private static $cptName = 'ninja-table';

    public static function getTables($perPage, $currentPage, $tables)
    {
        foreach ($tables as $table) {
            $provider = get_post_meta($table->ID, '_ninja_tables_data_provider', true);
            if ($provider === 'drag_and_drop') {
                $table->preview_url = site_url('?ninjatable_builder_preview=' . $table->ID);
            } else {
                $table->preview_url = site_url('?ninjatable_preview=' . $table->ID);
            }
            $dataSourceType        = ninja_table_get_data_provider($table->ID);
            $table->dataSourceType = $dataSourceType;
            if ($dataSourceType == 'fluent-form') {
                $fluentFormFormId = get_post_meta($table->ID, '_ninja_tables_data_provider_ff_form_id', true);
                if ($fluentFormFormId) {
                    $table->fluentfrom_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $fluentFormFormId);
                }
            } elseif ($dataSourceType == 'csv' || $dataSourceType == 'google-csv') {
                $table->remoteURL = get_post_meta($table->ID, '_ninja_tables_data_provider_url', true);
            }
        }

        $total    = wp_count_posts('ninja-table');
        $total    = intval($total->publish);
        $lastPage = ceil($total / $perPage);

        return [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $currentPage,
            'last_page'    => ($lastPage) ? $lastPage : 1,
            'data'         => $tables,
        ];
    }

    protected function destroyTable($tableId)
    {
        wp_delete_post($tableId, true);
        // Delete the post metas
        delete_post_meta($tableId, '_ninja_table_columns');
        delete_post_meta($tableId, '_ninja_table_settings');
        delete_post_meta($tableId, '_ninja_table_cache_object');
    }

    protected function makeDuplicate($tableId, $newTableId)
    {
        $tableColumns  = get_post_meta($tableId, '_ninja_table_columns', true);
        $tableSettings = get_post_meta($tableId, '_ninja_table_settings', true);

        update_post_meta($newTableId, '_ninja_table_columns', $tableColumns);
        update_post_meta($newTableId, '_ninja_table_settings', $tableSettings);
    }
}