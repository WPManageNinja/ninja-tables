<?php

namespace NinjaTables\App\Features;

use NinjaTables\Framework\Support\Arr;

class ProductComparison
{
    public function register()
    {
        add_filter('ninja_get_table_columns_public', [$this, 'appendActionColumn'], 10, 2);
        add_action('ninja_rendering_table_wp_fct', array($this, 'enqueueScripts'), 20, 1);
        add_action('ninja_tables_before_table_print', [$this, 'appendBulkAction'], 10, 2);
    }

    public function appendBulkAction($table, $tableArray)
    {
        $tableId             = intval(Arr::get($tableArray, 'table_id'));
        $isBulkActionEnabled = $this->isBulkActionEnabled($tableId);
        if (!$isBulkActionEnabled) {
            return;
        }
        ?>
        <div class="ninja-bulk-actions-tr">
            <div class="ninja-bulk-actions">
                <select class="ninja-bulk-action-select">
                    <option value="">Bulk Actions</option>
                    <option value="add_to_cart">Add to Cart</option>
                    <option value="compare">Compare Products</option>
                </select>
                <button class="ninja-bulk-apply-btn" data-table-id="<?php
                echo esc_attr($tableId) ?>">Apply
                </button>
            </div>
        </div>
        <?php
    }

    public function enqueueScripts($tableArray)
    {
        $isBulkActionEnabled = $this->isBulkActionEnabled(Arr::get($tableArray, 'table_id'));

        if ($isBulkActionEnabled) {
            wp_enqueue_script(
                'ninjatable_comparison_script',
                NINJA_TABLES_DIR_URL . 'assets/js/fluent-cart/ninja_table_fct_comparison.js',
                array('jquery'),
                NINJA_TABLES_VERSION,
                true
            );
        }
    }

    public function appendActionColumn($tableColumns, $tableId)
    {
        $isBulkActionEnabled = $this->isBulkActionEnabled($tableId);

        if (!$isBulkActionEnabled) {
            return $tableColumns;
        }

        $requestURI = sanitize_text_field(Arr::get($_SERVER, 'REQUEST_URI'));

        if (strpos($requestURI, 'table-inner-html') !== false) {
            return $tableColumns;
        }

        $newColumn = [
            'key'         => 'nt_product_compare',
            'name'        => '<span class="ninja-compare-header"><input type="checkbox" class="ninja-compare-checkbox-toggle"/><span style="opacity: 0">#</span></span>',
            'type'        => 'text',
            'filterable'  => false,
            'sortable'    => false,
            'breakpoints' => false
        ];

        array_unshift($tableColumns, $newColumn);

        return $tableColumns;
    }

    public function isBulkActionEnabled($tableId)
    {
        $appreanceSettings = get_post_meta(
            $tableId,
            '_ninja_table_fct_appearance_settings',
            true
        );

        return Arr::get($appreanceSettings, 'show_bulk_actions', 'no') == 'yes';
    }
}
