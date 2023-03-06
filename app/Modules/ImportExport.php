<?php

namespace NinjaTables\App\Modules;

use League\Csv\Reader;
use League\Csv\Writer;
use NinjaTables\App\Http\Controllers\TableBuilderController;
use NinjaTables\Framework\Support\Sanitizer;

class ImportExport
{
    public static function import()
    {
        $mimes    = [
            'text/csv',
            'text/plain',
            'application/csv',
            'application/json',
        ];
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

    public static function importFromURL($url)
    {
        $file_info                  = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type                  = $file_info->buffer(file_get_contents($url));
        $_FILES['file']['type']     = $mime_type;
        $_FILES['file']['tmp_name'] = $url;

        return static::import();
    }
}
