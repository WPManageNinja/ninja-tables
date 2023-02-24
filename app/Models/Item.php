<?php

namespace NinjaTables\App\Models;

use NinjaTables\App\Models\Model;

class Item extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'ninja_table_items';
}