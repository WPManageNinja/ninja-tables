<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\App;
use NinjaTables\App\Models\Import;
use NinjaTables\App\Traits\ImportTrait;
use NinjaTables\Database\Migrations\NinjaTablesSupsysticTableMigration;
use NinjaTables\Database\Migrations\NinjaTablesTablePressMigration;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;
use NinjaTables\App\Library\Csv\Reader;
use NinjaTables\App\Models\NinjaTableItem;

class ImportController extends Controller
{
    use ImportTrait;

    private $cpt_name = 'ninja-table';

    private static $tableName = 'ninja_table_items';

    public function tableBuilderImport(Request $request)
    {
        return $this->extracted($request);
    }

    public function defaultImport(Request $request)
    {
        $format    = Sanitizer::sanitizeTextField(Arr::get($request->all(), 'format'));
        $doUnicode = Sanitizer::sanitizeTextField(Arr::get($request->all(), 'do_unicode'));

        if ($format == 'dragAndDrop') {
            return $this->extracted($request);
        } else {
            if ($format == 'csv') {
                $this->uploadTableCsv($doUnicode);
            } elseif ($format == 'json') {
                $this->uploadTableJson();
            } elseif ($format == 'ninjaJson') {
                $this->uploadTableNinjaJson();
            }

            $this->json([
                'message' => __('No appropriate driver found for the import format.', 'ninja-tables')
            ], 423);
        }
    }

    private function uploadTableCsv($doUnicode)
    {
        $mimes = array(
            'text/csv',
            'text/plain',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'text/anytext',
            'application/octet-stream',
            'application/txt'
        );

        if ( ! in_array(Sanitizer::sanitizeTextField($_FILES['file']['type']), $mimes)) {
            return $this->sendError([
                'data' => [
                    'errors'  => array(),
                    'message' => __('Please upload valid CSV', 'ninja-tables')
                ]
            ], 423);
        }

        $tmpName  = Sanitizer::sanitizeTextField($_FILES['file']['tmp_name']);
        $fileName = Sanitizer::sanitizeTextField($_FILES['file']['name']);

        $data = file_get_contents($tmpName);
        if ($doUnicode && $doUnicode == 'yes') {
            $data = utf8_encode($data);
        }

        try {
            $reader = Reader::createFromString($data)->fetchAll();
        } catch (\Exception $exception) {
            return $this->sendError([
                'data' => [
                    'errors'  => $exception->getMessage(),
                    'message' => __('Something is wrong when parsing the csv', 'ninja-tables')
                ]
            ], 423);
        }

        $header = array_shift($reader);

        $tableId = $this->createTable(array(
            'post_title'   => $fileName,
            'post_content' => '',
            'post_type'    => $this->cpt_name,
            'post_status'  => 'publish'
        ));

        $header = ninja_table_format_header($header);

        $this->storeTableConfigWhenImporting($tableId, $header);

        ninjaTableInsertDataToTable($tableId, $reader, $header);

        $this->json([
            'message' => __('Successfully added a table.', 'ninja-tables'),
            'tableId' => $tableId
        ], 200);
    }

    private function createTable($data = null)
    {
        return wp_insert_post($data
            ? $data
            : array(
                'post_title'   => __('Temporary table name', 'ninja-tables'),
                'post_content' => __('Temporary table description',
                    'ninja-tables'),
                'post_type'    => $this->cpt_name,
                'post_status'  => 'publish'
            ));
    }

    private function storeTableConfigWhenImporting($tableId, $header)
    {
        $ninjaTableColumns = array();

        foreach ($header as $key => $name) {
            $ninjaTableColumns[] = array(
                'key'         => $key,
                'name'        => $name,
                'breakpoints' => ''
            );
        }
        update_post_meta($tableId, '_ninja_table_columns', $ninjaTableColumns);
        $ninjaTableSettings = ninja_table_get_table_settings($tableId, 'admin');
        update_post_meta($tableId, '_ninja_table_settings', $ninjaTableSettings);
        ninjaTablesClearTableDataCache($tableId);
    }

    private function uploadTableJson()
    {
        $tableId = $this->createTable();

        $tmpName = Sanitizer::sanitizeTextField($_FILES['file']['tmp_name']);

        $content = json_decode(file_get_contents($tmpName), true);

        $reverse_content = array_reverse($content);
        $header          = array_keys(array_pop($reverse_content));

        $formattedHeader = array();
        foreach ($header as $head) {
            $formattedHeader[$head] = $head;
        }

        $this->storeTableConfigWhenImporting($tableId, $formattedHeader);

        ninjaTableInsertDataToTable($tableId, $content, $formattedHeader);

        $this->json([
            'message' => __('Successfully added a table.', 'ninja-tables'),
            'tableId' => $tableId
        ], 200);
    }

    private function uploadTableNinjaJson()
    {
        $tmpName = Sanitizer::sanitizeTextField($_FILES['file']['tmp_name']);

        $parsedContent = file_get_contents($tmpName);

        $content = json_decode($parsedContent, true);

        if (json_last_error()) {
            for ($i = 0; $i <= 31; ++$i) {
                $parsedContent = str_replace(chr($i), "", $parsedContent);
            }
            $parsedContent = str_replace(chr(127), "", $parsedContent);
            if (0 === strpos(bin2hex($parsedContent), 'efbbbf')) {
                $parsedContent = substr($parsedContent, 3);
            }
            $content = json_decode($parsedContent, true);
        }

        // validation
        if ( ! $content['post'] || ! $content['columns'] || ! $content['settings']) {
            $this->json([
                'message' => __('You have a faulty JSON file. Please export a new one.',
                    'ninja-tables')
            ], 423);
        }


        $tableAttributes = array(
            'post_title'   => Sanitizer::sanitizeTitle($content['post']['post_title']),
            'post_content' => wp_kses_post($content['post']['post_content']),
            'post_type'    => $this->cpt_name,
            'post_status'  => 'publish'
        );

        $tableId = $this->createTable($tableAttributes);

        update_post_meta($tableId, '_ninja_table_columns', $content['columns']);

        update_post_meta($tableId, '_ninja_table_settings', $content['settings']);

        $metas = $content['metas'];
        foreach ($metas as $meta_key => $meta_value) {
            update_post_meta($tableId, $meta_key, $meta_value);
        }

        if ($rows = $content['rows']) {
            $header = [];
            foreach ($content['columns'] as $column) {
                $header[$column['key']] = $column['name'];
            }
            ninjaTableInsertDataToTable($tableId, $rows, $header);
        }

        global $wpdb;
        if (isset($content['original_rows']) && $originalRows = $content['original_rows']) {
            foreach ($originalRows as $row) {
                $row['table_id'] = $tableId;
                $row['value']    = json_encode($row['value'], JSON_UNESCAPED_UNICODE);
                $tableName       = $wpdb->prefix . static::$tableName;
//                $this->reset();
                $wpdb->insert($tableName, $row, false);
            }
        }

        $this->json([
            'message' => __('Successfully added a table.', 'ninja-tables'),
            'tableId' => $tableId
        ], 200);
    }

    public function getTablesFromOtherPlugin(Request $request)
    {
        $plugin = Sanitizer::sanitizeTextField(Arr::get($request->all(), 'plugin'));
        if ($plugin == 'TablePress') {
            $libraryClass = new NinjaTablesTablePressMigration();
        } elseif ($plugin == 'supsystic') {
            $libraryClass = new NinjaTablesSupsysticTableMigration();
        } else {
            return false;
        }

        $tables = $libraryClass->getTables();

        $this->json([
            'tables' => $tables
        ], 200);
    }

    public function importTableFromOtherPlugin(Request $request)
    {
        $plugin  = Sanitizer::sanitizeTextField(Arr::get($request->all(), 'plugin'));
        $tableId = intval(Arr::get($request->all(), 'tableId'));

        if ($plugin == 'TablePress') {
            $libraryClass = new NinjaTablesTablePressMigration();
        } elseif ($plugin == 'supsystic') {
            $libraryClass = new NinjaTablesSupsysticTableMigration();
        } else {
            return false;
        }

        $tableId = $libraryClass->migrateTable($tableId);
        if (is_wp_error($tableId)) {
            return $this->sendError([
                'data' => [
                    'message' => 'Something Went Wrong When Migrating'
                ]
            ], 423);
        }

        $message = __(
            'Successfully imported. Please go to all tables and review your newly imported table.',
            'ninja-tables'
        );

        return $this->sendSuccess([
            'data' => [
                'message' => $message,
                'tableId' => $tableId
            ]
        ], 200);
    }


    public function uploadCsvInExistingTable(Request $request)
    {
        global $wpdb;
        $tableId = intval(Arr::get($request->all(), 'table_id'));
        $tmpName = $_FILES['file']['tmp_name'];

        $data = file_get_contents($tmpName);
        if (Arr::get($request->all(), 'do_unicode') && $request->do_unicode == 'yes') {
            $data = utf8_encode($data);
        }

        try {
            $reader = Reader::createFromString($data)->fetchAll();
        } catch (\Exception $exception) {
            return $this->sendError([
                'data' => [
                    'errors'  => $exception->getMessage(),
                    'message' => __('CSV File is not valid', 'ninja-tables')
                ]
            ], 423);
        }

        $csvHeader = array_shift($reader);
        $csvHeader = array_map('esc_attr', $csvHeader);

        $config = get_post_meta($tableId, '_ninja_table_columns', true);

        if ( ! $config) {
            return $this->json(array(
                'message' => __('Please set table configuration first', 'ninja-tables')
            ), 423);
        }

        // Extract header keys to a plain array from the config.
        $header = array_map(function ($item) {
            return $item['key'];
        }, $config);

        // We are gonna allow to upload new data if the CSV
        // has the same number of headers as the config.
        if (count($header) != count($csvHeader)) {
            return $this->sendError([
                'data' => [
                    'message' => __('Please use the provided CSV header structure font face.', 'ninja-tables')
                ]
            ], 423);
        }

        $data = array();

        $userId    = get_current_user_id();
        $timeStamp = time() - (count($reader) * 100);
        foreach ($reader as $item) {
            $itemTemp = array_combine($header, $item);
            array_push($data, array(
                'table_id'   => $tableId,
                'attribute'  => 'value',
                'owner_id'   => $userId,
                'value'      => json_encode($itemTemp, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s', $timeStamp),
                'updated_at' => date('Y-m-d H:i:s')
            ));
            $timeStamp = $timeStamp + 100;
        }

        $replace = Arr::get($request->all(), 'replace') === 'true';

        $app  = App::getInstance();
        $data = $app->applyFilters('ninja_tables_import_table_data', $data, $tableId);

        if ($replace) {
            NinjaTableItem::where('table_id', $tableId)->delete();
        }

        // We are gonna batch insert by small chunk so that we can avoid PHP
        // memory issue or MYSQL max_allowed_packet issue for large data set.
        $tableName = $wpdb->prefix . static::$tableName;
        foreach (array_chunk($data, 3000) as $chunk) {
            ninjtaTableBatchInsert($tableName, $chunk);
        }

        ninjaTablesClearTableDataCache($tableId);

        return $this->json([
            'data' => [
                'message' => __('Successfully uploaded data.', 'ninja-tables')
            ]
        ]);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function extracted(Request $request)
    {
        $fileName = 'Ninja-tables' . date('d-m-Y');
        $url      = sanitize_url($request->get('url'));

        if ( ! empty($url)) {
            static::importFromURL($url);
        }

        $data = static::getData();

        $tableId = $this->savedDragAndDropTable($data, $fileName);

        return $this->sendSuccess([
            'data' => [
                'id' => $tableId
            ]
        ], 200);
    }
}
