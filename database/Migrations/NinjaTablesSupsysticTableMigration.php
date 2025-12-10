<?php

namespace NinjaTables\Database\Migrations;

use NinjaTables\Framework\Database\Schema;
use NinjaTables\Framework\Support\Arr;

class NinjaTablesSupsysticTableMigration extends NinjaTablesMigration
{
    public function getTables()
    {
        global $wpdb;
        $tables = array();
        try {
            if (Schema::hasTable($wpdb->prefix. 'supsystic_tbl_tables')) {
                $tables = $wpdb->get_results("SELECT id as ID,title as post_title FROM {$wpdb->prefix}supsystic_tbl_tables", \OBJECT); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
            }
        } catch (\Exception $exception) {

        }

        return $tables;
    }

    public function migrateTable($tableId)
    {
        try {
            $tableId = (int)$tableId;
            global $wpdb;
            $table = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}supsystic_tbl_tables WHERE id = %d LIMIT 1", $tableId)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
            if ( ! $table) {
                return new \WP_Error('broke', __('No Table Found with the selected table', 'ninja-tables'));
            }

            $tableRows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}supsystic_tbl_rows WHERE table_id = %d", $tableId)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

            $rawHeaders  = array_shift($tableRows);
            $raw_header  = @unserialize($rawHeaders->data);
            $headerCells = Arr::get($raw_header, 'cells', array());

            $headers = array();
            foreach ($headerCells as $header_cell) {
                if ($headerTitle = Arr::get($header_cell, 'fv', '')) {
                    $headers[] = $headerTitle;
                } else {
                    $headers[] = Arr::get($header_cell, 'd', '');
                }
            }

            $rows = array();
            foreach ($tableRows as $table_row) {
                $cell      = @unserialize($table_row->data);
                $rowValues = Arr::get($cell, 'cells', array());
                if ($rowValues) {
                    $rowItem = array();
                    foreach ($rowValues as $row_value) {
                        if ($value = Arr::get($row_value, 'fv', '')) {
                            $rowItem[] = $value;
                        } else {
                            $rowItem[] = Arr::get($row_value, 'd', '');
                        }
                    }
                    $rows[] = $rowItem;
                }

            }

            $headerRow     = $this->formatHeader($headers);
            $formattedRows = $this->prepareTableRows(array_keys($headerRow), $rows);

            $tableTitle = $table->title . ' (Imported From Supsystic Table)';

            $ninjaTableId = $this->createTable($tableTitle);
            $this->initTableConfiguration($ninjaTableId, $headerRow);
            $this->addRows($ninjaTableId, $formattedRows);

            return $ninjaTableId;
        } catch (\Exception $exception) {
            return new \WP_Error('broke', $exception->getMessage());
        }
    }
}
