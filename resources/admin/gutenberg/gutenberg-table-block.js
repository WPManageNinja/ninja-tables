const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
const {
    PanelBody,
    SelectControl,
    Placeholder,
    TabPanel,
    ToggleControl,
    RadioControl,
    CheckboxControl,
    TextControl,
    RangeControl,
    ButtonGroup,
    Button,
    Tooltip
} = wp.components;

import {tableLibs} from "../data/data";
import Rest from "../Bits/Rest";
const { useState, useEffect } = wp.element;


registerBlockType('ninja-tables/table-block', {
    title: __('Ninja Tables'),
    icon: 'grid-view',
    category: 'widgets',
    keywords: [__('table'), __('ninja'), __('data')],
    attributes: {
        tableId: {
            type: 'string',
            default: ''
        },
        dataSource: {
            type: 'string',
            default: ''
        },
        activeDesign: {
            type: 'string',
            default: 'features'
        },
        tableSettings: {
            type: 'object',
            default: {}
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        const { tableId, activeDesign } = attributes;

        const [tableConfig, setTableConfig] = useState(null);
        const [tableInnerHtml, setTableInnerHtml] = useState('');
        const [formattedColumns, setFormattedColumns] = useState([]);
        const [isLoading, setIsLoading] = useState(false);
        const [scriptLoaded, setScriptLoaded] = useState(false);
        const [dataLoaded, setDataLoaded] = useState(false);

        const blockProps = useBlockProps();

        const availableTables = window.ninja_table_admin ?
            window.ninja_table_admin.availableTables : [];

        const has_pro = !!window.ninja_table_admin?.hasPro;

        // Default settings
        const defaultSettings = {
            show_all: '0',
            perPage: 10,
            pagination_position: 'right',
            paginate_to_top: false,
            show_pager: false,
            paze_sizes: '10,20,50,100',
            search_position: '',
            nt_search_full_width: false,
            sorting_type: '',
            default_sorting: 'old_first',
            sorting_column: '',
            sorting_column_by: 'ASC',
            expand_type: 'default',
            togglePosition: 'first',
            extra_css_class: '',
            sticky_first_column: 'no',
            sticky_header: 'no',
            sticky_header_offset: '0',
            disable_sticky_on_mobile: 'no',
            table_font_family: 'inherit',
            table_font_size: 16
        };

        // Merge default settings with table config settings
        const tableSettings = tableConfig?.settings ?
            { ...defaultSettings, ...tableConfig.settings } :
            defaultSettings;

        useEffect(() => {
            if (tableId) {
                fetchConfig(tableId);
            }
            loadRequiredScripts();
        }, [tableId]);

        useEffect(() => {
            if (scriptLoaded && dataLoaded && tableInnerHtml) {
                reInitFootables();
            }
        }, [scriptLoaded, dataLoaded, tableInnerHtml]);

        const handleTableSelect = (selectedTableId) => {
            const selectedTable = availableTables.find(table => table.value == selectedTableId);
            setAttributes({
                tableId: selectedTableId,
                dataSource: selectedTable?.data_source || ''
            });
        };

        const renderStyles = () => {
            return (
                <style>
                    {`
        .striped > tbody > :nth-child(odd) {
          background: transparent;
        }

        .footable_parent.ninja_device_mobile {
          width: 480px;
          margin: 0 auto;
        }

        .footable_parent.ninja_device_tablet {
          max-width: 768px;
          padding: 0px 20px;
          margin: 0 auto;
        }

        .footable_parent .footable-header th, .footable_parent .footable-paging {
           font-size: var(--ninja-table-font-size);
        }
        
        .font-setting .font {
          margin-top: 10px;
        }
        
        .font-setting .font label {
          width: 30%;
        }
        
        .ninja_footable tbody tr td {
          font-size: var(--ninja-table-font-size);
          font-family: var(--ninja-table-font-family);
        }
      `}
                </style>
            );
        };

        const fetchConfig = (tableId) => {
            setIsLoading(true);
            Rest.get(`settings/${tableId}`)
                .then(res => {
                    setTableConfig(res);
                    formatColumns(res.columns);
                    fetchTableBody(tableId);
                })
                .catch(err => {
                    console.error('Error fetching config:', err);
                    setIsLoading(false);
                });
        };

        const fetchTableBody = (tableId) => {
            Rest.get(`tables/${tableId}/table-inner-html`)
                .then(response => {
                    setTableInnerHtml(response);
                    setDataLoaded(true);
                    setIsLoading(false);
                })
                .catch(error => {
                    console.error('Error loading table:', error);
                    setIsLoading(false);
                });
        };

        const formatColumns = (columns) => {
            if (!columns) return;

            const formatted = columns.map((column, index) => ({
                name: column.key,
                title: column.name,
                breakpoints: column.breakpoints,
                type: column.data_type,
                sortable: true,
                classes: ['ninja_column_' + index],
                visible: (column.breakpoints == 'hidden') ? false : true
            }));

            setFormattedColumns(formatted);
        };

        const loadRequiredScripts = () => {
            if (typeof FooTable !== 'undefined') {
                setScriptLoaded(true);
                return;
            }

            const scripts = window.ninja_table_admin?.preview_required_scripts || [];

            scripts.forEach(script => {
                const element = document.createElement(script.endsWith('.css') ? 'link' : 'script');

                if (script.endsWith('.css')) {
                    element.rel = 'stylesheet';
                    element.href = script;
                } else {
                    element.src = script;
                    element.onload = () => {
                        if (typeof FooTable !== 'undefined') {
                            setScriptLoaded(true);
                        }
                    };
                }

                document.head.appendChild(element);
            });
        };

        const reInitFootables = (updatedSettings = null) => {
            if (!scriptLoaded || !dataLoaded) return;

            const appReady = dataLoaded && scriptLoaded;
            if (!appReady) return;

            if (typeof FooTable === 'object') {
                const ft = FooTable.get(`#footable_${tableId}`);
                if (ft) {
                    ft.destroy();
                }
            }

            const $table = jQuery(`#footable_${tableId}`);
            $table.find('thead,tbody,tfoot').remove();
            $table.append(tableInnerHtml);

            // Pass the updated settings (if available)
            initFootables(updatedSettings);
        };


        const initFootables = (updatedSettings = null) => {
            if (!scriptLoaded) return;

            const NinjaTableApp = window.ninjaTableApp;
            const $table = jQuery(`#footable_${tableId}`);

            // Use updated settings if provided, otherwise use the state
            const settings = updatedSettings || tableSettings;

            if (settings.hide_on_empty) {
                $table.on('expanded.ft.row', function (e, ft, row) {
                    $table.find('table.footable-details td:empty').parent().addClass('nt_has_hide');
                });
            }

            const config = getTableConfig(settings);
            NinjaTableApp.initTable($table, config);
        };

        const getTableConfig = (customSettings = null) => {
            if (!tableConfig) return {};

            // Use provided settings or fall back to state
            const settings = customSettings || tableConfig.settings || {};
            const columns = tableConfig.columns || [];

            const customCss = {};
            columns.forEach((column, index) => {
                customCss[`ninja_column_${index}`] = {
                    'text-align': column.textAlign,
                    'width': `${column.width}px`
                };
            });

            const tableSettings = {
                default_sorting: settings.default_sorting || 'old_first',
                defaut_filter: false,
                defaut_filter_column: null,
                expandAll: settings.expand_type === "expandAll",
                expandFirst: settings.expand_type === "expandFirst",
                filtering: !!settings.enable_search,
                i18n: {},
                use_parent_width: false,
                sorting: !!settings.column_sorting,
                togglePosition: settings.togglePosition
            };

            const initConfig = {
                toggleColumn: settings.togglePosition,
                cascade: true,
                useParentWidth: false,
                columns: columns,
                expandFirst: settings.expand_type === "expandFirst",
                expandAll: settings.expand_type === "expandAll",
                empty: '',
                filtering: {
                    enabled: !!settings.enable_search
                },
                paging: {
                    // Use the passed settings (which may include the latest changes)
                    enabled: settings.show_all !== '1' && settings.show_all !== 1,
                    size: parseInt(settings.perPage || 10),
                    container: `#footable_parent_${tableId} .paging-ui-container`,
                },
                sorting: {
                    enabled: !!settings.column_sorting
                },
            };

            return {
                columns: formattedColumns.map(item => Object.assign({}, item)),
                custom_css: customCss,
                settings: tableSettings,
                render_type: 'legacy_table',
                instance_name: 'ninja_table_instance_0',
                table_id: tableId,
                title: '',
                init_config: initConfig
            };
        };

        const getWrapperClasses = () => {
            if (!tableConfig?.settings) return '';

            const settings = tableConfig.settings;
            const classes = [];

            if (settings.css_lib) {
                classes.push(settings.css_lib);
            }

            if (settings.table_color_type === 'custom_color' ||
                (settings.table_color && settings.table_color !== 'ninja_no_color_table')) {
                classes.push('colored_table');
            }

            return classes.join(' ');
        };

        const getTableClasses = () => {
            if (!tableConfig?.settings) return 'table foo-table ninja_footable';

            const settings = tableConfig.settings;
            let classes = ['table', 'foo-table', 'ninja_footable'];

            // Add table ID class
            if (tableId) {
                classes.push(`foo_table_${tableId}`);
            }

            // Handle color settings
            if (settings.table_color_type === 'custom_color') {
                classes.push('inverted');
                classes.push('ninja_custom_color');
            } else {
                if (settings.table_color && settings.table_color !== 'ninja_no_color_table') {
                    classes.push('inverted');
                    classes.push(settings.table_color);
                }
            }

            // Pagination position
            if (settings.pagination_position) {
                classes.push(`footable-paging-${settings.pagination_position}`);
            } else {
                classes.push('footable-paging-right');
            }

            // Header and borders
            if (settings.hide_header_row) {
                classes.push('ninjatable_hide_header_row');
            }
            if (settings.hide_all_borders) {
                classes.push('hide_all_borders');
            }

            // Pro features
            if (has_pro) {
                classes.push('ninja_table_pro');
            }

            // Search position
            if (settings.search_position) {
                classes.push(`ninja_search_${settings.search_position}`);
            }

            // Responsive labels
            if (settings.hide_responsive_labels) {
                classes.push('nt_hide_breakpoint_labels');
            }

            // Full-width search
            if (settings.nt_search_full_width) {
                classes.push('nt_search_full_width');
            }

            // Semantic UI specific class
            if (settings.css_lib === 'semantic_ui') {
                classes.push('ui');
            }

            // Add CSS classes from styles (requires computing available CSS classes)
            let table_css_classes = [];
            if (settings.css_classes && Array.isArray(settings.css_classes)) {
                const availableCssClasses = getAvailableCssClasses();
                table_css_classes = availableCssClasses.filter(value =>
                    settings.css_classes.indexOf(value) !== -1
                );
            }

            // Combine all classes (put style classes first, then other classes)
            return [...table_css_classes, ...classes].join(' ');
        };

// Helper function to compute available CSS classes - similar to Vue's availableCssClasses computed property
        const getAvailableCssClasses = () => {
            // Early return if we don't have tableConfig or css_lib not set
            if (!tableConfig?.settings?.css_lib || !tableConfig?.settings?.library) {
                return [];
            }

            const libs = tableLibs();
            const currentLib = libs[tableConfig.settings.library]?.css_libs?.[tableConfig.settings.css_lib];

            if (!currentLib || !currentLib.styles) {
                return [];
            }

            // Extract css class keys from styles array
            return currentLib.styles.map(style => style.key);
        };

        const getFontStyle = () => {
            if (!tableConfig?.settings) return {};

            const settings = tableConfig.settings;
            return {
                '--ninja-table-font-family': settings.table_font_family || 'inherit',
                '--ninja-table-font-size': `${settings.table_font_size || 16}px`
            };
        };

        const updateTableSettings = (key, value, isReload = true) => {
            const newSettings = {
                ...tableSettings,
                [key]: value
            };

            // Update table config state
            if (tableConfig) {
                setTableConfig({
                    ...tableConfig,
                    settings: newSettings
                });
            }

            // IMPORTANT: Save to block attributes
            setAttributes({
                tableSettings: newSettings
            });

            const updatedConfig = {
                ...tableConfig,
                settings: {
                    ...tableConfig.settings,
                    [key]: value
                }
            };

            setTableConfig(updatedConfig);

            if (isReload) {
                setTimeout(() => {
                    reInitFootables(newSettings);
                }, 50);
            }
        };

        const saveSettings = async (newSettings) => {
            try {
                await Rest.post(`settings/${tableId}`, {
                    columns: tableConfig?.columns || [],
                    table_settings: newSettings
                });
            } catch (error) {
                console.error('Error saving settings:', error);
            }
        };

        const renderStylingLibrarySection = () => {
            // Get the current table libraries for the selected library
            const libs = tableLibs();
            const currentTableLibs = libs[tableSettings.library]?.css_libs || {};

            // Convert library data to options for RadioControl
            const libraryOptions = Object.entries(currentTableLibs).map(([key, lib]) => ({
                label: (
                    <span>
                {lib.title}
                        <span
                            className="dashicons dashicons-info tooltip-icon"
                            style={{ marginLeft: '5px', fontSize: '16px', cursor: 'help' }}
                            title={lib.description}
                        ></span>
            </span>
                ),
                value: key
            }));

            return (
                <div className="form_group">
                    <h3 className="ninja_inner_title">{__('Select Styling Library')}</h3>
                    <RadioControl
                        selected={tableSettings.css_lib}
                        options={libraryOptions}
                        onChange={(value) => updateTableSettings('css_lib', value)}
                    />
                </div>
            );
        };

        // Function to render the Styles section
        const renderStylesSection = () => {
            // Get available styles for the selected library and CSS lib
            const libs = tableLibs();
            const currentLib = libs[tableSettings.library]?.css_libs?.[tableSettings.css_lib];
            const availableStyles = currentLib?.styles || [];

            // If no styles are available, return null
            if (availableStyles.length === 0) {
                return null;
            }

            return (
                <div className="form_group label-normalize">
                    <h3 className="ninja_inner_title">{__('Styles')}</h3>
                    <div className="styles-checkboxes">
                        {availableStyles.map(style => (
                            <div key={style.key} className="style-checkbox-row">
                                <CheckboxControl
                                    label={
                                        <span>
                                            {style.title}
                                            <Tooltip text={__(style.description)}>
                                                <span className="dashicons dashicons-info"
                                                      style={{marginLeft: '5px', fontSize: '16px'}}></span>
                                            </Tooltip>
                                        </span>
                                    }
                                    checked={(tableSettings.css_classes || []).includes(style.key)}
                                    onChange={(checked) => {
                                        let cssClasses = Array.isArray(tableSettings.css_classes) ?
                                            [...tableSettings.css_classes] : [];

                                        if (checked) {
                                            if (!cssClasses.includes(style.key)) {
                                                cssClasses.push(style.key);
                                            }
                                        } else {
                                            cssClasses = cssClasses.filter(cls => cls !== style.key);
                                        }

                                        updateTableSettings('css_classes', cssClasses);
                                    }}
                                />
                            </div>
                        ))}
                    </div>
                </div>
            );
        };

        const renderFeaturesSection = () => {
            const libs = tableLibs();

            return (
                <div className="form_group label-normalize">
                    <h3 className="ninja_inner_title">{__('Features')}</h3>

                    <CheckboxControl
                        label={__('Show Table Title')}
                        checked={tableSettings.show_title}
                        onChange={(value) => updateTableSettings('show_title', value)}
                        help={__('Enable this if you want to show table title in frontend')}
                    />

                    <CheckboxControl
                        label={__('Show Table Description')}
                        checked={tableSettings.show_description}
                        onChange={(value) => updateTableSettings('show_description', value)}
                        help={__('Enable this if you want to show table description in frontend')}
                    />

                    <CheckboxControl
                        label={__('Enable the visitor to filter or search the table')}
                        checked={tableSettings.enable_search}
                        onChange={(value) => updateTableSettings('enable_search', value)}
                    />

                    {libs[tableSettings.library]?.supports?.sorting && !tableSettings.enable_ajax && (
                        <CheckboxControl
                            label={__('Enable sorting of the table by the visitor')}
                            checked={tableSettings.column_sorting}
                            onChange={(value) => updateTableSettings('column_sorting', value)}
                        />
                    )}

                    <CheckboxControl
                        label={__('Hide Header Row')}
                        checked={tableSettings.hide_header_row}
                        onChange={(value) => updateTableSettings('hide_header_row', value)}
                    />

                    <CheckboxControl
                        label={__('Hide All Borders')}
                        checked={tableSettings.hide_all_borders}
                        onChange={(value) => updateTableSettings('hide_all_borders', value)}
                    />

                    <CheckboxControl
                        label={
                            <span>
                        {__('Hide empty items on responsive breakdown')}
                                {!has_pro && <span> ({__('Pro Only')})</span>}
                    </span>
                        }
                        checked={tableSettings.hide_on_empty}
                        onChange={(value) => updateTableSettings('hide_on_empty', value)}
                        disabled={!has_pro}
                        help={__('If You enable this then the empty items will not show into responsive drawer / Stackable View')}
                    />

                    <CheckboxControl
                        label={
                            <span>
                        {__('Hide Labels on responsive breakdown')}
                                {!has_pro && <span> ({__('Pro Only')})</span>}
                    </span>
                        }
                        checked={tableSettings.hide_responsive_labels}
                        onChange={(value) => updateTableSettings('hide_responsive_labels', value)}
                        disabled={!has_pro}
                        help={__('If You enable this then columns headings will not show into responsive drawer / Stackable View')}
                    />
                </div>
            );
        };
        const renderStackableConfigSection = () => {
            return (
                <div className="form_group label-normalize">
                    <h3 className="ninja_inner_title">
                        {__('Stackable Table Configuration')}
                        <Tooltip text={__('With stackable table, You can show your rows as list item. You can target by device width')}>
                            <span className="dashicons dashicons-info" style={{ marginLeft: '5px', fontSize: '16px' }}></span>
                        </Tooltip>
                    </h3>

                    <div className="form_group">
                        <ToggleControl
                            label={__('Enable Stackable Table')}
                            checked={tableSettings.stackable === 'yes'}
                            onChange={(value) => updateTableSettings('stackable', value ? 'yes' : 'no', false)}
                        />

                        {tableSettings.stackable === 'yes' && (
                            <>
                                <h3 style={{ marginTop: '15px' }} className="ninja_inner_title">
                                    {__('Target Devices')}
                                    <Tooltip text={__('Select the device by width in where the stackable tables will be enabled')}>
                                        <span className="dashicons dashicons-info" style={{ marginLeft: '5px', fontSize: '16px' }}></span>
                                    </Tooltip>
                                </h3>

                                <CheckboxControl
                                    label={__('Mobile Device')}
                                    checked={(tableSettings.stacks_devices || []).includes('xs')}
                                    onChange={(checked) => {
                                        const devices = Array.isArray(tableSettings.stacks_devices) ?
                                            [...tableSettings.stacks_devices] : [];
                                        if (checked) {
                                            if (!devices.includes('xs')) {
                                                devices.push('xs');
                                            }
                                        } else {
                                            const index = devices.indexOf('xs');
                                            if (index !== -1) {
                                                devices.splice(index, 1);
                                            }
                                        }
                                        updateTableSettings('stacks_devices', devices, false);
                                    }}
                                />

                                <CheckboxControl
                                    label={__('Tablet Device')}
                                    checked={(tableSettings.stacks_devices || []).includes('sm')}
                                    onChange={(checked) => {
                                        const devices = Array.isArray(tableSettings.stacks_devices) ?
                                            [...tableSettings.stacks_devices] : [];
                                        if (checked) {
                                            if (!devices.includes('sm')) {
                                                devices.push('sm');
                                            }
                                        } else {
                                            const index = devices.indexOf('sm');
                                            if (index !== -1) {
                                                devices.splice(index, 1);
                                            }
                                        }
                                        updateTableSettings('stacks_devices', devices, false);
                                    }}
                                />

                                <CheckboxControl
                                    label={__('Laptop')}
                                    checked={(tableSettings.stacks_devices || []).includes('md')}
                                    onChange={(checked) => {
                                        const devices = Array.isArray(tableSettings.stacks_devices) ?
                                            [...tableSettings.stacks_devices] : [];
                                        if (checked) {
                                            if (!devices.includes('md')) {
                                                devices.push('md');
                                            }
                                        } else {
                                            const index = devices.indexOf('md');
                                            if (index !== -1) {
                                                devices.splice(index, 1);
                                            }
                                        }
                                        updateTableSettings('stacks_devices', devices, false);
                                    }}
                                />

                                <CheckboxControl
                                    label={__('Large Devices (imac)')}
                                    checked={(tableSettings.stacks_devices || []).includes('lg')}
                                    onChange={(checked) => {
                                        const devices = Array.isArray(tableSettings.stacks_devices) ?
                                            [...tableSettings.stacks_devices] : [];
                                        if (checked) {
                                            if (!devices.includes('lg')) {
                                                devices.push('lg');
                                            }
                                        } else {
                                            const index = devices.indexOf('lg');
                                            if (index !== -1) {
                                                devices.splice(index, 1);
                                            }
                                        }
                                        updateTableSettings('stacks_devices', devices, false);
                                    }}
                                />

                                <h3 style={{ marginTop: '15px' }} className="ninja_inner_title">
                                    {__('Stacked Appearance')}
                                    <Tooltip text={__('You can customize the appearance in stacked view of your table')}>
                                        <span className="dashicons dashicons-info" style={{ marginLeft: '5px', fontSize: '16px' }}></span>
                                    </Tooltip>
                                </h3>

                                <CheckboxControl
                                    label={__('Hide column headings')}
                                    checked={(tableSettings.stacks_appearances || []).includes('hide_stacked_th')}
                                    onChange={(checked) => {
                                        const appearances = Array.isArray(tableSettings.stacks_appearances) ?
                                            [...tableSettings.stacks_appearances] : [];
                                        if (checked) {
                                            if (!appearances.includes('hide_stacked_th')) {
                                                appearances.push('hide_stacked_th');
                                            }
                                        } else {
                                            const index = appearances.indexOf('hide_stacked_th');
                                            if (index !== -1) {
                                                appearances.splice(index, 1);
                                            }
                                        }
                                        updateTableSettings('stacks_appearances', appearances, false);
                                    }}
                                />

                                <CheckboxControl
                                    label={__('Hide internal borders')}
                                    checked={(tableSettings.stacks_appearances || []).includes('ninja_stacked_no_cell_border')}
                                    onChange={(checked) => {
                                        const appearances = Array.isArray(tableSettings.stacks_appearances) ?
                                            [...tableSettings.stacks_appearances] : [];
                                        if (checked) {
                                            if (!appearances.includes('ninja_stacked_no_cell_border')) {
                                                appearances.push('ninja_stacked_no_cell_border');
                                            }
                                        } else {
                                            const index = appearances.indexOf('ninja_stacked_no_cell_border');
                                            if (index !== -1) {
                                                appearances.splice(index, 1);
                                            }
                                        }
                                        updateTableSettings('stacks_appearances', appearances, false);
                                    }}
                                />
                            </>
                        )}
                    </div>
                </div>
            );
        };
        const renderTable = () => {
            if (!tableConfig || isLoading || !scriptLoaded) return null;

            const appReady = dataLoaded && scriptLoaded;

            return (
                <div className="ninja_design_wrapper">
                    {renderStyles()}
                    <div className="design_preview" style={{ background: 'white', padding: '10px 20px' }}>
                        {tableSettings.show_title && tableConfig.table?.post_title && (
                            <h3 className="table_title footable_title">
                                {tableConfig.table.post_title}
                            </h3>
                        )}

                        {tableSettings.show_description && tableConfig.table?.post_content && (
                            <div
                                className="table_description footable_description"
                                dangerouslySetInnerHTML={{ __html: tableConfig.table.post_content }}
                            />
                        )}
                        <div
                            id={`footable_parent_${tableId}`}
                            className={`footable_parent ninja_table_wrapper loading_ninja_table wp_table_data_press_parent ${getWrapperClasses()}`}
                        >
                            <table
                                id={`footable_${tableId}`}
                                className={getTableClasses()}
                                style={getFontStyle()}
                                display={appReady ? 'block' : 'none'}
                            >
                                <colgroup>
                                    {formattedColumns.map((column, columnIndex) => (
                                        <col
                                            key={columnIndex}
                                            className={`ninja_column_${columnIndex} ${column.breakpoints || ''}`}
                                        />
                                    ))}
                                </colgroup>
                                <thead></thead>
                            </table>
                        </div>
                    </div>
                </div>
            );
        };

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Table Settings')} initialOpen={true}>
                        <SelectControl
                            label={__('Select Table')}
                            value={tableId}
                            options={availableTables}
                            onChange={handleTableSelect}
                        />
                    </PanelBody>

                    {tableId && tableConfig && (
                        <PanelBody title={__('Design Controls')} initialOpen={false}>
                            <TabPanel
                                className="ninja-tables-design-tabs"
                                activeClass="is-active"
                                tabs={[
                                    {
                                        name: 'styling',
                                        title: __('Styling'),
                                        className: 'tab-styling'
                                    },
                                    {
                                        name: 'colors',
                                        title: __('Colors'),
                                        className: 'tab-colors'
                                    },
                                    {
                                        name: 'other',
                                        title: __('Other'),
                                        className: 'tab-other'
                                    }
                                ]}
                                onSelect={(tabName) => setAttributes({ activeDesign: tabName })}
                                initialTabName={activeDesign}
                            >
                                {(tab) => {
                                    switch (tab.name) {
                                        case 'styling':
                                            return (
                                                <div className="ninja-tab-content">
                                                    {renderStylingLibrarySection()}
                                                    {renderStylesSection()}
                                                    {renderFeaturesSection()}
                                                    {renderStackableConfigSection()}
                                                </div>
                                            );
                                        case 'colors':
                                            return (
                                                <div className="ninja-tab-content">
                                                    <h3>{__('Table Colors Tab Content')}</h3>
                                                    <p>{__('Color options will go here')}</p>
                                                </div>
                                            );
                                        case 'other':
                                            return (
                                                <div className="ninja-tab-content">
                                                    {/* Hide Pagination Toggle */}
                                                    <div className="ninja_switch_wrapper">
                                                        <ToggleControl
                                                            label={__('Hide Pagination (Show all data at once)')}
                                                            checked={tableSettings.show_all == '1'}
                                                            onChange={(val) => updateTableSettings('show_all', val ? '1' : '0')}
                                                        />
                                                    </div>

                                                    {/* Pagination Settings */}
                                                    {tableSettings.show_all !== '1' && (
                                                        <div>
                                                            <div className="form_group">
                                                                <TextControl
                                                                    label={__('Pagination Items Per Page')}
                                                                    type="number"
                                                                    value={tableSettings.perPage}
                                                                    onChange={(val) => updateTableSettings('perPage', val)}
                                                                    disabled={tableSettings.show_all === '1'
                                                                    }
                                                                />
                                                            </div>

                                                            <RadioControl
                                                                label={__('Pagination Position')}
                                                                selected={tableSettings.pagination_position}
                                                                options={[
                                                                    { label: __('Left'), value: 'left' },
                                                                    { label: __('Center'), value: 'center' },
                                                                    { label: __('Right'), value: 'right' }
                                                                ]}
                                                                onChange={(val) => updateTableSettings('pagination_position', val)}
                                                                disabled={tableSettings.show_all === '1'}
                                                            />

                                                            <CheckboxControl
                                                                label={
                                                                    <span>
                                                                        {__('Scroll to table top for pagination change')}
                                                                        {!has_pro && <span> ({__('Pro Only')})</span>}
                                                                    </span>
                                                                }
                                                                checked={tableSettings.paginate_to_top}
                                                                onChange={(val) => updateTableSettings('paginate_to_top', val, false)}
                                                                disabled={!has_pro}
                                                                help={__('If you enable this then on pagination change, the table will be scrolled to top')}
                                                            />

                                                            <CheckboxControl
                                                                label={
                                                                    <span>
                                                                        {__('Show Page sizes change option')}
                                                                        {!has_pro && <span> ({__('Pro Only')})</span>}
                                                                    </span>
                                                                }
                                                                checked={tableSettings.show_pager}
                                                                onChange={(val) => updateTableSettings('show_pager', val)}
                                                                disabled={!has_pro}
                                                                help={__('If you enable this then Users can change the items per page on frontend')}
                                                            />

                                                            {tableSettings.show_pager && (
                                                                <TextControl
                                                                    label={__('Page Sizes (Number as Comma Separated)')}
                                                                    value={tableSettings.paze_sizes}
                                                                    onChange={(val) => updateTableSettings('paze_sizes', val, false)}
                                                                    placeholder="Default: 10,20,50,100"
                                                                />
                                                            )}
                                                        </div>
                                                    )}

                                                    {/* Search Bar Settings */}
                                                    <div className="form_group">
                                                        <RadioControl
                                                            label={__('Search Bar Position')}
                                                            selected={tableSettings.search_position}
                                                            options={[
                                                                { label: __('Left'), value: 'left' },
                                                                { label: __('Center'), value: 'center' },
                                                                { label: __('Right'), value: 'right' },
                                                                { label: __('Default'), value: '' }
                                                            ]}
                                                            onChange={(val) => updateTableSettings('search_position', val)}
                                                            disabled={!has_pro}
                                                        />

                                                        <CheckboxControl
                                                            label={
                                                                <span>
                                                                    {__('Make search input as full width')}
                                                                    {!has_pro && <span> ({__('Pro Only')})</span>}
                                                                </span>
                                                            }
                                                            checked={tableSettings.nt_search_full_width}
                                                            onChange={(val) => updateTableSettings('nt_search_full_width', val)}
                                                            disabled={!has_pro}
                                                            help={__('If You enable this, Then the search input will take all the available space (100% width)')}
                                                        />
                                                    </div>

                                                    {/* Sorting Settings */}
                                                    <div className="form_group">
                                                        <label style={{ display: 'block', marginBottom: '8px' }}>{__('Select Sorting Method')}</label>
                                                        <ButtonGroup>
                                                            {tableConfig?.table?.isCreatedSortable && (
                                                                <Button
                                                                    isPressed={tableSettings.sorting_type === 'by_created_at'}
                                                                    onClick={() => updateTableSettings('sorting_type', 'by_created_at', false)}
                                                                >
                                                                    {__('By Created at')}
                                                                </Button>
                                                            )}
                                                            <Button
                                                                isPressed={tableSettings.sorting_type === 'by_column'}
                                                                onClick={() => updateTableSettings('sorting_type', 'by_column', false)}
                                                            >
                                                                {__('By Column')}
                                                            </Button>
                                                            {tableConfig?.table?.isSortable && (
                                                                <Button
                                                                    isPressed={tableSettings.sorting_type === 'manual_sort'}
                                                                    onClick={() => updateTableSettings('sorting_type', 'manual_sort', false)}
                                                                >
                                                                    {__('Manual Sort')}
                                                                </Button>
                                                            )}
                                                        </ButtonGroup>

                                                        {tableConfig?.table?.isCreatedSortable && tableSettings.sorting_type === 'by_created_at' && (
                                                            <SelectControl
                                                                label={__('Sort Type')}
                                                                value={tableSettings.default_sorting}
                                                                options={[
                                                                    { label: __('Show New Items First'), value: 'new_first' },
                                                                    { label: __('Show Old Items First'), value: 'old_first' }
                                                                ]}
                                                                onChange={(val) => updateTableSettings('default_sorting', val, false)}
                                                            />
                                                        )}

                                                        {tableSettings.sorting_type === 'by_column' && (
                                                            <>
                                                                <SelectControl
                                                                    label={__('Select Column')}
                                                                    value={tableSettings.sorting_column}
                                                                    options={tableConfig?.columns?.map(column => ({
                                                                        label: column.name,
                                                                        value: column.key
                                                                    })) || []}
                                                                    onChange={(val) => updateTableSettings('sorting_column', val, false)}
                                                                />
                                                                <SelectControl
                                                                    label={__('Sort Type')}
                                                                    value={tableSettings.sorting_column_by}
                                                                    options={[
                                                                        { label: __('Ascending Way'), value: 'ASC' },
                                                                        { label: __('Descending Way'), value: 'DESC' }
                                                                    ]}
                                                                    onChange={(val) => updateTableSettings('sorting_column_by', val, false)}
                                                                />
                                                            </>
                                                        )}

                                                        {tableConfig?.table?.isSortable && tableSettings.sorting_type === 'manual_sort' && (
                                                            <p>
                                                                {__('You can sort the table data from ')}<strong>{__('Table Rows')}</strong>{__(' Manually. Click Sort Manually checkbox to sort the data using drag and drop feature')}
                                                            </p>
                                                        )}

                                                        {tableSettings.sorting_type && (
                                                            <Button
                                                                isSecondary
                                                                isSmall
                                                                onClick={() => updateTableSettings('sorting_type', '', false)}
                                                            >
                                                                {__('Reset')}
                                                            </Button>
                                                        )}
                                                    </div>

                                                    {/* Row Details Settings */}
                                                    <div className="form_group">
                                                        <label style={{ display: 'block', marginBottom: '8px' }}>
                                                            {__('Row Details (Responsive drawer)')} {!has_pro && <span>({__('PRO')})</span>}
                                                        </label>
                                                        <RadioControl
                                                            selected={tableSettings.expand_type}
                                                            options={[
                                                                {
                                                                    label: __('Default'),
                                                                    value: 'default',
                                                                    help: __('Show All the responsive columns data into the responsive drawer')
                                                                },
                                                                {
                                                                    label: __('Expand First'),
                                                                    value: 'expandFirst',
                                                                    help: __('This will automatically expand the first row of the table when displayed on a device that hides any columns.')
                                                                },
                                                                {
                                                                    label: __('Expand All'),
                                                                    value: 'expandAll',
                                                                    help: __('This will automatically expand all rows of the table when displayed on a device that hides any columns.')
                                                                }
                                                            ]}
                                                            onChange={(val) => updateTableSettings('expand_type', val, false)}
                                                            disabled={!has_pro}
                                                        />
                                                    </div>

                                                    {/* Toggle Position */}
                                                    <div className="form_group">
                                                        <RadioControl
                                                            label={__('Toggle Position')}
                                                            selected={tableSettings.togglePosition}
                                                            options={[
                                                                {
                                                                    label: __('First Column'),
                                                                    value: 'first',
                                                                    help: __('If you use responsive breakdown then the '+' icon will show at the first visible column')
                                                                },
                                                                {
                                                                    label: __('Last Column'),
                                                                    value: 'last',
                                                                    help: __('If you use responsive breakdown then the '+' icon will show at the last visible column')
                                                                }
                                                            ]}
                                                            onChange={(val) => updateTableSettings('togglePosition', val, false)}
                                                        />
                                                    </div>

                                                    {/* Extra CSS Class */}
                                                    <TextControl
                                                        label={__('Extra CSS Class for the table')}
                                                        value={tableSettings.extra_css_class}
                                                        onChange={(val) => updateTableSettings('extra_css_class', val, false)}
                                                    />

                                                    {/* Sticky Settings */}
                                                    <div className="form_group">
                                                        <CheckboxControl
                                                            label={
                                                                <span>
                                                                    {__('Sticky First Column')}
                                                                    {!has_pro && <span> ({__('Pro')})</span>}
                                                                </span>
                                                            }
                                                            checked={tableSettings.sticky_first_column === 'yes'}
                                                            onChange={(val) => updateTableSettings('sticky_first_column', val ? 'yes' : 'no', false)}
                                                            disabled={!has_pro}
                                                        />

                                                        <CheckboxControl
                                                            label={
                                                                <span>
                                                                    {__('Sticky Header')}
                                                                    {!has_pro && <span> ({__('Pro')})</span>}
                                                                </span>
                                                            }
                                                            checked={tableSettings.sticky_header === 'yes'}
                                                            onChange={(val) => updateTableSettings('sticky_header', val ? 'yes' : 'no', false)}
                                                            disabled={!has_pro}
                                                        />

                                                        {tableSettings.sticky_header === 'yes' && (
                                                            <>
                                                                <TextControl
                                                                    label={__('Sticky Top Offset')}
                                                                    value={tableSettings.sticky_header_offset}
                                                                    onChange={(val) => updateTableSettings('sticky_header_offset', val, false)}
                                                                    placeholder="positive or negative number"
                                                                    help={__('Please give positive/negative number or you can provide jquery element object')}
                                                                />

                                                                <CheckboxControl
                                                                    label={__('Disable Sticky header for mobile devices')}
                                                                    checked={tableSettings.disable_sticky_on_mobile === 'yes'}
                                                                    onChange={(val) => updateTableSettings('disable_sticky_on_mobile', val ? 'yes' : 'no', false)}
                                                                    disabled={!has_pro}
                                                                />
                                                            </>
                                                        )}
                                                    </div>

                                                    {/* Font Settings */}
                                                    <div className="form_group font-setting">
                                                        <h4>{__('Table Font Setting')}</h4>
                                                        <SelectControl
                                                            label={__('Font Family')}
                                                            value={tableSettings.table_font_family}
                                                            options={[
                                                                { label: __('theme-font'), value: 'inherit' },
                                                                { label: 'cursive', value: 'cursive' },
                                                                { label: 'fantasy', value: 'fantasy' },
                                                                { label: 'monospace', value: 'monospace' },
                                                                { label: 'sans-serif', value: 'sans-serif' },
                                                                { label: 'serif', value: 'serif' },
                                                                { label: 'system-ui', value: 'system-ui' },
                                                                { label: 'ui-monospace', value: 'ui-monospace' },
                                                                { label: 'ui-rounded', value: 'ui-rounded' },
                                                                { label: 'ui-sans-serif', value: 'ui-sans-serif' },
                                                                { label: 'ui-serif', value: 'ui-serif' }
                                                            ]}
                                                            onChange={(val) => updateTableSettings('table_font_family', val, false)}
                                                        />
                                                        <RangeControl
                                                            label={__('Font Size')}
                                                            value={parseInt(tableSettings.table_font_size) || 16}
                                                            onChange={(val) => updateTableSettings('table_font_size', val, false)}
                                                            min={1}
                                                            max={50}
                                                        />
                                                    </div>
                                                </div>
                                            );
                                        default:
                                            return null;
                                    }
                                }}
                            </TabPanel>
                        </PanelBody>
                    )}
                </InspectorControls>

                <div className="ninja-tables-gutenberg-block">
                    {!tableId ? (
                        <Placeholder
                            icon="grid-view"
                            label={__('Ninja Tables')}
                            instructions={__('Select a table from the block settings in the sidebar.')}
                        >
                            <SelectControl
                                value={tableId}
                                options={availableTables}
                                onChange={handleTableSelect}
                            />
                        </Placeholder>
                    ) : (
                        renderTable()
                    )}
                </div>
            </div>
        );
    },

    save: function({ attributes }) {
        const { tableId, dataSource } = attributes;

        if (!tableId) {
            return null;
        }

        if (dataSource === 'drag_and_drop') {
            return `[ninja_table_builder id="${tableId}"]`;
        } else {
            return `[ninja_tables id="${tableId}"]`;
        }
    }
});
