<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class WooCommerceController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'WooCommerce Controller'
        ];
    }

}