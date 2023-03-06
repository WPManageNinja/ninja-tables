<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\Post;
use NinjaTables\Database\Migrations\NinjaTableItemsMigrator;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;

class TablesController extends Controller
{
    private $cptName = 'ninja-table';

    public function index(Request $request)
    {
        $perPage = intval($request->per_page) ?: 10;

        $currentPage = intval($request->page);

        $skip = $perPage * ($currentPage - 1);

        $args = array(
            'posts_per_page' => $perPage,
            'offset'         => $skip,
            'orderby'        => Sanitizer::sanitizeTextField($request->orderBy),
            'order'          => Sanitizer::sanitizeTextField($request->order),
            'post_type'      => $this->cptName,
            'post_status'    => 'any',
        );

        if (isset($request->search) && $request->search) {
            $args['s'] = Sanitizer::sanitizeTextField($request->search);
        }

        try {
            $tables    = get_posts($args);
            $tables    = $this->app->applyFilters('ninja_tables_get_all_tables', $tables);
            $tablesRes = Post::getTables($perPage, $currentPage, $tables);
            $this->json($tablesRes, 200);
        } catch (\Exception $e) {
            $this->json(array(
                'message' => $e->getMessage()
            ), 300);
        }
    }

    public function store(Request $request)
    {
        if ( ! Sanitizer::sanitizeTextField($request->post_title)) {
            $this->sendError(array(
                'message' => __('The name field is required.', 'ninja-tables')
            ), 423);
        }

        $postId = intval($request->tableId);

        if (Arr::get($request->all(), 'table_caption')) {
            update_post_meta($postId, '_ninja_table_caption', Sanitizer::sanitizeTextField($request->table_caption));
        }

        $attributes = array(
            'post_title'   => Sanitizer::sanitizeTextField($request->post_title),
            'post_content' => wp_kses_post($request->post_content),
            'post_type'    => $this->cptName,
            'post_status'  => 'publish'
        );

        $this->json(array(
            'table_id' => Post::saveTable($attributes, $postId),
            'message'  => __('Table ' . ($postId ? 'updated' : 'created') . ' successfully.', 'ninja-tables')
        ), 200);
    }

    public function delete(Request $request, $id)
    {
        $tableId = intval($id);

        $tableExist = get_post($tableId);

        if (get_post_type($tableId) != 'ninja-table') {
            $this->json(array(
                'message' => __('Invalid Table to Delete', 'ninja-tables')
            ), 300);
        }

        if ( ! $tableExist) {
            $this->sendError(array(
                'message' => __('Table not found.', 'ninja-tables')
            ), 404);
        }

        try {
            Post::destroyTable($tableId);

            $this->json(array(
                'message' => __('Table deleted successfully.', 'ninja-tables')
            ), 200);
        } catch (\Exception $e) {
            $this->json(array(
                'message' => $e->getMessage()
            ), 300);
        }
    }

    public function duplicate(Request $request, $id)
    {
        $oldPostId = intval($id);

        if ( ! $oldPostId) {
            $this->json(array(
                'message' => __('Table not found.', 'ninja-tables')
            ), 404);
        }

        NinjaTableItemsMigrator::checkDBMigrations();

        $post = get_post($oldPostId);

        // Duplicate table itself.
        $attributes = array(
            'post_title'   => $post->post_title . '( Duplicate )',
            'post_content' => $post->post_content,
            'post_type'    => $post->post_type,
            'post_status'  => 'publish'
        );

        $newPostId = wp_insert_post($attributes);

        try {
            Post::makeDuplicate($oldPostId, $newPostId);

            $this->json(array(
                'message'  => __('Table duplicated successfully.', 'ninja-tables'),
                'table_id' => $newPostId
            ), 200);
        } catch (\Exception $e) {
            $this->json(array(
                'message' => $e->getMessage()
            ), 300);
        }
    }

    public function dismissFluentSuggest(Request $request)
    {
        update_option('_ninja_tables_plugin_suggest_dismiss', time());
    }
}
