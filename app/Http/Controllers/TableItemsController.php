<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\NinjaTableItem;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;

class TableItemsController extends Controller
{
    public function index(Request $request, $id)
    {
        $perPage     = Arr::get($request->all(), 'per_page', 10);
        $currentPage = Arr::get($request->all(), 'page', 1);
        $skip        = $perPage * ($currentPage - 1);
        $tableId     = intval($id);
        $search      = Sanitizer::sanitizeTextField(Arr::get($request->all(), 'search'));

        $dataSourceType = ninja_table_get_data_provider($tableId);

        $data = NinjaTableItem::getItems($tableId, $perPage, $currentPage, $skip, $search, $dataSourceType);

        $this->json($data, 200);
    }

    public function delete(Request $request, $id)
    {
        $data = ninja_tables_sanitize_array($request->all());

        $tableId = intval($id);

        $id = Arr::get($data, 'id');

        $ids = is_array($id) ? $id : array($id);

        $ids = array_map(function ($item) {
            return intval($item);
        }, $ids);

        NinjaTableItem::deleteTableItem($tableId, $ids);

        $this->json(array(
            'message' => __('Successfully deleted data.', 'ninja-tables')
        ), 200);
    }

    public function store(Request $request, $id)
    {
        $tableId = intval($id);

        if (user_can_richedit()) {
            $row = ninja_tables_sanitize_table_content_array(Arr::get($request->all(), 'row', []), $tableId);
        } else {
            ninja_tables_allowed_css_properties();
            $row = ninja_tables_sanitize_array(Arr::get($request->all(), 'row', []));
        }

        $formattedRow = array();

        foreach ($row as $key => $item) {
            $formattedRow[$key] = wp_unslash($item);
        }

        $created_at    = Arr::get($request->all(), 'created_at');
        $insertAfterId = Arr::get($request->all(), 'insert_after_id');
        $settings      = Arr::get($request->all(), 'settings');
        $id            = Arr::get($request->all(), 'id');

        $data = NinjaTableItem::insertTableItem($id, $tableId, $formattedRow, $created_at, $insertAfterId, $settings);

        $this->json(array(
            'message' => __('Successfully saved the data.', 'ninja-tables'),
            'item'    => $data
        ), 200);
    }

    public function update(Request $request, $id)
    {
        $rowId = intval($id);

        $row = NinjaTableItem::where('id', $rowId)->first();

        if (user_can_richedit()) {
            $data = ninja_tables_sanitize_table_content_array($request->all(), $row->table_id);
        } else {
            ninja_tables_allowed_css_properties();
            $data = ninja_tables_sanitize_array($request->all());
        }

        $columnKey   = Sanitizer::sanitizeTextField($data['column_key']);
        $columnValue = wp_unslash($data['column_value']);

        NinjaTableItem::editSingleCell($rowId, $row, $columnKey, $columnValue);

        return $this->sendSuccess([
            'data' => [
                'message' => 'Cell successfully updated'
            ]
        ], 200);
    }
}
