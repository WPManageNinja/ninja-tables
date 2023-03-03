<?php

namespace NinjaTables\App\Http\Controllers;

use League\Csv\Writer;
use NinjaTables\App\Modules\ImportExport;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;

class ExportTableController extends Controller
{
    public function export(Request $request)
    {
        $tableId = intval($request->table_id);
        $format   = Sanitizer::sanitizeTextField($request->format);
        $source   = Sanitizer::sanitizeTextField($request->source);
        if ($source === 'dragAndDrop') {
            return $this->dragAndDropExport($tableId, $format);
        } else if($source === 'default') {
            return $this->defaultExport($tableId, $format);
        }
    }

    public function dragAndDropExport($tableId, $format)
    {
        $tableTitle = get_the_title($tableId);
        $fileName   = Sanitizer::sanitizeTitle($tableTitle, 'Export-Table-' . date('Y-m-d-H-i-s'), 'preview');
        $tableData  = get_post_meta($tableId, '_ninja_table_builder_table_data', true);

        return ImportExport::export($tableId, $tableData, $fileName, $format);
    }

    public function defaultExport($tableId, $format)
    {

        $tableTitle = get_the_title($tableId);

        $fileName = sanitize_title($tableTitle, 'Export-Table-' . date('Y-m-d-H-i-s'), 'preview');

        $tableColumns = ninja_table_get_table_columns($tableId, 'admin');

        $tableSettings = ninja_table_get_table_settings($tableId, 'admin');

        if ($format == 'csv') {

            $sortingType = Arr::get($tableSettings, 'sorting_type', 'by_created_at');

            $tableColumns = ninja_table_get_table_columns($tableId, 'admin');
            $data = ninjaTablesGetTablesDataByID($tableId, $tableColumns, $sortingType, true);

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
             $this->exportAsCSV(array_values($header), $exportData, $fileName . '.csv');
        } elseif ($format == 'json') {
            $table = get_post($tableId);

            $dataProvider = ninja_table_get_data_provider($tableId);
            $rows = array();
            if ($dataProvider == 'default') {
                $rawRows = ninja_tables_DbTable()
                    ->select(array('position', 'owner_id', 'attribute', 'value', 'settings', 'created_at', 'updated_at'))
                    ->where('table_id', $tableId)
                    ->get();
                foreach ($rawRows as $row) {
                    $row->value = json_decode($row->value, true);
                    $rows[] = $row;
                }
            }

            $matas = get_post_meta($tableId);
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
                if (!in_array($metaKey, $excludedMetaKeys)) {
                    if (isset($metaValue[0])) {
                        $metaValue = maybe_unserialize($metaValue[0]);
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
            $this->exportAsJSON($exportData, $fileName . '.json');
        }
    }

    private function exportAsCSV($header, $data, $fileName = null)
    {
        $fileName = ($fileName) ? $fileName : 'export-data-' . date('d-m-Y') . '.csv';

        $writer = Writer::createFromFileObject(new \SplTempFileObject());
        $writer->setDelimiter(",");
        $writer->setNewline("\r\n");
        $writer->insertOne($header);
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
