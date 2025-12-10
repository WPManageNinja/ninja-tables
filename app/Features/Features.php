<?php

namespace NinjaTables\App\Features;

class Features
{
    public function register()
    {
        (new ProductComparison())->register();
    }
}
