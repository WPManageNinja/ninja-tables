<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class ExternalCSVController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'External CSV Controller'
        ];
    }

}