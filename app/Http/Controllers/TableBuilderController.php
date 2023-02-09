<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class TableBuilderController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Table Builder Controller'
        ];
    }
}