<?php

namespace NinjaTables\App\Modules\DataProviders;

use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;
use FluentFormPro\Payments\PaymentHelper;

class FluentFormProvider
{
    public function boot()
    {
        add_filter('ninja_tables_get_table_fluent-form', array($this, 'getTableSettings'));
        add_filter('ninja_tables_get_table_data_fluent-form', array($this, 'getTableData'), 10, 4);
        add_filter('ninja_tables_fetching_table_rows_fluent-form', array($this, 'data'), 10, 5);
    }

    public function getFields($id)
    {
        $form            = wpFluentForm('FluentForm\App\Modules\Form\Form');
        $formFieldParser = wpFluentForm('FluentForm\App\Modules\Form\FormFieldsParser');

        // Default meta data fields.
        $labels = [
            ['name' => 'id', 'label' => 'ID'],
            ['name' => 'serial_number', 'label' => 'Serial Number'],
            ['name' => 'status', 'label' => 'Status']
        ];

        $form   = $form->fetchForm($id);
        $inputs = $formFieldParser->getEntryInputs($form);
        foreach ($formFieldParser->getAdminLabels($form, $inputs) as $key => $value) {
            $labels[] = array('name' => $key, 'label' => $value);
        }

        if ($form->has_payment) {
            $labels = $this->makePaymentFieldLabels($labels);
        }

        return $labels;
    }

    public function makePaymentFieldLabels($labels)
    {
        $labels[] = ['name' => 'payment_total', 'label' => 'Payment Total'];
        $labels[] = ['name' => 'payment_status', 'label' => 'Payment Status'];
        $labels[] = ['name' => 'payer_name', 'label' => 'Billing Name'];
        $labels[] = ['name' => 'payer_email', 'label' => 'Billing Email'];
        $labels[] = ['name' => 'charge_id', 'label' => 'Transaction ID'];
        $labels[] = ['name' => 'created_at', 'label' => 'Payment Date'];

        return $labels;
    }

    public function setPaymentFieldValue($value)
    {
        if (isset($value->payment_status)) {
            $transaction = wpFluent()->table('fluentform_transactions')
                                     ->where('submission_id', $value->id)
                                     ->first();

            return [
                'payment_total'  => PaymentHelper::formatMoney($value->payment_total, $value->currency),
                'payment_status' => $value->payment_status,
                'payer_name'     => $transaction->payer_name,
                'payer_email'    => $transaction->payer_email,
                'charge_id'      => $transaction->charge_id,
                'created_at'     => $value->created_at
            ];
        }

        return [];
    }

    public function saveTable($form, $fields, $tableId, $formId)
    {
        $currentUserEntryOnly = Arr::get($form, 'current_user_entry_only');
        $entryLimit           = Arr::get($form, 'entry_limit');
        $entryStatus          = Arr::get($form, 'entry_status');

        $columns = array();
        foreach ($fields as $field) {
            $columns[] = array(
                'name'                => $field['label'],
                'key'                 => $field['name'],
                'breakpoints'         => null,
                'data_type'           => 'text',
                'dateFormat'          => null,
                'header_html_content' => null,
                'enable_html_content' => false,
                'contentAlign'        => null,
                'textAlign'           => null,
                'original_name'       => $field['name']
            );
        }

        if ($tableId) {
            $oldColumns = get_post_meta($tableId, '_ninja_table_columns', true);
            foreach ($columns as $key => $newColumn) {
                foreach ($oldColumns as $oldColumn) {
                    if ($oldColumn['original_name'] == $newColumn['original_name']) {
                        $columns[$key] = $oldColumn;
                    }
                }
            }

            // Reset/Reorder array indices
            $columns = array_values($columns);
        } else {
            $tableId = $this->saveOrCreateTable();
        }

        update_post_meta($tableId, '_ninja_table_columns', $columns);
        update_post_meta($tableId, '_ninja_tables_data_provider', 'fluent-form');
        update_post_meta($tableId, '_ninja_tables_data_provider_ff_form_id', $formId);

        if ($currentUserEntryOnly) {
            update_post_meta($tableId, '_ninja_tables_ff_own_submission_only',
                Sanitizer::sanitizeTextField($currentUserEntryOnly));
        }

        update_post_meta(
            $tableId, '_ninja_tables_data_provider_ff_entry_limit',
            Sanitizer::sanitizeTextField($entryLimit)
        );

        update_post_meta(
            $tableId, '_ninja_tables_data_provider_ff_entry_status',
            Sanitizer::sanitizeTextField($entryStatus)
        );

        return $tableId;
    }

    public function getTableSettings($table)
    {
        $table->isEditable        = false;
        $table->dataSourceType    = 'fluent-form';
        $table->isEditableMessage = 'You may edit your table settings here.';
        $table->fluentFormFormId  = get_post_meta(
            $table->ID, '_ninja_tables_data_provider_ff_form_id', true
        );
        $table->entry_limit       = get_post_meta(
            $table->ID, '_ninja_tables_data_provider_ff_entry_limit', true
        );
        $table->entry_status      = get_post_meta(
            $table->ID, '_ninja_tables_data_provider_ff_entry_status', true
        );

        $table->current_user_entry_only = get_post_meta($table->ID, '_ninja_tables_ff_own_submission_only', true);

        $table->isExportable      = true;
        $table->isImportable      = false;
        $table->isCreatedSortable = true;
        $table->isSortable        = false;
        $table->hasCacheFeature   = false;

        return $table;
    }

    public function getTableData($data, $tableId, $perPage = -1, $offset = 0)
    {
        if (function_exists('wpFluentForm')) {
            // we need this short-circuite to overwrite fluentform entry permissions
            add_filter('fluentform_verify_user_permission_fluentform_entries_viewer',
                array($this, 'addEntryPermission'));

            $formId  = get_post_meta($tableId, '_ninja_tables_data_provider_ff_form_id', true);
            $entries = wpFluentForm('FluentForm\App\Modules\Entries\Entries')->_getEntries(
                intval($formId),
                isset($_GET['page']) ? intval($_GET['page']) : 1,
                intval($perPage),
                $this->getOrderBy($tableId),
                'all',
                null
            );

            // removing this short-circuite to overwrite fluentform entry permissions
            remove_filter('fluentform_verify_user_permission_fluentform_entries_viewer',
                array($this, 'addEntryPermission'));

            $columns = $this->getTableColumns($tableId);

            $formattedEntries = array();
            foreach ($entries['submissions']['data'] as $key => $value) {
                // Prepare the entry with the selected columns.
                $value->user_inputs = $this->prepareEntry($value, $columns);
                $formattedEntries[] = array(
                    'id'       => $value->id,
                    'position' => $key,
                    'values'   => $value->user_inputs
                );
            }

            return array(
                $formattedEntries,
                $entries['submissions']['paginate']['total']
            );
        }

        return $data;
    }

    public function data($data, $tableId, $defaultSorting, $limitEntries = false, $skip = false)
    {
        if ( ! function_exists('wpFluentForm')) {
            return $data;
        }

        add_filter('fluentform_verify_user_permission_fluentform_entries_viewer', array($this, 'addEntryPermission'));

        $formId = get_post_meta($tableId, '_ninja_tables_data_provider_ff_form_id', true);
        $status = get_post_meta($tableId, '_ninja_tables_data_provider_ff_entry_status', true);

        $limit = null;

        if ($limitEntries || $skip) {
            $limit = intval($limitEntries) + intval($skip);
        }

        if ( ! $limit) {
            $limit = (int)get_post_meta($tableId, '_ninja_tables_data_provider_ff_entry_limit', true);
        }

        $entryStatus = apply_filters(
            'ninja_tables_fluentform_entry_status', $status, $tableId, $formId
        );

        $entryLimit = apply_filters(
            'ninja_tables_fluentform_per_page', ($limit ? $limit : -1), $tableId, $formId
        );

        $orderBy = apply_filters(
            'ninja_tables_fluentform_order_by', $this->getOrderBy($tableId), $tableId, $formId
        );

        $ownSubmissionOnly = get_post_meta($tableId, '_ninja_tables_ff_own_submission_only', true);
        $wheres            = array();
        if ($ownSubmissionOnly == 'yes') {
            $userId = get_current_user_id();
            if ( ! $userId) {
                return $data;
            }
            $wheres = array(
                array('user_id', $userId)
            );
        }

        $entries = wpFluentForm('FluentForm\App\Modules\Entries\Entries')->_getEntries(
            intval($formId), -1, $entryLimit, $orderBy, $entryStatus, null, $wheres
        );

        if ($skip && isset($entries['submissions']['data'])) {
            $entries['submissions']['data'] = array_slice($entries['submissions']['data'], $skip, $limitEntries);
        }

        remove_filter('fluentform_verify_user_permission_fluentform_entries_viewer',
            array($this, 'addEntryPermission'));

        $columns = $this->getTableColumns($tableId);

        foreach ($entries['submissions']['data'] as $key => $value) {
            // Prepare the entry with the selected columns.
            $data[] = $this->prepareEntry($value, $columns);
        }

        $data = apply_filters('ninja_tables_fluentform_all_entries', $data, $entries['submissions']['data'], $columns,
            $tableId);

        return $data;
    }

    public function saveOrCreateTable($postId = null)
    {
        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        //Need to update this code segment by replacing $_REQUEST
        $attributes = array(
            'post_title'   => Sanitizer::sanitizeTextField($_REQUEST['post_title']),
            'post_content' => isset($_REQUEST['post_content']) ? wp_kses_post($_REQUEST['post_content']) : '',
            'post_type'    => 'ninja-table',
            'post_status'  => 'publish'
        );
        if ( ! $postId) {
            $postId = wp_insert_post($attributes);
        } else {
            $attributes['ID'] = $postId;
            wp_update_post($attributes);
        }

        return $postId;
    }

    private function getOrderBy($tableId)
    {
        $tableSettings = get_post_meta($tableId, '_ninja_table_settings', true);
        if (Arr::get($tableSettings, 'default_sorting') == 'old_first') {
            return 'ASC';
        } else {
            return 'DESC';
        }
    }

    public function addEntryPermission()
    {
        return true;
    }

    /**
     * Prepare the entry with the selected columns.
     *
     * @param  $entry
     * @param array $columns
     *
     * @return array
     */
    private function prepareEntry($entry, $columns = [])
    {
        $entry->user_inputs = $this->addEntryMeta($entry, $columns);

        return array_intersect_key(
            $entry->user_inputs, array_combine($columns, $columns)
        );
    }

    /**
     * Add available meta data to the entry.
     *
     * @param  $value
     * @param array $columns
     *
     * @return array
     */
    private function addEntryMeta($value, $columns = [])
    {
        $defaultData = [
            'id'            => $value->id,
            'serial_number' => $value->serial_number,
            'status'        => $value->status
        ];

        return array_merge($value->user_inputs, array_intersect_key(
            array_merge($defaultData, $this->setPaymentFieldValue($value)),
            array_combine($columns, $columns)
        ));
    }

    /**
     * Get the table columns extracted from the column settings.
     *
     * @param  $tableId
     *
     * @return array
     */
    private function getTableColumns($tableId)
    {
        return array_map(function ($column) {
            return $column['original_name'];
        }, get_post_meta($tableId, '_ninja_table_columns', true));
    }
}
