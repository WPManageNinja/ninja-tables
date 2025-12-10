<?php

namespace NinjaTables\Database\Migrations;

use NinjaTables\App\Models\NinjaTableItem;

class NinjaTableItemsMigrator
{
    private static $tableName = 'ninja_table_items';

    public static function migrate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name      = $wpdb->prefix . static::$tableName;
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
            $sql = "CREATE TABLE $table_name (
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

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            update_option('_ninja_tables_settings_migration', true);
            update_option('_ninja_tables_sorting_migration', true);
        } else {
            // check if the new columns is there or not
            do_action('ninja_table_check_db_integrity');
            update_option('_ninja_tables_settings_migration', true);
            update_option('_ninja_tables_sorting_migration', true);
        }

        if (function_exists('ninja_table_clear_all_cache')) {
            ninja_table_clear_all_cache();
        }
    }

    public static function checkDBMigrations()
    {
        static::migrateIdAndTableIdColumn();

        $firstRow = NinjaTableItem::first();

        if ($firstRow) {
            $firstRow = (object)$firstRow->toArray();
        }

        if ( ! $firstRow) {
            if (get_option('_ninja_table_db_settings_owner_id')) {
                return true;
            }
            static::migrateSettingColumnIfNeeded();
            static::migrateOwnerColumnIfNeeded();
            update_option('_ninja_table_db_settings_owner_id', true);

            return true;
        }
        if ( ! property_exists($firstRow, 'owner_id')) {
            static::migrateOwnerColumnIfNeeded();
        }
        if ( ! property_exists($firstRow, 'settings')) {
            static::migrateSettingColumnIfNeeded();
        }
        if ( ! property_exists($firstRow, 'position')) {
            static::migratePositionDatabase();
        }

        return true;
    }

    public static function migrateIdAndTableIdColumn()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . esc_sql(ninja_tables_db_table_name());
        $results = $wpdb->get_results($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
            "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'id'",
            DB_NAME,
            $tableName
        ));
        $dataType  = $results[0]->DATA_TYPE;

        if ($dataType == 'int') {
            $sql = "ALTER TABLE $tableName 
            MODIFY COLUMN id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, 
            MODIFY COLUMN table_id BIGINT(20) UNSIGNED NOT NULL"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query($sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
        }
    }

    public static function migratePositionDatabase()
    {
        // If the database is already migrated for manual
        // sorting the option table would have a flag.
        $option = '_ninja_tables_sorting_migration';
        global $wpdb;
        $tableName = esc_sql($wpdb->prefix . sanitize_key(ninja_tables_db_table_name()));

        // Update the databse to hold the sorting position number.
        $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
            "ALTER TABLE $tableName ADD COLUMN `position` INT(11) AFTER `id`;" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
        ));

        // Keep a flag on the options table that the
        // db is migrated to use for manual sorting.
        update_option($option, true);
    }

    private static function migrateSettingColumnIfNeeded()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . esc_sql(ninja_tables_db_table_name());
        // Update the databse to hold the sorting position number.
        $sql = "ALTER TABLE $tableName ADD COLUMN `settings` LONGTEXT AFTER `value`;";
        ob_start();
        $wpdb->query($sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $maybeError = ob_get_clean();
        update_option('_ninja_tables_settings_migration', true);
    }

    private static function migrateOwnerColumnIfNeeded()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . esc_sql(ninja_tables_db_table_name());
        // Update the databse to hold the sorting position number.
        $sql = "ALTER TABLE $tableName ADD COLUMN `owner_id` int(11) AFTER `table_id`;"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        ob_start();
        $wpdb->query($sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $maybeError = ob_get_clean();
    }

}


