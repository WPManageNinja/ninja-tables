<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\Import;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Sanitizer;
use League\Csv\Reader;

class ImportController extends Controller
{
    private $cpt_name = 'ninja-table';

    private static $tableName = 'ninja_table_items';

    private static $mimes_type = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/json',
    ];

    public function tableBuilderImport(Request $request)
    {
        $url      = Sanitizer::sanitizeTextField($request->url);
        $fileName = 'Ninja-tables' . date('d-m-Y');

        if (isset($url) && ! empty($url)) {
            $data = static::importFromURL($url);
        } else {
            $fileType = Sanitizer::sanitizeTextField($_FILES['file']['type']);
            if ($fileType === "application/json") {
                return static::import();
            }

            $data     = static::import();
            $fileName = Sanitizer::sanitizeTextField($_FILES['file']['name']);
        }

        return (new TableBuilderController())->importCSV($data, $fileName);
    }

    public static function importFromURL($url)
    {
        $file_info                  = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type                  = $file_info->buffer(file_get_contents($url));
        $_FILES['file']['type']     = $mime_type;
        $_FILES['file']['tmp_name'] = $url;

        return static::import();
    }

    private static function importCSV()
    {
        $tmpName = Sanitizer::sanitizeTextField($_FILES['file']['tmp_name']);
        $data    = file_get_contents($tmpName);

        try {
            $reader = Reader::createFromString($data)->fetchAll();
        } catch (\Exception $exception) {
            wp_send_json_error(array(
                'errors'  => $exception->getMessage(),
                'message' => __('Something is wrong when parsing the csv', 'ninja-tables')
            ), 423);
        }

        return $reader;
    }

    private static function importJSON()
    {
        $tmpName = Sanitizer::sanitizeTextField($_FILES['file']['tmp_name']);
        $content = json_decode(file_get_contents($tmpName), true);

        if (isset($content['table_id']) && $content['table_id']) {
            return static::ninjaTableJSONImport();
        } else {
            return $content;
        }
    }

    private static function ninjaTableJSONImport()
    {
        $tmpName       = Sanitizer::sanitizeTextField($_FILES['file']['tmp_name']);
        $parsedContent = file_get_contents($tmpName);
        $content       = json_decode($parsedContent, true);
        $table_id      = (new TableBuilderController())->wpInsertPost($content['table_name']);

        $data = [
            'table_name'       => $content['table_name'],
            'table_settings'   => $content['table_settings'],
            'table_responsive' => $content['table_responsive'],
            'table_data'       => $content['table_data'],
            'table_html'       => $content['table_html']
        ];

        return (new TableBuilderController())->updatePostMeta($table_id, $data);
    }

    public function defaultImport(Request $request)
    {
        $format = Sanitizer::sanitizeTextField($request->format);
        if ($format == 'dragAndDrop') {
            $fileType = Sanitizer::sanitizeTextField($_FILES['file']['type']);
            $fileName = Sanitizer::sanitizeTextField($_FILES['file']['name']);

            if ($fileType == 'text/csv') {
                $data = static::import();

                return (new TableBuilderController())->importCSV($data, $fileName);
            }

            return static::import();
        } else {
            if ($format == 'csv') {
                $this->uploadTableCsv();
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

    public static function import()
    {
        $mimes    = self::$mimes_type;
        $fileType = Sanitizer::sanitizeTextField($_FILES['file']['type']);
        if ( ! in_array($fileType, $mimes)) {
            wp_send_json_error(array(
                'errors'  => array(),
                'message' => __('Please upload valid CSV or JSON', 'ninja-tables')
            ), 423);
        }

        if ($fileType === 'text/csv' || $fileType === 'application/csv' || $fileType === 'text/plain') {
            return static::importCSV();
        } elseif ($fileType === 'application/json') {
            return static::importJSON();
        }

    }

    private function uploadTableCsv()
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
        if (isset($_REQUEST['do_unicode']) && Sanitizer::sanitizeTextField($_REQUEST['do_unicode']) == 'yes') {
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
                Import::insert($tableName, $row);
            }
        }

        $this->json([
            'message' => __('Successfully added a table.', 'ninja-tables'),
            'tableId' => $tableId
        ], 200);
    }
}
