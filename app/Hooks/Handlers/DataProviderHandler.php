<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\Modules\DataProviders\DefaultProvider;
use NinjaTables\App\Modules\DataProviders\FluentFormProvider;
use NinjaTables\Framework\Foundation\Application;

class DataProviderHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle()
    {
        $this->app->make(FluentFormProvider::class)->boot();
        $this->app->make(DefaultProvider::class)->boot();
    }
}