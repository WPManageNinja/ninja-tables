<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class FluentFormsController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Fluent Forms Controller'
        ];
    }

}