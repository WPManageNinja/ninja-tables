<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Import Table Controller'
        ];
    }
}