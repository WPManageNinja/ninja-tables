<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Foundation\Application;
use NinjaTables\Database\DBMigrator;
use NinjaTables\Database\DBSeeder;

class ActivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($network_wide = false)
    {
        DBMigrator::run($network_wide);
        DBSeeder::run();
    }
}
