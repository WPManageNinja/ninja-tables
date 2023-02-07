<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class GoogleSheetsController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Google Sheets Controller'
        ];
    }

}