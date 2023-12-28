<?php

namespace NinjaTables\App\Models;

use NinjaTables\Framework\Foundation\App;

class Post extends Model
{
    private static $cptName = 'ninja-table';
    protected $table = 'posts';

    public static function getPosts($args)
    {
        return Post::where('post_type', self::$cptName)
                     ->where(function ($query) use ($args) {
                         if (isset($args['s'])) {
                             $query->where('post_title', 'like', '%' . $args['s'] . '%')
                                   ->orWhere('ID', 'like', '%' . $args['s'] . '%');
                         }
                     })
                     ->orderBy('ID', $args['order'])
                     ->skip($args['offset'])
                     ->take($args['posts_per_page'])
                     ->get();
    }

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

        // Delete the table items
        NinjaTableItem::where('table_id', $tableId)->delete();
    }

    public static function makeDuplicate($oldPostId, $newPostId)
    {
        global $wpdb;
        $oldPostId = (int)$oldPostId;
        $newPostId = (int)$newPostId;

        // Duplicate table settings.
        $postMetas = get_post_meta($oldPostId);

        foreach ($postMetas as $metaKey => $metaValue) {
            update_post_meta($newPostId, $metaKey, maybe_unserialize($metaValue[0]));
        }

        // Duplicate table rows.
        $itemsTable = $wpdb->prefix . esc_sql(ninja_tables_db_table_name());

        $sql = "INSERT INTO $itemsTable (`position`, `table_id`, `owner_id`, `settings`, `attribute`, `value`, `created_at`, `updated_at`)";
        $sql .= " SELECT `position`, $newPostId, `owner_id`, `settings`, `attribute`, `value`, `created_at`, `updated_at` FROM $itemsTable";
        $sql .= " WHERE `table_id` = $oldPostId";

        $wpdb->query($sql);
    }

    public static function updatedSettings($tableId, $rawColumns, $tablePreference)
    {
        $tableColumns             = array();
        $formattedTablePreference = array();
        $provider                 = ninja_table_get_data_provider($tableId);

        if ($rawColumns && is_array($rawColumns)) {
            foreach ($rawColumns as $column) {
                foreach ($column as $column_index => $column_value) {
                    if ($provider === 'google-csv' && gettype($column_value) === 'string') {
                        $column_value = htmlspecialchars_decode($column_value);
                    }
                    if (is_int($column_value)) {
                        $column[$column_index] = intval($column_value);
                    } else {
                        $column[$column_index] = $column_value;
                    }
                }
                $tableColumns[] = $column;
            }
            $tableColumns = apply_filters('ninja_table_update_columns_' . ninja_table_get_data_provider($tableId),
                $tableColumns, $rawColumns, $tableId);
            do_action('ninja_table_before_update_columns_' . ninja_table_get_data_provider($tableId),
                $tableColumns, $rawColumns, $tableId);
            update_post_meta($tableId, '_ninja_table_columns', $tableColumns);
        }

        if ($tablePreference && is_array($tablePreference)) {
            $formattedTablePreference = ninjaTableNormalize($tablePreference);
            update_post_meta($tableId, '_ninja_table_settings', $formattedTablePreference);
        }

        ninjaTablesClearTableDataCache($tableId);

        update_post_meta($tableId, '_last_edited_by', get_current_user_id());
        update_post_meta($tableId, '_last_edited_time', date('Y-m-d H:i:s'));

        return [
            'message'  => __('Successfully updated configuration.', 'ninja-tables'),
            'columns'  => $tableColumns,
            'settings' => $formattedTablePreference,
        ];
    }
}
