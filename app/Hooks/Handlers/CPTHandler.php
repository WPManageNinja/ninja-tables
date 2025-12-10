<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;
use NinjaTables\App\CPT\NinjaTable;

class CPTHandler
{
    /*
    * Add all Custom Post Type classes here to
    * register all of your Custom Post Types.
    */

    protected $customPostTypes = [
        NinjaTable::class
    ];

    public function registerPostTypes()
    {
        foreach ($this->customPostTypes as $cpt) {
            App::make($cpt)->registerPostType();
        }
    }
}
