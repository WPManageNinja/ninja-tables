<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;

class DefaultController extends Controller
{
    public function index(Request $request)
    {
        return [
            'message' => 'Default Controller Index'
        ];
    }

    public function store(Request $request)
    {
        return [
            'message' => 'Default Controller Store'
        ];
    }

    public function show(Request $request, $id)
    {
        return [
            'message' => 'Default Controller Show'
        ];
    }

    public function update(Request $request, $id)
    {
        return [
            'message' => 'Default Controller Update'
        ];
    }

    public function destroy(Request $request, $id)
    {
        return [
            'message' => 'Default Controller Destroy'
        ];
    }

}
