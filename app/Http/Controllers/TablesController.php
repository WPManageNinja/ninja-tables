<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\NinjaTable;
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
            $tablesRes = NinjaTable::getTables($perPage, $currentPage, $tables);
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
            'table_id' => NinjaTable::saveTable($attributes, $postId),
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
            NinjaTable::destroyTable($tableId);

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
            NinjaTable::makeDuplicate($oldPostId, $newPostId);

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

    public function getTableSettings(Request $request, $id)
    {
        $table = get_post($tableID = intval($id));
        if ( ! $table || $table->post_type != $this->cptName) {
            $this->sendError(array(
                'message' => __('No Table Found'),
                'route'   => 'home'
            ), 423);
        }
        $provider = ninja_table_get_data_provider($table->ID);

        $table = $this->app->applyFilters('ninja_tables_get_table_' . $provider, $table);

        $table->table_caption = get_post_meta($tableID, '_ninja_table_caption', true);

        $table->custom_css = get_post_meta($tableID, '_ninja_tables_custom_css', true);

        NinjaTableItemsMigrator::checkDBMigrations();

        $this->json(array(
            'preview_url' => site_url('?ninjatable_preview=' . $tableID),
            'columns'     => ninja_table_get_table_columns($tableID, 'admin'),
            'settings'    => ninja_table_get_table_settings($tableID, 'admin'),
            'table'       => $table,
        ), 200);
    }

    public function updateTableSettings(Request $request, $id)
    {
        $tableId         = intval($id);
        $rawColumns      = '';
        $tablePreference = '';

        if (Arr::get($request->all(), 'columns', [])) {
            $rawColumns = $this->app->applyFilters('ninja_tables_before_update_settings',
                ninja_tables_sanitize_array($request->columns), $tableId);
        }

        if (Arr::get($request->all(), 'table_settings', [])) {
            $tablePreference = ninja_tables_sanitize_array($request->table_settings);
        }

        $data = NinjaTable::updatedSettings($tableId, $rawColumns, $tablePreference);

        $this->json($data, 200);
    }

    public function getButtonSettings(Request $request, $id)
    {
        $tableId             = absint($id);
        $tableButtonDefaults = array(
            'csv'              => array(
                'status'     => 'no',
                'label'      => 'CSV',
                'all_rows'   => 'no',
                'bg_color'   => 'rgb(0,0,0)',
                'text_color' => 'rgb(255,255,255)'
            ),
            'print'            => array(
                'status'           => 'no',
                'label'            => 'Print',
                'all_rows'         => 'no',
                'bg_color'         => 'rgb(0,0,0)',
                'text_color'       => 'rgb(255,255,255)',
                'header_each_page' => 'no',
                'footer_each_page' => 'no',
            ),
            'button_position'  => 'after_search_box',
            'button_alignment' => 'ninja_buttons_right'
        );

        $tableButtons = get_post_meta($tableId, '_ninja_custom_table_buttons', true);
        if ( ! $tableButtons) {
            $tableButtons = array();
        }

        $tableButtons = array_replace_recursive($tableButtonDefaults, $tableButtons);

        return $this->sendSuccess([
            'data' => [
                'button_settings' => $tableButtons
            ]
        ]);
    }

    public function updateButtonSettings(Request $request, $id)
    {
        ninja_tables_allowed_css_properties();
        $tableId        = absint($id);
        $buttonSettings = wp_unslash(ninja_tables_sanitize_array($request->button_settings));
        update_post_meta($tableId, '_ninja_custom_table_buttons', $buttonSettings);

        return $this->sendSuccess(array(
            'data' => array(
                'message' => __('Settings successfully updated', 'ninja-tables')
            )
        ), 200);
    }

    // Table design & frontend editing code will write here

    public function saveCustomCSSJS(Request $request, $id)
    {
        $tableId = intval($id);
        $css     = Sanitizer::sanitizeTextField($request->custom_css);
        $css     = wp_strip_all_tags($css);
        update_post_meta($tableId, '_ninja_tables_custom_css', $css);

        $this->app->doAction('ninja_tables_custom_code_before_save', $request->all());

        return $this->sendSuccess([
            'data' => [
                'message' => 'Code successfully saved'
            ]
        ], 200);
    }

    public function getCustomCSSJS(Request $request, $id)
    {
        $tableId = intval($id);

        return $this->sendSuccess([
            'data' => [
                'custom_css' => get_post_meta($tableId, '_ninja_tables_custom_css', true),
                'custom_js'  => get_post_meta($tableId, '_ninja_tables_custom_js', true)
            ]
        ], 200);
    }

}