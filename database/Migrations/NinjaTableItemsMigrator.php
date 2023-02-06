<?php

namespace NinjaTables\Database\Migrations;

class NinjaTableItemsMigrator
{
    static $tableName = 'ninja_table_items';

    public static function migrate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name      = $wpdb->prefix . static::$tableName;
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            $sql
                = "CREATE TABLE $table_name (
				id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				position int(11),
				table_id BIGINT(20) NOT NULL,
				owner_id int(11),
				attribute varchar(255) NOT NULL,
				settings longtext,
				value longtext,
				created_at timestamp NULL,
				updated_at timestamp NULL
			) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            update_option('_ninja_tables_settings_migration', true);
            update_option('_ninja_tables_sorting_migration', true);
        } else {
            // check if the new columns is there or not
            do_action('ninja_table_check_db_integrity');
            update_option('_ninja_tables_settings_migration', true);
            update_option('_ninja_tables_sorting_migration', true);
        }

        if(function_exists('ninja_table_clear_all_cache')) {
            ninja_table_clear_all_cache();
        }
    }
}


