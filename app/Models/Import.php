<?php

namespace NinjaTables\App\Models;

class Import extends Model
{
    protected function insert($tableName, $data, $format = false)
    {
        global $wpdb;
        $wpdb->insert($tableName, $data, $format);
//        $this->reset();
        return $wpdb->insert_id;
    }
}
