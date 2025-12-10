<?php

namespace NinjaTables\App\Traits;

trait WpPostTrait
{
    public function wpInsertPost($postTitle)
    {
        $my_post = [
            'post_title'  => $postTitle,
            'post_type'   => 'ninja-table',
            'post_status' => 'publish'
        ];

        return wp_insert_post($my_post);
    }

    public function updatePostMeta($tableId, array $data)
    {
        update_post_meta($tableId, '_ninja_tables_data_provider', 'drag_and_drop');
        update_post_meta($tableId, '_ninja_table_builder_table_html', $data['table_html']);
        update_post_meta($tableId, '_ninja_table_builder_table_settings', $data['table_settings']);
        update_post_meta($tableId, '_ninja_table_builder_table_responsive', $data['table_responsive']);
        update_post_meta($tableId, '_ninja_table_builder_table_data', $data['table_data']);
    }

    public function wpUpdatePost($table_id, $postTitle)
    {
        $my_post = [
            'ID'          => $table_id,
            'post_title'  => $postTitle,
            'post_type'   => 'ninja-table',
            'post_status' => 'publish'
        ];

        return wp_update_post($my_post);
    }
}
