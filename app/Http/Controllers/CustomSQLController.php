<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class CustomSQLController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Custom SQL Controller'
        ];
    }

}