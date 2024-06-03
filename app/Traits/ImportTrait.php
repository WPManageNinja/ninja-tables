<?php

namespace NinjaTables\App\Traits;

use NinjaTables\App\Library\Csv\Reader;
use NinjaTables\App\Modules\DragAndDrop\InitConfig;
use NinjaTables\Framework\Support\Sanitizer;

trait ImportTrait
{
    use WpPostTrait;

    private static $mimeTypes = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/json',
    ];

    public function savedDragAndDropTable($csvData, $fileName)
    {
        if (isset($csvData['table_name'])) {
            $tableId                   = $this->wpInsertPost($csvData['table_name']);
            $data    = $csvData;
        } else {
            $initConfig                = new InitConfig();
            $tableId                   = $this->wpInsertPost($fileName);
            $table_data                = $initConfig->getTableData();
            $table_data['table']['tr'] = count($csvData);
            $table_data['table']['tc'] = count($csvData[0]);
            $table_data['headers']     = $initConfig->makeTableHeader($table_data);
            $table_data['table_name']  = $fileName;
            $table_data['data']        = $initConfig->makeTableRow($table_data, $csvData);

            $data = [
                'table_name'       => $fileName,
                'table_settings'   => $initConfig->settingConfig(),
                'table_responsive' => $initConfig->responsiveConfig(),
                'table_data'       => $table_data,
                'table_html'       => null
            ];
        }

        $this->updatePostMeta($tableId, $data);

        return $tableId;
    }

    public static function importFromURL($url)
    {
        $file_info     = new \finfo (FILEINFO_MIME_TYPE);
        $remoteContent = ninjaTablesGetRemoteContent($url);
        $mime_type                  = $file_info->buffer($remoteContent);
        $_FILES['file']['type']     = $mime_type;
        $_FILES['file']['tmp_name'] = $url;

        return $_FILES;
    }

    public static function getData()
    {
        $mimes    = static::$mimeTypes;
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
        $tmpName       = Sanitizer::sanitizeTextField($_FILES['file']['tmp_name']);
        $remoteContent = ninjaTablesGetRemoteContent($tmpName);
        $data          = mb_convert_encoding($remoteContent, 'UTF-8', 'ISO-8859-1');

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

        $content = ninjaTablesGetRemoteContent($tmpName);
        $content = json_decode($content, true);

        if (isset($content['table_id']) && isset($content['table_name'])) {
            return [
                'table_name'       => $content['table_name'],
                'table_settings'   => $content['table_settings'],
                'table_responsive' => $content['table_responsive'],
                'table_data'       => $content['table_data'],
                'table_html'       => $content['table_html']
            ];
        } else {
            return $content;
        }
    }
}
