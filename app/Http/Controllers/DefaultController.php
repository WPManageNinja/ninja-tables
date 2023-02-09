<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Http\Controller;
use NinjaTables\Framework\Support\Sanitizer;
use NinjaTables\App\Models\DefaultModel;

class DefaultController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Default Controller Index'
        ];
    }

    public function store(Request $request)
    {
        if ( ! Sanitizer::sanitizeTextField($_REQUEST['post_title'])) {
            $this->sendError(array(
                'message' => __('The name field is required.', 'ninja-tables')
            ), 423);
        }

        $postId = intval($_REQUEST['tableId']);

        if (isset($_REQUEST['table_caption'])) {
            update_post_meta($postId, '_ninja_table_caption', Sanitizer::sanitizeTextField($_REQUEST['table_caption']));
        }

        $this->json(array(
            'table_id' => DefaultModel::saveTable($postId),
            'message'  => __('Table ' . ($postId ? 'updated' : 'created') . ' successfully.', 'ninja-tables')
        ), 200);
    }

    public function show(Request $request, $id)
    {
        return [
            'message' => 'Default Controller Show'
        ];
    }

    public function update(Request $request, $id)
    {
        return [
            'message' => 'Default Controller Update'
        ];
    }

    public function destroy(Request $request, $id)
    {
        return [
            'message' => 'Default Controller Destroy'
        ];
    }

}
