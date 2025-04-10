import Event from "./EventBus";
import './ninja-tables-footable-custom-event';
import './_stackable';
import mustache from './_tiny_mustace'
import unescape from 'lodash/unescape';
import _find from 'lodash/find';
import parser from './parser';
import {euFormat} from "../../common/helpers";


let $ = jQuery;
const isHTML = RegExp.prototype.test.bind(/(<([^>]+)>)/i);

let parseImageTag = function (valueOrElement) {
    let getImageName = function (str) {
        let regex = '(?:[^\\/]+)(\\.jpg|\\.png|\\.jpeg|\\.gif)';
        str = str.match(regex);
        if (str) {
            return str[0];
        }
        return str;
    }
    return valueOrElement.replace(/(<img([\w\W]+?)\>)/g, getImageName);
}

const googleTranslatorPagination = () => {
    const scripts = document.getElementsByTagName("script");
    let cb = '';
    for (var i = 0; i < scripts.length; i++) {
        if (scripts[i].src) {
            let params = (new URL(scripts[i].src)).searchParams;
            cb = params.get("cb");
            if (cb) {
                break;
            }
        }
    }
    const url = `//translate.google.com/translate_a/element.js?cb=${cb}`;
    jQuery.getScript(url);
}

export default {
    chunkResponse: [],
    chunkLoaded: false,
    initTables: function () {
        let that = this;
        this.ninjaFooTablesInstance = [];
        let footables = jQuery('table.foo-table.ninja_footable');
        if (footables.length) {
            $.each(footables, (index, table) => {
                let $table = $(table);
                let tableDataName = $table.attr('data-ninja_table_instance');
                let tableConfig = window[tableDataName];
                if (!tableConfig) {
                    return;
                }
                tableConfig.instance_name = tableDataName;
                this.initTable($table, tableConfig);
            });
        }
    },
    initTable($table, tableConfig) {
        var that = this;
        let initConfig = that.getNinjaTableConfig(tableConfig);
        tableConfig.init_config = initConfig;
        $table.on('ready.ft.table', (e, fooTable) => {
            try {
                $table.parent().removeClass('loading_ninja_table');
                that.onReadyFooTable($table, tableConfig);
                jQuery(document).trigger('ninja_table_loaded', [$table, tableConfig]);

                that.jetpackLazyImageCompatibility();
            } catch (error) {
                //console.warn(error);
            }
        })
            .on('postdraw.ft.table', (e, fooTable) => {
                if (window.google && window.google.translate) {
                    googleTranslatorPagination();
                }
                try {
                    Event.trigger(
                        'ninja-tables-apply-conditional-formatting',
                        [$table, tableConfig]
                    );
                    if ($table.find("td:contains('#colspan#')").length) {
                        $table.find("td:contains('#colspan#')").remove();
                    }
                } catch (error) {
                    console.warn(error);
                }
            })
            .on('after.ft.filtering', function (e, ft, filter) {

                $table.trigger('after_ft_filtering', [ft, filter]);

                if (filter && filter.length) {
                    $table.addClass('ninja_has_filter');
                } else {
                    let frm_elements = $table.find('.ninja-custom-filter input, .ninja-custom-filter select');
                    $.each(frm_elements, (index, frm_element) => {
                        let field_type = frm_element.type.toLowerCase();
                        switch (field_type) {
                            case "text":
                            case "password":
                            case "textarea":
                            case "hidden":
                            case "number":
                                frm_element.value = "";
                                break;
                            case "radio":
                            case "checkbox":
                                if (frm_element.checked) {
                                    frm_element.checked = false;
                                }
                                break;
                            case "select-one":
                                frm_element.selectedIndex = 0;
                                break;
                            case "select-multiple":
                                jQuery(document).trigger('ninja_table_trigger_multi_reset', {
                                    'frm_element': frm_element,
                                    '$table': $table
                                });
                                break;
                            default:
                                break;
                        }
                    });
                    $table.removeClass('ninja_has_filter');
                    $table.trigger('ninja_table_reset_filter');
                }
            })
            .on('preinit.ft.breakpoints', function (e, ft) {
                if (window.ninjaTablesCustomBreakpoints) {
                    ft.o.breakpoints = window.ninjaTablesCustomBreakpoints;
                    // {lg: 1201, md: 993, sm: 769, xs: 481}
                }
            })
            .on('before.ft.filtering', function (e, ft, filters) {
                if (filters.length) {
                    jQuery.each(filters, (filterIndex, filter) => {
                        if (filter.name === 'search') {
                            const original = filter.query._original;
                            filter.query = new FooTable.Query(
                                window.ninjaTableApp.diacriticsRemoval(filter.query._original), null, false
                            );
                            filter.query._value = original;
                        }
                    });
                }
            });

        if (tableConfig.settings.stack_config && tableConfig.settings.stack_config.stackable) {
            $(document).trigger('ninja_table_init_stackables', {
                '$table': $table,
                'tableConfig': tableConfig
            });
        }

        $table.on('click', '.ninja_table_do_column_filter', function (e) {
            e.preventDefault();
            try {
                const link = $(this);
                const filtering = FooTable.get($table).use(FooTable.Filtering);

                const linkText = link.text();
                const queryText = window.ninjaTableApp.diacriticsRemoval(linkText);

                const query = new FooTable.Query(queryText, 'AND', false, false);
                filtering.addFilter('nt_link_filter', query, [link.data('target_column')]);
                filtering.filter();
            } catch (error) {
                console.warn(error);
            }
        });

        if (tableConfig.chunks) {
            $table.on('ready.ft.table', (e, fooTable) => {
                that.loadMoreData(tableConfig, fooTable);
            });
        }

        $table.on('postdraw.ft.table', (e, ft) => {
            // if chunk exists load custom pager after chunk loaded.
            // otherwise, if no chunk, load custom pager always.
            const loadCustomPager = tableConfig.chunks ? this.chunkLoaded : true;

            if (loadCustomPager) {
                that.loadCustomPager(ft, $table, tableConfig);
            }
            // function is used to hide the responsive break points plus icon if td is empty
            this.hideOnEmptyIcons($table, tableConfig)
        });

        let $tableInstance = FooTable.init($table, initConfig, function (ft) {
            that.doAccesibility($table);
            $table.trigger('ninja_table_loaded_once', {
                initConfig: initConfig
            });

            if (tableConfig.render_type != 'ajax_table') {
                $table.find("td:contains('#colspan#')").remove();
            }
            if (tableConfig.settings.sticky_header) {
                if (
                    tableConfig.settings.disable_sticky_on_mobile != 'yes' ||
                    jQuery('body').width() > 767
                ) {
                    let offset = parseInt(tableConfig.settings.sticky_header_offset);
                    if (!offset) {
                        offset = 0;
                    }
                    setTimeout(() => {
                        jQuery('.' + tableConfig.uniqueID).stickyTableHeaders({
                            fixedOffset: offset
                        });
                    }, 1000);
                }
            }

        });

        if (!this.ninjaFooTablesInstance) {
            this.ninjaFooTablesInstance = [];
        }

        this.ninjaFooTablesInstance[tableConfig.instance_name] = $tableInstance;

        if (tableConfig.render_type != 'ajax_table' && !tableConfig?.editing?.enabled) {
            $table.find("td:contains('#colspan#')").remove();
        }
    },
    doAccesibility($table) {
        if ($table.data('post_accessible') == 'yes') {
            return;
        }
        let dropdownHeaderId = 'ninja_drop_down_id_' + $table.attr('id');
        // We will make the table as accessible
        $table.find('.footable-header th').attr('scope', 'col');
        $table.find('.footable-filtering .footable-filtering-search > .input-group > input')
            .attr('aria-label', 'Search in Table');
        $table.find('.footable-filtering th').attr('scope', 'row');
        $table.find('.footable-filtering .footable-filtering-search > .input-group > .input-group-btn button.btn-primary')
            .attr({
                type: 'submit',
                'aria-label': 'Search',
                'value': 'Search'
            });

        $table.find('.footable-filtering .footable-filtering-search > .input-group .dropdown-toggle')
            .attr({
                'aria-label': 'Search in',
                'value': 'Search in'
            })

        $table.find('.footable-filtering .footable-filtering-search > .input-group  ul.dropdown-menu li.dropdown-header')
            .attr('id', dropdownHeaderId);
        $table.find('.footable-filtering .footable-filtering-search > .input-group  ul.dropdown-menu')
            .attr({
                'role': 'group',
                'aria-labelledby': dropdownHeaderId
            });

        let $customFilters = $table.find('thead .ninja-custom-filter');
        jQuery.each($customFilters, (filterIndex, customFilter) => {
            let $customFilter = jQuery(customFilter);
            let elementType = $customFilter.attr('data-element_type');
            let filterId = 'nt_cf_' + filterIndex + '_table_' + $table.attr('data-footable_id');
            if (elementType == 'radio' || elementType == 'checkbox') {
                $customFilter.attr({
                    'role': 'group',
                    'aria-labelledby': filterId
                });
                $customFilter.find('.ninja_filter_title').attr('id', filterId);
            } else {
                let $input = $customFilter.find('select,input');
                if ($input.length) {
                    $customFilter.find('.ninja_filter_title').attr('for', filterId);
                    $($input[0]).attr('id', filterId);
                }
            }
        });

        let $pagination = $table.find('.pagination');
        if ($pagination) {
            jQuery.each($pagination.children(), (index, child) => {
                let $child = jQuery(child);
                let attr = $child.attr('data-page');

                if (attr == 'first') {
                    attr = 'first page'
                } else if (attr == 'prev') {
                    attr = 'previous';
                } else if (attr == 'prev-limit') {
                    attr = 'previous limit';
                } else if (attr == 'next-limit') {
                    attr = 'next limit';
                } else if (attr == 'next') {
                    attr = 'next';
                } else if (attr == 'last') {
                    attr = 'last page';
                } else if (attr) {
                    attr = 'page ' + attr;
                } else {
                    attr = 'page size';
                }

                $child.attr('aria-label', attr);

                $child.find('a').attr({
                    'role': 'button',
                    'aria-label': attr
                });
            })
        }

        $table.data('post_accessible', 'yes');
    },
    loadMoreData(tableConfig, fooTable) {
        this.loadChuck(1, tableConfig, fooTable);
    },
    loadChuck(counter, tableConfig, fooTable) {
        let maxChunk = tableConfig.chunks;
        if (counter <= maxChunk) {
            let uri_params = {
                action: 'wp_ajax_ninja_tables_public_action',
                table_id: tableConfig.table_id,
                target_action: 'get-all-data',
                default_sorting: tableConfig.settings.default_sorting,
                skip_rows: tableConfig.settings.skip_rows,
                limit_rows: tableConfig.settings.limit_rows,
                chunk_number: counter,
                ninja_table_public_nonce: window.ninja_footables.ninja_table_public_nonce
            };

            if (tableConfig.editing && tableConfig.editing.check_editing == 'yes') {
                uri_params.check_editing = 'yes';
                if (tableConfig.editing.own_data_only == 'yes') {
                    uri_params.own_only = 'yes';
                }
            }

            $.get(window.ninja_footables.ajax_url, uri_params)
                .then(response => {
                    this.loadChuck(counter + 1, tableConfig, fooTable);

                    if (response) {
                        // store response and load it later to avoid numerous event firing issue.
                        this.chunkResponse = this.chunkResponse.concat(response);
                    }
                })
                .then(() => {
                    if (counter === maxChunk) {
                        fooTable.rows.load(this.chunkResponse, true);
                        this.chunkLoaded = true;
                        setTimeout(() => {
                            fooTable.$el.trigger('ninja_table_all_chunk_loaded', {
                                maxChunk: maxChunk,
                            });
                        }, 500);
                    }
                })
        }
    },
    getNinjaTableConfig(tableConfig) {
        const that = this;
        const isHTML = RegExp.prototype.test.bind(/(<([^>]+)>)/i);
        let initConfig = tableConfig.init_config;

        if (initConfig.data_request_url) {
            initConfig.rows = $.get(initConfig.data_request_url);
        }

        jQuery.each(tableConfig.columns, (columnIndex, column) => {
            column.original_type = column.type;
            if (column.type === 'date') {
                column.sortValue = function (valueOrElement) {
                    if (FooTable.is.element(valueOrElement) || FooTable.is.jq(valueOrElement)) {
                        valueOrElement = jQuery(valueOrElement).text();
                    }
                    if (!valueOrElement) {
                        return 0;
                    }

                    if (column.formatString) {
                        valueOrElement = moment(valueOrElement, column.formatString).valueOf();
                    }
                    return valueOrElement;
                };
                column.type = 'numeric';
            } else if (column.type == 'numeric') {
                column.sortValue = function (valueOrElement) {
                    return that.parseNumberValue(valueOrElement, column);
                };
                column.filterValue = function (valueOrElement) {
                    return that.parseNumberValue(valueOrElement, column, true);
                };
            } else if (column.type == 'image') {
                column.sortValue = that.getTextFiltererOrSorter(true);
                column.filterValue = that.getTextFiltererOrSorter();
                if (initConfig.data_request_url) {
                    column.type = 'object';
                } else {
                    column.type = 'text';
                }

            } else {
                column.sortValue = that.getTextFiltererOrSorter(true);
                column.filterValue = that.getTextFiltererOrSorter();
                column.type = 'text';
            }

            // format the value here
            column.formatter = function (value, options, rowData) {
                if (options === true) {
                    rowData = rowData.value
                }
                if (!column.original) {
                    column.original = column;
                }
                if (column.original.data_type == 'image') {
                    value = that.parseImageColumn(value, column);
                }

                if (column.original.data_type == 'button' && initConfig.data_request_url) {
                    value = that.parseButtonColumn(value, column);
                }
                const hasTransformedValue = column.transformed_value && column.transformed_value.trim();
                if (hasTransformedValue) {
                    const allRows = (FooTable.Export).snapshot;
                    $.each(allRows, (i, row) => {
                        if (!row.value.value_transformation_complete) {
                            let sortValue = that.getShortcodes(column.transformed_value, column, row.value, tableConfig.columns);
                            let filterValue = sortValue;

                            if (tableConfig.settings.has_formula === 'yes') {
                                sortValue = filterValue = parser.parse(unescape(sortValue), column.decimalSeparator);
                            }

                            if (column.decimalSeparator === ',') {
                                filterValue = that.euFormat(transformedValue);
                            }

                            row.value[column.key] = sortValue;

                            $.each(row.cells, (cellIndex, cell) => {
                                if (cell.column.name === column.name) {
                                    cell.filterValue = filterValue;
                                    cell.sortValue = column.original_type === 'numeric' ? Number(sortValue) : sortValue;
                                }
                            })
                        }
                    })

                    value = that.getShortcodes(column.transformed_value, column, rowData, tableConfig.columns);

                    if (tableConfig.settings.has_formula == 'yes') {
                        value = parser.parse(unescape(value), column.decimalSeparator);
                    }

                    if (column.decimalSeparator == ',') {
                        value = that.euFormat(value);
                    }
                }

                return value;
            }
        });

        initConfig.columns = tableConfig.columns;

        if (tableConfig.editing && tableConfig.editing.enabled) {
            initConfig.editing = {
                "enabled": tableConfig.editing.enabled,
                "position": tableConfig.editing.position,
                "alwaysShow": tableConfig.editing.alwaysShow,
                "allowEdit": tableConfig.editing.editing,
                "allowDelete": tableConfig.editing.deleting,
                "allowView": false,
                "showText": '<span class="fooicon fooicon-pencil" aria-hidden="true"></span> ' + tableConfig.editing.showText,
                "hideText": tableConfig.editing.hideText,
                "addText": tableConfig.editing.addText,
                "column": {
                    "classes": "footable-editing",
                    "name": "____editing____",
                    "title": tableConfig.editing.editingColumnTitle,
                    "filterable": false,
                    "sortable": false
                },
                editRow(row) {
                    let self = this;
                    jQuery(document).trigger('ninja_table_edit_row', {
                        row: row,
                        self: self,
                        tableConfig: tableConfig
                    });
                },
                addRow() {
                    let self = this;
                    jQuery(document).trigger('ninja_table_add_row', {
                        self: self,
                        tableConfig: tableConfig
                    });
                },
                deleteRow(row) {
                    let self = this;
                    jQuery(document).trigger('ninja_table_delete_row', {
                        row: row,
                        self: self,
                        tableConfig: tableConfig
                    });
                }
            }
        }
        if (tableConfig.custom_filter_key) {
            let filterKey = tableConfig.custom_filter_key;
            initConfig.components = {
                filtering: FooTable[filterKey]
            };
            initConfig.filtering.enabled = true
        }

        if (tableConfig.settings.defualt_filter) {
            if (initConfig.filtering.filters.length) {
                jQuery.each(initConfig.filtering.filters, (filterIndex, filter) => {
                    filter.query = window.ninjaTableApp.diacriticsRemoval(filter.query);
                });
            }
        }

        return initConfig;
    },
    onReadyFooTable($table, tableConfig) {
        let cssStyles = tableConfig.custom_css;
        if (tableConfig.settings.extra_css_class) {
            $table.addClass(tableConfig.settings.extra_css_class);
        }

        jQuery.each(cssStyles, (className, values) => {
            $table.find('.' + className).css(values);
        });

        if (tableConfig.settings.hide_on_empty) {
            $table.on('expanded.ft.row', function (e, ft, row) {
                $table.find('table.footable-details td:empty').parent().addClass('nt_has_hide');
            });
            // We have to run this intially if all the rows are expanded by default
            $table.find('table.footable-details td:empty').parent().addClass('nt_has_hide');
        }


        if (tableConfig.settings.paginate_to_top) {
            $table.find('tfoot').on('click', '.footable-page-link', function () {
                if ($(this).text() !== '...') {
                    jQuery('html, body').animate(
                        {scrollTop: $table.offset().top}, 200
                    );
                }
            });
        }

        jQuery(document).trigger('ninja_table_ready_init', {
            '$table': $table,
            'tableConfig': tableConfig
        });

        jQuery(document).trigger('ninja_table_ready_init_table_id_' + tableConfig.table_id, {
            '$table': $table,
            'tableConfig': tableConfig
        });

        if (jQuery('.ninja_filter_date_picker,.ninja_filter_date_range').length && Pikaday) {
            let datePikers = jQuery('.ninja_filter_date_picker,.ninja_filter_date_range');
            jQuery.each(datePikers, function (index, datePiker) {
                let $piker = jQuery(datePiker);
                $piker.pikaday({
                    format: $piker.data('date_format'),
                    defaultDate: $piker.val(),
                    showTime: $piker.data('show_time'),
                    firstDay: $piker.data('first_day_of_week'),
                    i18n: ninja_footables.i18n.pikaday,
                    onOpen() {

                        $('.pika-lendar .pika-title').attr({
                            'aria-atomic': true,
                            'role': 'button'
                        });
                        $('.pika-single').attr({
                            'aria-label': "Date Picker",
                            'aria-pressed': 'true',
                            'aria-hidden': 'false'
                        });
                        $('select.pika-select.pika-select-month').attr('title', 'Select Month');
                        $('select.pika-select.pika-select-year').attr('title', 'Select Year');
                    },
                    onclose() {
                        $('.pika-single').attr({
                            'aria-label': "Date Picker",
                            'aria-pressed': 'false',
                            'aria-hidden': 'true'
                        });
                    }
                });
            });
        }

        if ($table.hasClass('nt_has_lightbox')) {
            $table.on('click', '.nt_lightbox', lity);
        }

        $table.removeClass('ninja_require_initial_hide');
        $table.parent().find('.footable-loader').remove();

        $table.find('.footable-filtering')
            .find('input')
            .attr('spellcheck', 'false')
            .attr('autocorrect', 'off')
            .attr('autocapitalize', 'off');

        $table.on('keyup', '.ninja-custom-filter input, .footable-filtering-search > .input-group > input', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        if ($table.find('tfoot').text().trim() === '') {
            $table.find('tfoot').remove();
        }
    },
    loadCustomPager(ft, $table, tableConfig) {

        if (!tableConfig.settings.pager) {
            return;
        }

        if ($table.find('.nt_customer_pager').length) {
            return;
        }

        let $tableFooterPaging = $table.find('tfoot .footable-pagination-wrapper .pagination');
        if ($tableFooterPaging.length) {
            let preselected = parseInt(ft.o.paging.size);
            if ($table.data('page-size')) {
                preselected = parseInt($table.data('page-size'));
            }
            let pagers = tableConfig.settings.page_sizes || [10, 20, 50, 100];
            if (pagers.indexOf(preselected) == -1) {
                pagers.push(preselected);
            }
            let total = ft.rows.all.length;
            let validPagers = [];
            pagers.forEach(function (number) {
                number = parseInt(number);
                if (number <= total) {
                    validPagers.push(number);
                }
            });
            validPagers.sort((a, b) => a - b);
            validPagers = $.unique(validPagers);

            let selects = '';
            $.each(validPagers, (index, value) => {
                let atts = '';
                if (preselected == value) {
                    atts = 'selected';
                }
                selects += `<option ${atts} value="${value}">${value}</option>`;
            });

            let pagerDom = $('<li>', {class: 'nt_customer_pager'}).append(
                $('<select>', {class: 'nt_pager_selection'})
                    .html(selects)
                    .on('change', function () {
                        let selectedSize = $(this).val();
                        $table.data('page-size', selectedSize);
                        FooTable.get('#footable_' + tableConfig.table_id).pageSize(selectedSize);
                    })
            );

            $tableFooterPaging.append(pagerDom);
        }
    },
    getShortcodes(str, column, row, columns) {
        let transValue = column.transformed_value;

        if (row.value_transformation_complete != true) {
            $.each(row, (key, item) => {
                if (key != '____editing____') {
                    const col = _find(columns, ['key', key]);

                    if (col && col.type === 'numeric' && col.original_type !== 'date' && col.transformed_value !== undefined) {
                        if (col.decimalSeparator === '.') {
                            row[key] = row[key] ? row[key].replace(/\,/g, '') : row[key];
                        } else {
                            row[key] = row[key] ? row[key].replace(/\./g, '').replace(/\,/g, '.') : row[key];
                        }
                    }
                }
            })

            row.value_transformation_complete = true;
        }

        if (transValue.indexOf('{{') != -1) {
            transValue = unescape(
                mustache(transValue, {row: row})
            );
        }

        // For legacy placeholder (with single curly brace)
        let singleBraces = transValue.match(/{row.([^\}]*)}/g);

        if (singleBraces) {
            $.each(singleBraces, (index, match) => {
                let rowKey = match.substring(5, match.length - 1);
                transValue = transValue.replace(match, row[rowKey] || '');
            });
        }
        return transValue;
    },
    parseImageColumn(value, column) {
        if (typeof value != 'object' || !value) {
            return value;
        }
        if (column.original.link_type == 'file_download') {
            return `<a target="_blank" target="${column.original.link_target}" href="${value.permalink}">${column.original.download_button}</a>`;
        } else {
            let prefix = '';
            let suffix = '';
            if (column.original.link_type == 'hyperlinked') {
                prefix = `<a href='${value.permalink}' target='${column.original.link_target}'>`;
                suffix = '</a>';
            } else if (column.original.link_type == 'image_light_box') {
                prefix = `<a class="nt_lightbox" href='${value.image_full}'>`;
                suffix = '</a>';
            } else if (column.original.link_type == 'iframe_ligtbox') {
                prefix = `<a class="nt_lightbox" href='${value.permalink}'>`;
                suffix = '</a>';
            }
            if (value.image_thumb) {
                return `${prefix}<img alt="${value.alt_text}" src="${value.image_thumb}" />${suffix}`;
            }
            return '';
        }
    },

    parseButtonColumn(value, column) {
        if (!value) {
            return '';
        }
        column = column['original'];

        let atts = '';
        if (column.link_target) {
            atts = 'target="' + column.link_target + '"';
        }

        let extraClass = '';
        if (column['btn_extra_class'] && column['btn_extra_class']) {
            extraClass = column['btn_extra_class'];
        }

        let styles = '';
        if (column['btn_text_color']) {
            styles += 'color: ' + column['btn_text_color'] + ';';
        }

        if (column['btn_bg_color']) {
            styles += 'background-color: ' + column['btn_bg_color'] + ';';
        }

        if (column['btn_border_color']) {
            styles += 'border-color: ' + column['btn_border_color'] + ';';
        }

        let btnText = '';
        if (column['button_text']) {
            btnText = column['button_text'];
        }

        let relAttributes = '';
        if (column['relAttributes']) {
            relAttributes = column['relAttributes'].join(" ");
        }

        let forceDownload = '';
        let url = value;
        if (column['force_download'] && column['force_download']) {
            forceDownload = `download`;
            const siteURL = ninja_footables.site_url;
            if (!url.includes(siteURL)) {
                extraClass += ' nt_force_download';
            }
        }

        return `<a ${atts} ${forceDownload} class="nt_btn ${extraClass}" style="${styles}" rel="${relAttributes}" href="${url}">${btnText}</a>`;
    },
    getTextFiltererOrSorter(sort = false) {
        return function (valueOrElement) {
            if (FooTable.is.element(valueOrElement) || FooTable.is.jq(valueOrElement)) {
                valueOrElement = this.parser(valueOrElement);
                if (!valueOrElement) {
                    return '';
                }
                if (valueOrElement.indexOf('<img ') != -1) {
                    valueOrElement = parseImageTag(valueOrElement);
                }
            }
            // Create a new div element
            var temporalDivElement = document.createElement("div");
            // Set the HTML content with the providen
            temporalDivElement.innerHTML = valueOrElement;
            // Retrieve the text property of the element (cross-browser support)
            var text = temporalDivElement.textContent || temporalDivElement.innerText || "";

            text = text.replace(/(\r\n\t|\n|\r\t|")/gm, "").trim();

            if (!sort) {
                text = window.ninjaTableApp.diacriticsRemoval(text);
                // text = parser.parse(unescape(text));
            }

            return text;
        }
    },
    parseNumberValue(valueOrElement, column, filtering = false) {
        if (FooTable.is.element(valueOrElement) || FooTable.is.jq(valueOrElement) || isHTML(valueOrElement)) {
            valueOrElement = jQuery(valueOrElement).html();
            if (valueOrElement) {
                valueOrElement = valueOrElement.replace(/<del>(.*?)<\/del>|<[^>]*>/g, '');
            }
        }
        if (!valueOrElement) {
            return '';
        }


        if (typeof (valueOrElement) != 'number') {
            valueOrElement = valueOrElement.replace(/[^0-9\.,-]+/g, "");
        } else {
            valueOrElement = valueOrElement.toString();
        }

        // for filtering a number having , & . won't be a problem
        // so we are returning that value from here. because
        // the filtering value comes from the dom and it
        // may contain , & . so those should be included.
        if (filtering) {
            return valueOrElement;
        }

        // for sorting we need to generate the proper number for european value.
        if (valueOrElement && column.decimalSeparator == ',') {
            valueOrElement = valueOrElement.replace(/\./g, '').replace(/\,/g, '.');
        } else if (column.decimalSeparator == '.') {
            valueOrElement = valueOrElement.replace(/\,/g, '');
        }
        let numberValue = Number(valueOrElement);
        if (isNaN(numberValue)) {
            return valueOrElement;
        }
        return numberValue;
    },
    jetpackLazyImageCompatibility() {
        const isEnabled = !!jQuery('.jetpack-lazy-images-js-enabled').length;

        if (isEnabled) {
            jQuery('body').trigger('jetpack-lazy-images-load');
        }
    },
    hideOnEmptyIcons($table, tableConfig) {
        if (tableConfig.settings && tableConfig.settings.hide_on_empty) {
            // Get columns that are always hidden
            const alwaysHiddenColumns = [];
            if (tableConfig.columns) {
                tableConfig.columns.forEach((column, index) => {
                    if (column?.breakpoints === "hidden") {
                        alwaysHiddenColumns.push(index);
                    }
                });
            }

            $table.find('tbody tr').each(function () {
                let singleTr = [];
                let hiddenCellIndices = [];

                // Get indices of hidden cells
                $(this).find('td').each(function (index) {
                    if ($(this).css('display') === 'none') {
                        hiddenCellIndices.push(index);
                    }
                });

                // Filter out always-hidden columns
                const responsiveHiddenIndices = hiddenCellIndices.filter(index =>
                    !alwaysHiddenColumns.includes(index)
                );

                // Check content of cells that are hidden due to responsive behavior
                $(this).find('td[style*="display: none"]').each(function (index) {
                    const cellIndex = $(this).index();
                    if (!alwaysHiddenColumns.includes(cellIndex)) {
                        singleTr.push($(this).text());
                    }
                });

                const filterSingleTr = singleTr.filter(function (el) {
                    return el !== '';
                });

                const expandValue = $(this).data('expanded');

                // Only remove expand icon if responsive hidden cells are all empty
                if (filterSingleTr.length === 0 && responsiveHiddenIndices.length > 0 && expandValue !== true) {
                    $(this).find('span.fooicon-plus').remove();
                }
            });
        }
    },

    euFormat: euFormat
}
