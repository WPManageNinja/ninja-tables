<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\App;
use NinjaTables\App\Modules\ImportExport;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Sanitizer;;

class ImportController extends Controller
{
    public function store(Request $request)
    {
        $format = Sanitizer::sanitizeTextField($request->format);
        if ($format == 'dragAndDrop') {
            $fileType = Sanitizer::sanitizeTextField($_FILES['file']['type']);
            $fileName = Sanitizer::sanitizeTextField($_FILES['file']['name']);

            if ($fileType == 'text/csv') {
                $data = ImportExport::import();

                return App::make(TableBuilderController::class)->importCSV($data, $fileName);
            }

            return ImportExport::import();
        } else {
            if ($format == 'csv') {
                $this->uploadTableCsv();
            } elseif ($format == 'json') {
                $this->uploadTableJson();
            } elseif ($format == 'ninjaJson') {
                $this->uploadTableNinjaJson();
            }

            wp_send_json(array(
                'message' => __('No appropriate driver found for the import format.', 'ninja-tables')
            ), 423);
        }
    }
}
