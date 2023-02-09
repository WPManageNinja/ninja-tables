<?php

namespace NinjaTables\App\Models;

use NinjaTables\App\Models\Model;
use NinjaTables\Framework\Response\Response;

class DefaultModel extends Model
{
    private $cptName = 'ninja-table';

    protected function saveTable($postId = null)
    {
        $attributes = array(
            'post_title'   => sanitize_text_field($_REQUEST['post_title']),
            'post_content' => wp_kses_post($_REQUEST['post_content']),
            'post_type'    => $this->cptName,
            'post_status'  => 'publish'
        );

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

}