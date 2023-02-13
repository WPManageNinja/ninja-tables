<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\TableModel;
use NinjaTables\Framework\Request\Request;
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
            'orderby'        => sanitize_text_field($request->orderBy),
            'order'          => sanitize_text_field($request->order),
            'post_type'      => 'ninja-table',   //cpt name
            'post_status'    => 'any',
        );

        if (isset($request->search) && $request->search) {
            $args['s'] = sanitize_text_field($request->search);
        }

        try {
            $tables    = get_posts($args);
            $tables    = $this->app->applyFilters('ninja_tables_get_all_tables', $tables);
            $tablesRes = TableModel::getTables($perPage, $currentPage, $tables);
            $this->json($tablesRes, 200);
        } catch (\Exception $e) {
            $this->json(array(
                'message' => $e->getMessage()
            ), 300);
        }
    }


    public function deleteTable(Request $request, $id)
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
            TableModel::destroyTable($tableId);

            $this->json(array(
                'message' => __('Table deleted successfully.', 'ninja-tables')
            ), 200);
        } catch (\Exception $e) {
            $this->json(array(
                'message' => $e->getMessage()
            ), 300);
        }
    }

    public function duplicateTable(Request $request, $id)
    {
        $tableId = intval($id);

        $table = get_post($tableId);

        if ( ! $table) {
            $this->json(array(
                'message' => __('Table not found.', 'ninja-tables')
            ), 404);
        }

        $newTableId = wp_insert_post(array(
            'post_title'   => $table->post_title . '( Duplicate )',
            'post_content' => $table->post_content,
            'post_status'  => 'publish',
            'post_type'    => $this->cptName,
        ));

        if ( ! $newTableId) {
            $this->json(array(
                'message' => __('Something went wrong while duplicating the table.', 'ninja-tables')
            ), 500);
        }

        try {
            TableModel::makeDuplicate($tableId, $newTableId);

            $this->json(array(
                'message' => __('Table duplicated successfully.', 'ninja-tables')
            ), 200);
        } catch (\Exception $e) {
            $this->json(array(
                'message' => $e->getMessage()
            ), 300);
        }
    }
}