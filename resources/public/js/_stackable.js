jQuery(document).on('ninja_table_init_stackables', function (e, data) {
    const $ = jQuery;
    let $table = data.$table;
    let tableConfig = data.tableConfig;
    let isDrawn = false;
    let targetBreakpoints = tableConfig.settings.stack_config.stacks_devices;
    if (!targetBreakpoints) {
        return;
    }

    function isStackable(currentName) {
        return targetBreakpoints.indexOf(currentName) !== -1;
    }

    function initTableEditForStackables($table) {
        if ($table.data('stackable_edit_inited')) {
            return;
        }
        $table.on('click', '.footable-edit', function (e) {
            if ($table.hasClass('ninja_stacked_table')) {
                var originalRow = $(this).closest('tr.footable-detail-row').prev();
                var row = originalRow.data('__FooTableRow__');
                var self = {
                    ft: row.ft
                }
                jQuery(document).trigger('ninja_table_edit_row', {
                    row: row,
                    self: self,
                    tableConfig: tableConfig
                });
            }
        });

        $table.on('click', '.footable-delete', function (e) {
            if ($table.hasClass('ninja_stacked_table')) {
                var originalRow = $(this).closest('tr.footable-detail-row').prev();
                var row = originalRow.data('__FooTableRow__');
                var self = {
                    ft: row.ft
                }
                jQuery(document).trigger('ninja_table_delete_row', {
                    row: row,
                    self: self,
                    tableConfig: tableConfig
                });
            }
        });

        $table.data('stackable_edit_inited', 'yes');
    }


    $table
        .on('expand.ft.row', function (e, data, selfArg) {
            if (!isStackable(data.breakpoints.current.name)) {
                return;
            }
            e.preventDefault();
            selfArg.__hidden__ = FooTable.arr.map(selfArg.cells, function (cell) {
                return cell.column.visible ? cell : null;
            });
            if (selfArg.__hidden__.length > 0) {
                selfArg.$details.insertAfter(selfArg.$el)
                    .children('td').first()
                    .attr('colspan', selfArg.ft.columns.visibleColspan);
                FooTable.arr.each(selfArg.__hidden__, function (cell) {
                    if (isDrawn) cell.collapse();
                });
            }
            selfArg.$el.attr('data-expanded', true);
            selfArg.$toggle.removeClass('fooicon-plus').addClass('fooicon-minus');
            selfArg.expanded = true;
            selfArg.ft.raise('expanded.ft.row', [self]);
        })
        .on('collapse.ft.row', function (e, data, selfArg) {
            isDrawn = false;

            FooTable.arr.each(selfArg.__hidden__, function(cell){
                cell.restore();
            });
            selfArg.$details.detach();
            selfArg.$el.removeAttr('data-expanded');
            selfArg.$toggle.removeClass('fooicon-minus').addClass('fooicon-plus');
            selfArg.expanded = false;
            selfArg.ft.raise('collapsed.ft.row', [selfArg]);
        })
        .on('draw.ft.table', function (event, $element) {
            if (isStackable($element.breakpoints.current.name)) {
                isDrawn = true;
                $table.addClass('ninja_stacked_table');
                // We have to collapse otherwise after filtering
                // the table data won't be displayed again.
                // $element.rows.collapse();

                $element.rows.expand();
                initTableEditForStackables($table);
                $table.trigger('ninja_stacked_init');
            } else {
                isDrawn = false;
                $table.removeClass('ninja_stacked_table');

                // Commenting out the following code to solve the combination
                // of Breaking Point, Stackable & Expand All settings.

                // if(tableConfig.init_config.expandAll) {
                //     $element.rows.expand();
                // } else {
                //     $element.rows.collapse();
                // }
                
                $table.trigger('ninja_stacked_removed');
            }
        });
});
