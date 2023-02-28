<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Services\BackgroundInstaller;
use NinjaTables\Framework\Request\Request;

class NinjaChartsController extends Controller
{
    public function installNinjaCharts(Request $request)
    {
        $plugin = [
            'name'      => 'Ninja Charts',
            'repo-slug' => 'ninja-charts',
            'file'      => 'plugin.php',
            'redirect'  => self_admin_url('admin.php?page=ninja-charts#/chart-list')
        ];

        (new BackgroundInstaller())->install($plugin);

        return $this->sendSuccess([
            'data' => [
                'message'  => 'Successfully enabled Ninja Charts.',
                'redirect' => $plugin['redirect']
            ]
        ], 200);
    }
}