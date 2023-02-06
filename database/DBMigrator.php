<?php

namespace NinjaTables\Database;

use NinjaTables\Database\Migrations\NinjaTableItemsMigrator;

class DBMigrator
{
    public static function run($network_wide = false)
    {
        NinjaTableItemsMigrator::migrate();
    }
}
