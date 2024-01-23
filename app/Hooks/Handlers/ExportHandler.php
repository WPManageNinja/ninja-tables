<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\Library\Csv\Writer;
use NinjaTables\App\Models\NinjaTableItem;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;

class ExportHandler
{
    public function dragAndDropExport()
    {
        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        $tableId    = intval(Arr::get($_REQUEST, 'table_id'));
        $format     = Sanitizer::sanitizeTextField(Arr::get($_REQUEST, 'format'));
        $tableTitle = get_the_title($tableId);
        $fileName   = Sanitizer::sanitizeTitle($tableTitle);
        $tableData  = get_post_meta($tableId, '_ninja_table_builder_table_data', true);

        if ($format === 'csv') {
            static::exportCSV($tableData, $fileName);
        } elseif ($format === 'json') {
            static::exportJSON($tableId, $fileName);
        }
    }

    public static function exportCSV($tableData, $fileName = null)
    {
        $rows = [];
        foreach ($tableData['data'] as $row) {
            $cols = [];
            foreach ($row['rows'] as $columns) {
                $values = '';
                foreach ($columns['columns'] as $key => $item) {
                    if (is_array($item['data']['value'])) {
                        $tmp = [];
                        foreach ($item['data']['value'] as $value) {
                            $tmp[] = ninjaTablesSanitizeForCSV($value);
                        }

                        $values .= implode(",", $tmp);
                    } else {
                        $values .= " " . ninjaTablesSanitizeForCSV($item['data']['value']);
                    }
                }
                $cols[] = $values;
            }
            $rows[] = $cols;
        }

        static::exportAsCSV($rows, $fileName);
    }

    public static function exportJSON($tableId, $fileName = null)
    {
        $table_settings   = get_post_meta($tableId, '_ninja_table_builder_table_settings', true);
        $table_responsive = get_post_meta($tableId, '_ninja_table_builder_table_responsive', true);
        $table_data       = get_post_meta($tableId, '_ninja_table_builder_table_data', true);
        $table_html       = get_post_meta($tableId, '_ninja_table_builder_table_html', true);
        $data             = [
            'table_id'         => $tableId,
            'table_name'       => $fileName,
            'table_settings'   => $table_settings,
            'table_responsive' => $table_responsive,
            'table_data'       => $table_data,
            'table_html'       => $table_html
        ];

        static::exportAsJSON($data, $fileName);
    }

    public function defaultExport()
    {
        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        $tableId = intval(Arr::get($_REQUEST, 'table_id'));
        $format  = Sanitizer::sanitizeTextField(Arr::get($_REQUEST, 'format'));

        $tableTitle = get_the_title($tableId);

        $fileName = sanitize_title($tableTitle, 'Export-Table-' . date('Y-m-d-H-i-s'), 'preview');

        $tableColumns = ninja_table_get_table_columns($tableId, 'admin');

        $tableSettings = ninja_table_get_table_settings($tableId, 'admin');

        if ($format == 'csv') {

            $sortingType = Arr::get($tableSettings, 'sorting_type', 'by_created_at');

            $tableColumns = ninja_table_get_table_columns($tableId, 'admin');
            $data         = ninjaTablesGetTablesDataByID($tableId, $tableColumns, $sortingType, true);

            $header = array();

            foreach ($tableColumns as $item) {
                $header[$item['key']] = $item['name'];
            }

            $exportData = array();

            foreach ($data as $item) {
                $temp = array();
                foreach ($header as $accessor => $name) {
                    $value = Arr::get($item, $accessor);
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $temp[] = ninjaTablesSanitizeForCSV($value);
                }
                array_push($exportData, $temp);
            }

            static::exportAsCSV($exportData, $fileName, array_values($header));

        } elseif ($format == 'json') {
            $table = get_post($tableId);

            $dataProvider = ninja_table_get_data_provider($tableId);
            $rows         = array();
            if ($dataProvider == 'default') {

                $rawRows = NinjaTableItem::selectedRows($tableId);

                foreach ($rawRows as $row) {
                    $row->value = json_decode($row->value, true);
                    $rows[]     = $row;
                }
            }

            $matas   = get_post_meta($tableId);
            $allMeta = array();

            $excludedMetaKeys = array(
                '_ninja_table_cache_object',
                '_ninja_table_cache_html',
                '_external_cached_data',
                '_last_external_cached_time',
                '_last_edited_by',
                '_last_edited_time',
                '__ninja_cached_table_html'
            );

            foreach ($matas as $metaKey => $metaValue) {
                if ( ! in_array($metaKey, $excludedMetaKeys)) {
                    if (isset($metaValue[0])) {
                        $metaValue         = maybe_unserialize($metaValue[0]);
                        $allMeta[$metaKey] = $metaValue;
                    }
                }
            }

            $exportData = array(
                'post'          => $table,
                'columns'       => $tableColumns,
                'settings'      => $tableSettings,
                'data_provider' => $dataProvider,
                'metas'         => $allMeta,
                'rows'          => array(),
                'original_rows' => $rows
            );

            static::exportAsJSON($exportData, $fileName);
        }
    }

    private static function exportAsCSV($data, $fileName = null, $header = null)
    {
        $fileName = ($fileName) ? $fileName . '.csv' : 'export-data-' . date('d-m-Y') . '.csv';

        $writer = Writer::createFromFileObject(new \SplTempFileObject());
        $writer->setDelimiter(",");
        $writer->setNewline("\r\n");
        $header !== null ? $writer->insertOne($header) : '';
        $writer->insertAll($data);
        $writer->output($fileName);
        die();
    }

    private static function exportAsJSON($data, $fileName = null)
    {
        $fileName = ($fileName) ? $fileName . '.json' : 'export-data-' . date('d-m-Y') . '.json';

        header('Content-disposition: attachment; filename=' . $fileName);

        header('Content-type: application/json');

        echo json_encode($data);

        die();
    }
}
