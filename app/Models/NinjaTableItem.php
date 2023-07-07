<?php

namespace NinjaTables\App\Models;

use NinjaTables\Framework\Support\Sanitizer;

class NinjaTableItem extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'ninja_table_items';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected function getItems($tableId, $perPage, $currentPage, $skip, $search, $dataSourceType)
    {
        if ($dataSourceType == 'default') {
            list($orderByField, $orderByType) = $this->getTableSortingParams($tableId);

            $query = $this->where('table_id', $tableId);

            if ($search) {
                $query->where('value', 'LIKE', "%$search%");
            }
            $data = $query->take($perPage)
                          ->skip($skip)
                          ->limit($perPage)
                          ->orderBy($orderByField, $orderByType)
                          ->get();

            $total = $this->where('table_id', $tableId)->count();

            $response = array();

            $hasSettings = true;
            foreach ($data as $item) {
                $item        = (object)$item->toArray();
                $hasSettings = property_exists($item, 'settings');
                $settings    = (object)array();
                if ($hasSettings) {
                    $settings = maybe_unserialize($item->settings);
                    if ( ! is_array($settings)) {
                        $settings = (object)array();
                    }
                }
                $createdBy = '';
                if (property_exists($item, 'owner_id')) {
                    $userInfo = get_userdata($item->owner_id);
                    if ($userInfo && property_exists($userInfo->data, 'display_name')) {
                        $createdBy = $userInfo->data->display_name;
                    }
                }

                $response[] = array(
                    'id'         => $item->id,
                    'created_at' => $item->created_at,
                    'settings'   => $settings,
                    'created_by' => $createdBy,
                    'position'   => property_exists($item, 'position') ? $item->position : null,
                    'values'     => json_decode($item->value, true)
                );
            }

            if ( ! $hasSettings) {
                // We have to migrate the data now
            }
        } else {
            list($response, $total) = apply_filters(
                'ninja_tables_get_table_data_' . $dataSourceType,
                array(array(), 0),
                $tableId,
                $perPage,
                $skip
            );
        }

        // Needed for other data source providers
        list($response, $total) = apply_filters(
            'ninja_tables_get_table_data',
            array($response, $total),
            $tableId,
            $perPage,
            $skip
        );

        return [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $currentPage,
            'last_page'    => ceil($total / $perPage),
            'data'         => $response,
            'data_source'  => $dataSourceType
        ];
    }

    /*
     * Get the order by field and order by type values.
     *
     * @param        $tableId
     * @param null $tableSettings
     *
     * @return array
     */
    public function getTableSortingParams($tableId, $tableSettings = null)
    {
        $tableSettings = $tableSettings ?: ninja_table_get_table_settings($tableId, 'admin');

        $orderByField = 'created_at';
        $orderByType  = 'DESC';

        if (isset($tableSettings['sorting_type'])) {
            if ($tableSettings['sorting_type'] === 'manual_sort') {
                $orderByField = 'position';
                $orderByType  = 'ASC';
            } elseif ($tableSettings['sorting_type'] === 'by_created_at') {
                $orderByField = 'created_at';
                if ($tableSettings['default_sorting'] === 'new_first') {
                    $orderByType = 'DESC';
                } else {
                    $orderByType = 'ASC';
                }
            }
        }

        return [$orderByField, $orderByType];
    }

    protected function deleteTableItem($tableId, $ids)
    {
        do_action('ninja_table_before_items_deleted', $ids, $tableId);
        $this->where('table_id', $tableId)->whereIn('id', $ids)->delete();
        do_action('ninja_table_after_items_deleted', $ids, $tableId);

        ninjaTablesClearTableDataCache($tableId);
    }

    protected function insertTableItem($id, $tableId, $formattedRow, $created_at, $insertAfterId, $settings)
    {
        $attributes = array(
            'table_id'   => $tableId,
            'attribute'  => 'value',
            'value'      => $this->asJson($formattedRow),
            'owner_id'   => get_current_user_id(),
            'updated_at' => date('Y-m-d H:i:s')
        );


        if ($settings && $settings !== 'null') {
            $attributes['settings'] = maybe_serialize(
                wp_unslash(
                    ninja_tables_sanitize_array($settings)
                )
            );
        }


        $createdAt = '';
        if ($created_at !== null) {
            $createdAt = Sanitizer::sanitizeTextField($created_at);
        }
        if ($createdAt) {
            $attributes['created_at'] = $createdAt;
        }


        if ($id = intval($id)) {
            do_action('ninja_table_before_update_item', $id, $tableId, $attributes);
            $this->where('id', $id)->update($attributes);
            do_action('ninja_table_after_update_item', $id, $tableId, $attributes);
        } else {
            if ($insertAfterId !== null) {
                list($orderByField, $orderByType) = $this->getTableSortingParams($tableId);
                if ($orderByField == 'created_at') {
                    // Calculate the insert position Date
                    $prevItemId   = absint($insertAfterId);
                    $previousItem = $this->where('id', $prevItemId)->first();
                    if ($previousItem) {
                        if ($orderByType == 'ASC') {
                            // ASC means, We have to minus time to created_at
                            $newDateStamp = strtotime($previousItem->created_at) + 1;
                        } else {
                            $newDateStamp = strtotime($previousItem->created_at) - 1;
                        }
                        $attributes['created_at'] = date('Y-m-d H:i:s', $newDateStamp);
                        $this->fixCreatedAtDate($tableId, $previousItem->created_at, $orderByType);
                    }
                }
            }

            if ( ! isset($attributes['created_at'])) {
                $attributes['created_at'] = date('Y-m-d H:i:s');
            }

            $attributes = apply_filters('ninja_tables_item_attributes', $attributes);

            do_action('ninja_table_before_add_item', $tableId, $attributes);
            $id = $insertId = $this->insertGetId($attributes);
            do_action('ninja_table_after_add_item', $insertId, $tableId, $attributes);
        }

        $item = $this->find($id);

        ninjaTablesClearTableDataCache($tableId);

        update_post_meta($tableId, '_last_edited_by', get_current_user_id());
        update_post_meta($tableId, '_last_edited_time', date('Y-m-d H:i:s'));

        $itemSettings = '';

        if ($item && $item->settings !== null) {
            $itemSettings = maybe_unserialize($item->settings);
        }

        if ( ! is_array($itemSettings)) {
            $itemSettings = (object)array();
        }

        return [
            'id'         => $item->id,
            'values'     => $formattedRow,
            'row'        => json_decode($item->value),
            'created_at' => $item->created_at,
            'settings'   => $itemSettings,
            'position'   => isset($item->position) ? $item->position : null
        ];
    }

    private function fixCreatedAtDate($tableId, $refDate, $orderType)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . esc_sql(ninja_tables_db_table_name());
        if ($orderType == 'ASC') {
            $query = "UPDATE {$tableName}
                  SET created_at = ADDTIME(created_at, 2)
                  WHERE table_id = %d
                  AND created_at > %s";
        } else {
            $orderType = 'DESC';
            $query     = "UPDATE {$tableName}
                  SET created_at = SUBTIME(created_at, 2)
                  WHERE table_id = %d
                  AND created_at < %s";
        }

        $bindings = [
            $tableId,
            $refDate
        ];
        $query    .= " ORDER BY created_at " . $orderType;
        $wpdb->query($wpdb->prepare($query, $bindings));
    }

    protected function editSingleCell($rowId, $row, $columnKey, $columnValue)
    {
        $values             = json_decode($row->value, true);
        $values[$columnKey] = $columnValue;

        $this->where('id', $rowId)->update([
            'value'      => $this->asJson($values),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        ninjaTablesClearTableDataCache($row->table_id);
        update_post_meta($row->table_id, '_last_edited_by', get_current_user_id());
        update_post_meta($row->table_id, '_last_edited_time', date('Y-m-d H:i:s'));
    }

    protected function selectedRows($tableId)
    {
        return $this->select(array(
            'position',
            'owner_id',
            'attribute',
            'value',
            'settings',
            'created_at',
            'updated_at'
        ))
             ->where('table_id', $tableId)
             ->get();
    }
}
