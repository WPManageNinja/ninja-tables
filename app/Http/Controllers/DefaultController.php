<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Http\Controller;
use NinjaTables\Framework\Support\Sanitizer;

class DefaultController extends Controller
{
    private $cptName = 'ninja-table';

    public function store(Request $request)
    {
        if ( ! Sanitizer::sanitizeTextField($request->post_title)) {
            $this->sendError(array(
                'message' => __('The name field is required.', 'ninja-tables')
            ), 423);
        }

        $postId = intval($request->tableId);

        if (isset($request->table_caption)) {
            update_post_meta($postId, '_ninja_table_caption', Sanitizer::sanitizeTextField($request->table_caption));
        }

        $attributes = array(
            'post_title'   => sanitize_text_field($request->post_title),
            'post_content' => wp_kses_post($request->post_content),
            'post_type'    => $this->cptName,
            'post_status'  => 'publish'
        );

        $this->json(array(
            'table_id' => $this->saveTable($attributes, $postId),
            'message'  => __('Table ' . ($postId ? 'updated' : 'created') . ' successfully.', 'ninja-tables')
        ), 200);
    }

    protected function saveTable($attributes, $postId = null)
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
}
