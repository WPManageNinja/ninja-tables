<?php

namespace NinjaTables\App\Models;

use NinjaTables\Framework\Foundation\App;

class NinjaTable extends Model
{
    private static $cptName = 'ninja-table';
    protected $table = 'posts';

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

        $total    = wp_count_posts(self::$cptName);
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

    public static function saveTable($attributes, $postId = null)
    {
        if ( ! $postId) {
            $postId = wp_insert_post($attributes);
        } else {
            $attributes['ID'] = $postId;
            wp_update_post($attributes);
        }
        update_post_meta($postId, '_last_edited_by', get_current_user_id());
        update_post_meta($postId, '_last_edited_time', date('Y-m-d H:i:s'));

        return $postId;
    }

    public static function destroyTable($tableId)
    {
        wp_delete_post($tableId, true);
        // Delete the post metas
        delete_post_meta($tableId, '_ninja_table_columns');
        delete_post_meta($tableId, '_ninja_table_settings');
        delete_post_meta($tableId, '_ninja_table_cache_object');
    }

    public static function makeDuplicate($oldPostId, $newPostId)
    {
        global $wpdb;

        // Duplicate table settings.
        $postMetas = get_post_meta($oldPostId);

        foreach ($postMetas as $metaKey => $metaValue) {
            update_post_meta($newPostId, $metaKey, maybe_unserialize($metaValue[0]));
        }

        // Duplicate table rows.
        $itemsTable = $wpdb->prefix . ninja_tables_db_table_name();

        $sql = "INSERT INTO $itemsTable (`position`, `table_id`, `owner_id`, `settings`, `attribute`, `value`, `created_at`, `updated_at`)";
        $sql .= " SELECT `position`, $newPostId, `owner_id`, `settings`, `attribute`, `value`, `created_at`, `updated_at` FROM $itemsTable";
        $sql .= " WHERE `table_id` = $oldPostId";

        $wpdb->query($sql);
    }
}