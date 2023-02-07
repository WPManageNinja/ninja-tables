<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class WPPostsController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'WP Posts Controller'
        ];
    }
}