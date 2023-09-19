<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Modules\DragAndDrop\InitConfig;
use NinjaTables\App\Traits\WpPostTrait;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Http\Controller;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;
use NinjaTables\App\Modules\DynamicConfig;
use NinjaTables\App\Modules\ReadyMadeTable;

class TableBuilderController extends Controller
{
    use WpPostTrait;

    public function index(Request $request)
    {
        $initConfig = (new InitConfig())->getAllInitialData();

        return $this->sendSuccess($initConfig, 200);
    }

    public function store(Request $request)
    {
        $data    = json_decode(Arr::get($request->all(), 'data'), true);
        $table_type = Sanitizer::sanitizeTextField(Arr::get($data, 'table_data.table_type'));
        $table_name = Sanitizer::sanitizeTextField(Arr::get($data, 'table_data.table_name'));

        if (isset($table_type) && $table_type !== '') {
            return $this->generateByTemplateConfig($table_type); // for ready-made table
        }


        $initConfig            = new InitConfig();
        $table_id              = $this->wpInsertPost($table_name);
        $data                  = sanitize_post_field('data', $data, $table_id, 'db');
        $table_data            = $data['table_data'];
        $table_data['headers'] = $initConfig->makeTableHeader($table_data);
        $table_data['data']    = $initConfig->makeTableRow($table_data);

        $meta_data = [
            'table_name'       => $table_name,
            'table_settings'   => $initConfig->settingConfig(),
            'table_responsive' => $initConfig->responsiveConfig(),
            'table_data'       => $table_data,
            'table_html'       => null
        ];

        $this->updatePostMeta($table_id, $meta_data);

        return $this->sendSuccess([
            'data' => [
                'id' => $table_id
            ]
        ], 200);
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

        $this->updatePostMeta($table_id, $data);

        return $this->sendSuccess([
            'data' => [
                'id' => $table_id
            ]
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $initConfig        = new InitConfig();
        $table_id          = intval($id);
        $table_settings    = get_post_meta($table_id, '_ninja_table_builder_table_settings', true);
        $table_responsive  = get_post_meta($table_id, '_ninja_table_builder_table_responsive', true);
        $table_data        = get_post_meta($table_id, '_ninja_table_builder_table_data', true);
        $components        = $initConfig->componentConfig();
        $ready_made_tables = $initConfig->templateConfig();
        $table_data_info   = DynamicConfig::getTableDataInfo($table_data['data'], $initConfig->tableColumnStyling(),
            $initConfig->tableRawStyling());

        return $this->sendSuccess([
            'data' => [
                'settings'          => DynamicConfig::getSetting($table_settings, $initConfig->settingConfig()),
                'responsive'        => DynamicConfig::getResponsive($table_responsive, $initConfig->responsiveConfig()),
                'components'        => $components,
                'ready_made_tables' => $ready_made_tables,
                'table_data'        => [
                    'id'         => $table_id,
                    'table_name' => $table_data['table_name'],
                    'data'       => $table_data_info,
                    'headers'    => $table_data['headers'],
                    'table'      => array_replace_recursive($initConfig->getOtherTableConfig(), $table_data['table']),
                    'preview_url' => site_url('?ninjatable_builder_preview=' . $table_id)
                ]
            ]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $table_id   = intval(Arr::get($request->all(), 'table_id'));
        $table_html = ninjaTablesEscapeScript(Arr::get($request->all(), 'table_html'));
        $json       = ninjaTablesEscapeScript(Arr::get($request->all(), 'data'));
        $data       = json_decode(htmlspecialchars_decode($json), true);

        if ( ! ninjaTablesCanUnfilteredHTML()) {
            ninja_tables_allowed_css_properties();
            $table_html = $this->convertRGBtoHex($table_html);
            $table_html = wp_kses($table_html, ninja_tables_allowed_html_tags());
        }

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

        $this->updatePostMeta($table_id, $data);

        return $this->sendSuccess([
            'data' => [
                'id' => $table_id
            ]
        ], 200);
    }

    public function convertRGBtoHex($tableHtml)
    {
        return preg_replace_callback('/rgb\(\d{1,3},\s*\d{1,3},\s*\d{1,3}\)/', function ($matches) {
            $rgbArray = explode(",", $matches[0]);
            $red      = intval(trim($rgbArray[0], "rgb()"));
            $green    = intval(trim($rgbArray[1]));
            $blue     = intval(trim($rgbArray[2], " )"));

            // Convert the RGB values to hex format
            $hexCode = "#" . str_pad(dechex($red), 2, "0", STR_PAD_LEFT) . str_pad(dechex($green), 2, "0",
                    STR_PAD_LEFT) . str_pad(dechex($blue), 2, "0", STR_PAD_LEFT);

            // Return the hex code
            return $hexCode;
        }, $tableHtml);
    }
}
