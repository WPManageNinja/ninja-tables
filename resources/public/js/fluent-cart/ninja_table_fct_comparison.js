import FluentCartComparison from './FluentCartComparison';
import NinjaTableFluentCartUtils from './NinjaTableFluentCartUtils';

jQuery(document).ready($ => {
    const MAX = 20;
    const selected = {};
    const suppress = {};
    let tableSettings = null;   // ← will be set per table

    const getRowId = $row => $row.attr('class')?.match(/nt_row_id_(\S+)/)?.[1];

    const getCheckedIds = $table => $table
        .find('tbody tr:visible')
        .map((_, r) => {
            const $r = $(r);
            const id = getRowId($r);
            return id && $r.find('.ninja-compare-checkbox:checked').length ? id : null;
        })
        .get()
        .filter(Boolean);

    const init = ($table, cfg) => {
        if (cfg.provider !== 'wp_fct') return;
        if (cfg.settings?.appearance_settings?.show_bulk_actions !== 'yes') return;
        FluentCartComparison.init($table, cfg)

        const id = cfg.table_id;
        selected[id] = [];
        suppress[id] = false;
        tableSettings = cfg;   // ← store for modal & cart

        addDropdown($table, id, cfg);
        bindCheckboxes($table, id);
        selected[id] = getCheckedIds($table);
        updateUI($table, id);
    };

    const addDropdown = ($table, id, settings) => {
        const $wrapper = $table.closest('.ninja_table_wrapper');
        const $container = $wrapper.find('.ninja-bulk-actions-tr');
        if (!$container.length) return;

        $container.off('change.bulk').off('click.bulk');

        $container.on('change.bulk', '.ninja-bulk-action-select', function () {
            const action = $(this).val();
            $(this).data('action', action);
            updateVisibility($table, id, action);
        });

        $container.on('click.bulk', '.ninja-bulk-apply-btn', function () {
            const action = $container.find('.ninja-bulk-action-select').data('action');
            if (!action || !selected[id].length) return;

            const rows = getRowsData($table, id);
            if (action === 'compare') {
                FluentCartComparison.createComparisonModal(rows, tableSettings);
                clearAll($table, id);
            } else if (action === 'add_to_cart') {
                const products = rows.map(r => r.cart);
                NinjaTableFluentCartUtils._ajaxAdd(products, null, '', settings);
                clearAll($table, id);
            }
        });

        $container.find('.ninja-bulk-apply-btn').attr('data-table-id', id);
    };

    const bindCheckboxes = ($table, id) => {
        $table.off('change.bulk').on('change.bulk', '.ninja-compare-checkbox', function () {
            if (suppress[id]) return;
            const $cb = $(this);
            const $row = $cb.closest('tr');
            const rid = getRowId($row);
            if (!rid) return $cb.prop('checked', false);

            const checked = $table.find('.ninja-compare-checkbox:checked').length;
            if ($cb.prop('checked') && checked > MAX) {
                $cb.prop('checked', false);
                return;
            }

            selected[id] = getCheckedIds($table);
            updateUI($table, id);
        });

        $table.off('click.toggle').on('click.toggle', '.ninja-compare-checkbox-toggle', function (e) {
            e.stopPropagation();
            const $tog = $(this);
            setTimeout(() => {
                const want = $tog.prop('checked');
                const $rows = $table.find('tbody tr:visible').filter((_, r) => getRowId($(r)));
                suppress[id] = true;
                $rows.find('.ninja-compare-checkbox').prop('checked', false);
                if (want && selected[id].length < MAX) {
                    let cnt = 0;
                    $rows.each(function () {
                        if (cnt >= MAX) return false;
                        const $cb = $(this).find('.ninja-compare-checkbox');
                        if ($cb.is(':visible')) {
                            $cb.prop('checked', true);
                            cnt++;
                        }
                    });
                }
                suppress[id] = false;
                selected[id] = getCheckedIds($table);
                updateUI($table, id);
            }, 0);
        });
    };

    const updateVisibility = ($table, id, action) => {
        const ft = FooTable.get($table);
        if (!ft) return;
        suppress[id] = true;
        ft.rows.all.forEach(r => {
            const $row = r.$el;
            const $cb = $row.find('.ninja-compare-checkbox');
            if (!$cb.length) return;

            const $wrapper = $row.find('.nt_fct_variations_wrapper');
            const isVar = $wrapper.length > 0;
            const vid = isVar ? ($wrapper.find('.nt_fct_variations_select').val() || 0) : 0;

            const valid = action === 'add_to_cart'
                ? (!isVar || (vid && parseInt(vid, 10) > 0))
                : true;

            $cb.css('visibility', valid ? 'visible' : 'hidden')
                .prop('checked', valid ? $cb.prop('checked') : false);
        });
        suppress[id] = false;
        selected[id] = getCheckedIds($table);
        updateUI($table, id);
    };

    const getRowsData = ($table, id) => {
        const rows = [];
        const ft = FooTable.get($table);
        if (!ft) return rows;

        ft.rows.all.forEach(r => {
            const rid = getRowId(r.$el);
            if (!selected[id].includes(rid)) return;

            const findInRowOrNext = (sel) => $row.find(sel).length ? $row.find(sel) : $row.next().find(sel);
            const $row = r.$el;
            const $btn = findInRowOrNext('.nt_fct_add_cart_wrapper .nt_button');
            const pid = parseInt($btn.data('product_id')) || 0;
            const vid = parseInt($btn.data('variation_id') || 0, 10);
            const qty = parseInt($btn.data('quantity') || 1, 10);

            const productData = {product_id: pid, variation_id: vid, quantity: qty};
            const rowId = $row.attr('class').match(/nt_row_id_(\S+)/)?.[1];

            rows.push({
                cart: productData,
                row_id: rowId,
                ...r.value
            });
        });
        return rows;
    };

    const clearAll = ($table, id) => {
        suppress[id] = true;
        $table.find('.ninja-compare-checkbox').prop('checked', false);
        suppress[id] = false;
        selected[id] = [];
        updateUI($table, id);
    };

    const updateUI = ($table, id) => {
        const $wrapper = $table.closest('.ninja_table_wrapper');
        const $tog = $table.find('.ninja-compare-checkbox-toggle');
        const $boxes = $table.find('tbody tr:visible .ninja-compare-checkbox');
        const checked = $boxes.filter(':checked').length;
        const total = $boxes.length;

        $tog.prop({
            indeterminate: checked > 0 && checked < total && checked < MAX,
            checked: checked > 0 && (checked >= MAX || checked === total)
        });

        const hasSel = selected[id].length > 0;
        const action = $wrapper.find('.ninja-bulk-action-select').data('action');
        $wrapper.find('.ninja-bulk-action-select').prop('disabled', !hasSel);
        $wrapper.find('.ninja-bulk-apply-btn').prop('disabled', !hasSel || !action);
    };

    $(document).on('ninja_table_loaded', (e, $table, cfg) => {
        try {
            init($table, cfg);
        } catch (err) {
            console.error('Bulk Compare Init Error:', err);
        }
    });
});
