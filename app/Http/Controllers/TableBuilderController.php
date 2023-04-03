<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Modules\DragAndDrop\InitialDataHandling;
use NinjaTables\App\Modules\ImportExport;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Http\Controller;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;
use NinjaTables\App\Modules\DynamicConfig;
use NinjaTables\App\Modules\ReadyMadeTable;

class TableBuilderController extends Controller
{
    public function index(Request $request)
    {
        $initialData = (new InitialDataHandling())->getAllInitialData();
        return $this->sendSuccess($initialData, 200);
    }

    public function importCSV($csvData, $fileName)
    {
        $initialData = new InitialDataHandling();
        $table_id                  = $this->wpInsertPost($fileName);
        $table_data                = $initialData->getTableData();
        $table_data['table']['tr'] = count($csvData);
        $table_data['table']['tc'] = count($csvData[0]);
        $table_data['headers']     = $initialData->makeTableHeader($table_data);
        $table_data['table_name']  = $fileName;
        $table_data['data']        = $initialData->makeTableRow($table_data, $csvData);

        $data = [
            'table_name'       => $fileName,
            'table_settings'   => $initialData->settingConfig(),
            'table_responsive' => $initialData->responsiveConfig(),
            'table_data'       => $table_data,
            'table_html'       => null
        ];

        return $this->updatePostMeta($table_id, $data);
    }

    public function store(Request $request)
    {
        $table_type = Sanitizer::sanitizeTextField($request->data['table_data']['table_type']);
        $table_name = Sanitizer::sanitizeTextField($request->data['table_data']['table_name']);

        if (isset($table_type) && $table_type !== '') {
            return $this->generateByTemplateConfig($table_type); // for ready-made table
        }


        $initialData = new InitialDataHandling();
        $table_id              = $this->wpInsertPost($table_name);
        $data                  = sanitize_post_field('data', $request->data, $table_id, 'db');
        $table_data            = $data['table_data'];
        $table_data['headers'] = $initialData->makeTableHeader($table_data);
        $table_data['data']    = $initialData->makeTableRow($table_data);

        $meta_data = [
            'table_name'       => $table_name,
            'table_settings'   => $initialData->settingConfig(),
            'table_responsive' => $initialData->responsiveConfig(),
            'table_data'       => $table_data,
            'table_html'       => null
        ];

        return $this->updatePostMeta($table_id, $meta_data);
    }

    public function generateByTemplateConfig($table_type)
    {
        $table            = (new ReadyMadeTable())->tableByType($table_type);
        $table_settings   = $table['table_settings'];
        $table_responsive = $table['table_responsive'];
        $table_data       = $table['table_data'];

        $table_id = $this->wpInsertPost($table_data['table_name']);

        $data = [
            'table_id'         => $table_id,
            'table_name'       => $table_type,
            'table_settings'   => $table_settings,
            'table_responsive' => $table_responsive,
            'table_data'       => $table_data,
            'table_html'       => null
        ];

        return $this->updatePostMeta($table_id, $data);
    }

    public function updatePostMeta($table_id, array $data)
    {
        update_post_meta($table_id, '_ninja_tables_data_provider', 'drag_and_drop');
        update_post_meta($table_id, '_ninja_table_builder_table_html', $data['table_html']);
        update_post_meta($table_id, '_ninja_table_builder_table_settings', $data['table_settings']);
        update_post_meta($table_id, '_ninja_table_builder_table_responsive', $data['table_responsive']);
        update_post_meta($table_id, '_ninja_table_builder_table_data', $data['table_data']);

        return $this->sendSuccess([
            'data' => [
                'id' => $table_id
            ]
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $initialData = new InitialDataHandling();
        $table_id          = intval($id);
        $table_settings    = get_post_meta($table_id, '_ninja_table_builder_table_settings', true);
        $table_responsive  = get_post_meta($table_id, '_ninja_table_builder_table_responsive', true);
        $table_data        = get_post_meta($table_id, '_ninja_table_builder_table_data', true);
        $components        = $initialData->componentConfig();
        $ready_made_tables = $initialData->templateConfig();
        $table_data_info   = DynamicConfig::getTableDataInfo($table_data['data'], $initialData->tableColumnStyling(),
            $initialData->tableRawStyling());

        return $this->sendSuccess([
            'data' => [
                'settings'          => DynamicConfig::getSetting($table_settings, $initialData->settingConfig()),
                'responsive'        => DynamicConfig::getResponsive($table_responsive, $initialData->responsiveConfig()),
                'components'        => $components,
                'ready_made_tables' => $ready_made_tables,
                'table_data'        => [
                    'id'         => $table_id,
                    'table_name' => $table_data['table_name'],
                    'data'       => $table_data_info,
                    'headers'    => $table_data['headers'],
                    'table'      => array_replace_recursive($initialData->getOtherTableConfig(), $table_data['table'])
                ]
            ]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $table_id   = intval($request->table_id);
        $table_html = ninjaTablesEscapeScript($request->table_html);
        $json       = ninjaTablesEscapeScript($request->data);
        $data       = json_decode(htmlspecialchars_decode($json), true);

        $table_name            = Arr::get($data, 'table_data.table_name');
        $table_settings        = Arr::get($data, 'settings');
        $table_responsive      = Arr::get($data, 'responsive');
        $table_data            = Arr::get($data, 'table_data');
        $table_data['headers'] = Arr::get($data, 'table_data.headers');

        $this->wpUpdatePost($table_id, $table_name);

        $data = [
            'table_name'       => $table_name,
            'table_settings'   => $table_settings,
            'table_responsive' => $table_responsive,
            'table_data'       => $table_data,
            'table_html'       => $table_html
        ];

        return $this->updatePostMeta($table_id, $data);
    }

    public function wpInsertPost($table_name)
    {
        $my_post = [
            'post_title'  => $table_name,
            'post_type'   => 'ninja-table',
            'post_status' => 'publish'
        ];

        return wp_insert_post($my_post);
    }

    public function wpUpdatePost($table_id, $table_name)
    {
        $my_post = [
            'ID'          => $table_id,
            'post_title'  => $table_name,
            'post_type'   => 'ninja-table',
            'post_status' => 'publish'
        ];

        return wp_update_post($my_post);
    }
}
