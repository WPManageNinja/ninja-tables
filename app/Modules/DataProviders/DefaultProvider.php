<?php

namespace NinjaTables\App\Modules\DataProviders;

use NinjaTables\App\Models\NinjaTableItem;
use NinjaTables\Framework\Support\Arr;

class DefaultProvider
{
    public function boot()
    {
        add_filter('ninja_tables_get_table_default', array($this, 'getTableSettings'));
        add_filter('ninja_tables_fetching_table_rows_default', array($this, 'data'), 10, 6);
    }

    public function getTableSettings($table)
    {
        $table->isEditable        = true;
        $table->dataSourceType    = 'default';
        $table->isExportable      = true;
        $table->isImportable      = true;
        $table->isSortable        = true;
        $table->isCreatedSortable = true;
        $table->hasCacheFeature   = true;

        return $table;
    }

    public function data($data, $tableId, $defaultSorting, $limit = false, $skip = false, $ownOnly = false)
    {
        $advancedQuery = false;
        $disabledCache = false;

        if ($skip || $limit || $ownOnly) {
            $advancedQuery = true;
        }

        $settings        = ninja_table_get_table_settings($tableId);
        $sortingType     = Arr::get($settings, 'sorting_type', 'by_created_at');
        $sortingColumn   = Arr::get($settings, 'sorting_column');
        $sortingColumnBy = Arr::get($settings, 'sorting_column_by', 'asc');

        // if cached not disabled then return cached data
        if (!$advancedQuery && !$disabledCache = ninja_tables_shouldNotCache($tableId)) {
            $cachedData = get_post_meta($tableId, '_ninja_table_cache_object', true);
            if ($cachedData) {
                return $cachedData;
            }
        }

        $query = NinjaTableItem::where('table_id', $tableId);

        if ($sortingColumn && ($limit || $skip) && $sortingType === 'by_column') {
            $query = $this->orderByJsonQuery($query, $tableId);
        } else {
            if ($defaultSorting == 'new_first') {
                $query->orderBy('created_at', 'desc');
            } elseif ($defaultSorting == 'manual_sort') {
                $query->orderBy('position', 'asc');
            } else {
                $query->orderBy('created_at', 'asc');
            }
        }

        $skip = intval($skip);
        if ($skip && $skip > 0) {
            $query->skip($skip);
        }

        $limit = intval($limit);
        if ($limit && $limit > 0) {
            $query->limit($limit);
        } elseif ($skip && $skip > 0) {
            $query->limit(99999);
        }

        if ($ownOnly) {
            $query = apply_filters('ninja_table_own_data_filter_query', $query, $tableId);
        }

        $items = $query->get();

        foreach ($items as $item) {
            $values             = json_decode($item->value, true);
            $values['___id___'] = $item->id;
            $data[]             = $values;
        }

        // Please do not hook this filter unless you don't know what you are doing.
        // Hook ninja_tables_get_public_data instead.
        // You should hook this if you need to cache your filter modifications
        $data = apply_filters('ninja_tables_get_raw_table_data', $data, $tableId);

        if (!$advancedQuery && !$disabledCache) {
            update_post_meta($tableId, '_ninja_table_cache_object', $data);
        }

        return $data;
    }

    protected function orderByJsonQuery($query, $tableId)
    {
        $settings        = ninja_table_get_table_settings($tableId);
        $columns         = ninja_table_get_table_columns($tableId);
        $sortingColumn   = Arr::get($settings, 'sorting_column');
        $sortingColumnBy = Arr::get($settings, 'sorting_column_by', 'asc');

        $column = Arr::first($columns, function ($column) use ($sortingColumn) {
            return Arr::get($column, 'key') === $sortingColumn;
        });

        $dataType = Arr::get($column, 'data_type');

        $dateFormat = Arr::get($column, 'dateFormat');

        if ($dataType === 'number') {
            $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(value, '$.$sortingColumn')) AS SIGNED) " . $sortingColumnBy);
        } else if ($dataType === 'date') {
            $mysqlDateFormat = $this->convertDateFormatToMysql($dateFormat);
            $query->orderByRaw("STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(value, '$.$sortingColumn')), '$mysqlDateFormat') " . $sortingColumnBy);
        } else {
            $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(value, '$.$sortingColumn')) " . $sortingColumnBy);
        }

        return $query;
    }

    /**
     * Convert any date format string to MySQL format
     *
     * @param string $dateFormat
     * @return string
     */
    protected function convertDateFormatToMysql($dateFormat)
    {
        if (!$dateFormat) {
            return '%Y-%m-%d';
        }

        $formatMap = [
            'DD' => '%d',    // Day of the month, 2 digits with leading zeros
            'D' => '%e',     // Day of the month without leading zeros
            'MM' => '%m',    // Month, 2 digits with leading zeros
            'M' => '%c',     // Month without leading zeros
            'YYYY' => '%Y',  // Year, 4 digits
            'YY' => '%y',    // Year, 2 digits

            // Add additional format components if needed
            'HH' => '%H',    // Hour (00-23)
            'hh' => '%h',    // Hour (01-12)
            'mm' => '%i',    // Minutes
            'ss' => '%s',    // Seconds
        ];

        // Copy the format string for manipulation
        $mysqlFormat = $dateFormat;

        // Replace each format component with its MySQL equivalent
        foreach ($formatMap as $phpFormat => $mysqlFormatEquivalent) {
            $mysqlFormat = str_replace($phpFormat, $mysqlFormatEquivalent, $mysqlFormat);
        }

        // Replace any remaining characters (separators like -, /, etc.) as they are
        return $mysqlFormat;
    }
}
