<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\Post;
use NinjaTables\App\Modules\DataProviders\NinjaFooTable;
use NinjaTables\Database\Migrations\NinjaTableItemsMigrator;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;

class TablesController extends Controller
{
    private $cptName = 'ninja-table';

    public function index(Request $request)
    {
        $perPage = intval(Arr::get($request->all(), 'per_page')) ?: 10;

        $currentPage = intval(Arr::get($request->all(), 'page')) ?: 1;

        $skip = $perPage * ($currentPage - 1);

        $args = array(
            'posts_per_page' => $perPage,
            'offset'         => $skip,
            'orderby'        => Sanitizer::sanitizeTextField(Arr::get($request->all(), 'orderBy')),
            'order'          => Sanitizer::sanitizeTextField(Arr::get($request->all(), 'order')),
            'post_type'      => $this->cptName,
            'post_status'    => 'any',
        );

        if (Arr::get($request->all(), 'search') && $request->search) {
            $args['s'] = Sanitizer::sanitizeTextField($request->search);
        }

        try {
            $tables    = Post::getPosts($args);
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
        if ( ! Sanitizer::sanitizeTextField(Arr::get($request->all(), 'post_title'))) {
            $this->sendError(array(
                'message' => __('The name field is required.', 'ninja-tables')
            ), 423);
        }

        $postId = intval(Arr::get($request->all(), 'tableId'));

        $caption = Arr::get($request->all(), 'table_caption');
        update_post_meta($postId, '_ninja_table_caption', Sanitizer::sanitizeTextField($caption));

        $attributes = array(
            'post_title'   => Sanitizer::sanitizeTextField(Arr::get($request->all(), 'post_title')),
            'post_content' => wp_kses_post(Arr::get($request->all(), 'post_content')),
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

    public function tableInnerHtml($id)
    {
        $tableId       = intval($id);
        $tableColumns  = ninja_table_get_table_columns($tableId, 'public');
        $tableSettings = ninja_table_get_table_settings($tableId, 'public');

        $formattedColumns = [];
        foreach ($tableColumns as $index => $column) {
            $formattedColumn             = NinjaFooTable::getFormattedColumn($column, $index, $tableSettings, true,
                'by_created_at');
            $formattedColumn['original'] = $column;
            $formattedColumns[]          = $formattedColumn;
        }

        $formatted_data = ninjaTablesGetTablesDataByID($tableId, $tableColumns, $tableSettings['default_sorting'], true,
            25);

        if (count($formatted_data) > 25) {
            $formatted_data = array_slice($formatted_data, 0, 25);
        }

        return (string)$this->app->view->make('public/table-inner-html', array(
            'table_columns' => $formattedColumns,
            'table_rows'    => $formatted_data
        ));
    }
}
