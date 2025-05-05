const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
const {
    PanelBody,
    SelectControl,
    Placeholder,
    CheckboxControl,
    RadioControl,
    ButtonGroup,
    Button,
    TextControl,
    RangeControl,
    __experimentalInputControl: InputControl,
    ToggleControl
} = wp.components;
const { useState, useEffect } = wp.element;
import Rest from "../Bits/Rest";
import {tableLibs} from '../data/data';

// Color picker component for Gutenberg
const ColorControl = ({ label, value, onChange }) => {
    const [showPicker, setShowPicker] = useState(false);
    const [color, setColor] = useState(value || '');

    useEffect(() => {
        setColor(value || '');
    }, [value]);

    return (
        <div style={{ marginBottom: '10px' }}>
            <label style={{ display: 'block', marginBottom: '5px' }}>{label}</label>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <div
                    style={{
                        width: '30px',
                        height: '30px',
                        backgroundColor: color,
                        border: '1px solid #ccc',
                        cursor: 'pointer',
                        borderRadius: '3px'
                    }}
                    onClick={() => setShowPicker(!showPicker)}
                />
                <input
                    type="text"
                    value={color}
                    onChange={(e) => {
                        setColor(e.target.value);
                        onChange(e.target.value);
                    }}
                    placeholder="Enter color"
                    style={{ flex: 1 }}
                />
            </div>
            {showPicker && (
                <div style={{ position: 'absolute', zIndex: 100 }}>
                    <input
                        type="color"
                        value={color}
                        onChange={(e) => {
                            setColor(e.target.value);
                            onChange(e.target.value);
                        }}
                    />
                </div>
            )}
        </div>
    );
};

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
        tableSettings: {
            type: 'object',
            default: {}
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        const { tableId, tableSettings: customSettings } = attributes;

        const [tableConfig, setTableConfig] = useState(null);
        const [tableInnerHtml, setTableInnerHtml] = useState('');
        const [formattedColumns, setFormattedColumns] = useState([]);
        const [isLoading, setIsLoading] = useState(false);
        const [footableLoading, setFootableLoading] = useState(false);
        const [scriptLoaded, setScriptLoaded] = useState(false);
        const [dataLoaded, setDataLoaded] = useState(false);
        const [showingDevice, setShowingDevice] = useState('desktop');
        const [savingSettings, setSavingSettings] = useState(false);

        const blockProps = useBlockProps();

        const availableTables = window.ninja_table_admin ?
            window.ninja_table_admin.availableTables : [];

        const tableLibsData = tableLibs();
        const has_pro = !!window.ninja_table_admin?.hasPro;
        const hasSortable = !!window.ninja_table_admin?.hasSortable;

        // Default settings
        const defaultSettings = {
            library: 'bootstrap3',
            css_lib: 'bootstrap3',
            css_classes: [],
            table_color: 'ninja_no_color_table',
            table_color_type: 'pre_defined_color',
            show_title: false,
            show_description: false,
            enable_search: false,
            column_sorting: false,
            hide_header_row: false,
            hide_all_borders: false,
            show_all: '0',
            perPage: 10,
            pagination_position: 'right',
            search_position: 'right',
            sorting_type: '',
            expand_type: 'default',
            togglePosition: 'first',
            stackable: 'no',
            stacks_devices: [],
            stacks_appearances: [],
            hide_responsive_labels: false,
            hide_on_empty: false,
            nt_search_full_width: false,
            table_font_family: 'inherit',
            table_font_size: 16,
            extra_css_class: '',
            sticky_first_column: 'no',
            sticky_header: 'no',
            sticky_header_offset: '0',
            disable_sticky_on_mobile: 'no',
            alternate_color_status: 'no'
        };

        const tableSettings = tableConfig?.settings ?
            { ...defaultSettings, ...tableConfig.settings, ...customSettings } :
            { ...defaultSettings, ...customSettings };

        const currentTableLibs = tableLibsData[tableSettings.library]?.css_libs || {};

        const updateTableSettings = (key, value) => {
            setAttributes({
                tableSettings: {
                    ...tableSettings,
                    [key]: value
                }
            });
        };

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
        }, [scriptLoaded, dataLoaded, tableInnerHtml, tableSettings]);

        const handleTableSelect = (selectedTableId) => {
            const selectedTable = availableTables.find(table => table.value == selectedTableId);
            setAttributes({
                tableId: selectedTableId,
                dataSource: selectedTable?.data_source || ''
            });
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

        const reInitFootables = () => {
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
            setFootableLoading(false);
            $table.append(tableInnerHtml);
            initFootables();
        };

        const initFootables = () => {
            if (footableLoading || !scriptLoaded) return;

            setFootableLoading(true);
            const NinjaTableApp = window.ninjaTableApp;
            const $table = jQuery(`#footable_${tableId}`);

            if (tableSettings.hide_on_empty) {
                $table.on('expanded.ft.row', function (e, ft, row) {
                    $table.find('table.footable-details td:empty').parent().addClass('nt_has_hide');
                });
            }

            const config = getTableConfig();
            NinjaTableApp.initTable($table, config);
            setFootableLoading(false);
        };

        const getTableConfig = () => {
            if (!tableConfig) return {};

            const columns = tableConfig.columns || [];

            const customCss = {};
            columns.forEach((column, index) => {
                customCss[`ninja_column_${index}`] = {
                    'text-align': column.textAlign,
                    'width': `${column.width}px`
                };
            });

            const settings = {
                default_sorting: 'old_first',
                defualt_filter: false,
                defualt_filter_column: null,
                expandAll: tableSettings.expand_type === "expandAll",
                expandFirst: tableSettings.expand_type === "expandFirst",
                filtering: !!tableSettings.enable_search,
                i18n: {},
                use_parent_width: showingDevice !== 'desktop',
                sorting: !!tableSettings.column_sorting,
                togglePosition: tableSettings.togglePosition
            };

            const initConfig = {
                toggleColumn: tableSettings.togglePosition,
                cascade: true,
                useParentWidth: showingDevice !== 'desktop',
                columns: columns,
                expandFirst: tableSettings.expand_type === "expandFirst",
                expandAll: tableSettings.expand_type === "expandAll",
                empty: '',
                filtering: {
                    enabled: !!tableSettings.enable_search
                },
                paging: {
                    enabled: tableSettings.show_all === '0' || tableSettings.show_all === 0,
                    size: 10,
                    container: `#footable_parent_${tableId} .paging-ui-container`,
                },
                sorting: {
                    enabled: !!tableSettings.column_sorting
                },
            };

            return {
                columns: formattedColumns.map(item => Object.assign({}, item)),
                custom_css: customCss,
                settings: settings,
                render_type: 'legacy_table',
                instance_name: 'ninja_table_instance_0',
                table_id: tableId,
                title: '',
                init_config: initConfig
            };
        };

        const getWrapperClasses = () => {
            const classes = [
                'footable_parent',
                'ninja_table_wrapper',
                'loading_ninja_table',
                'wp_table_data_press_parent'
            ];

            if (tableSettings.css_lib) {
                classes.push(tableSettings.css_lib);
            }

            classes.push(`ninja_device_${showingDevice}`);

            if (tableSettings.table_color_type === 'custom_color' ||
                (tableSettings.table_color && tableSettings.table_color !== 'ninja_no_color_table')) {
                classes.push('colored_table');
            }

            return classes.join(' ');
        };

        const getTableClasses = () => {
            const classes = ['table', 'foo-table', 'ninja_footable'];

            if (tableId) {
                classes.push(`foo_table_${tableId}`);
            }

            if (tableSettings.table_color_type === 'custom_color') {
                classes.push('inverted', 'ninja_custom_color');
            } else if (tableSettings.table_color && tableSettings.table_color !== 'ninja_no_color_table') {
                classes.push('inverted', tableSettings.table_color);
            }

            if (tableSettings.pagination_position) {
                classes.push(`footable-paging-${tableSettings.pagination_position}`);
            } else {
                classes.push('footable-paging-right');
            }

            if (tableSettings.hide_header_row) {
                classes.push('ninjatable_hide_header_row');
            }

            if (tableSettings.hide_all_borders) {
                classes.push('hide_all_borders');
            }

            classes.push('ninja_table_pro');

            if (tableSettings.search_position) {
                classes.push(`ninja_search_${tableSettings.search_position}`);
            }

            if (tableSettings.hide_responsive_labels) {
                classes.push('nt_hide_breakpoint_labels');
            }

            if (tableSettings.nt_search_full_width) {
                classes.push('nt_search_full_width');
            }

            if (tableSettings.css_lib === 'semantic_ui') {
                classes.push('ui');
            }

            if (tableSettings.css_classes) {
                classes.push(...tableSettings.css_classes);
            }

            return classes.join(' ');
        };

        const getFontStyle = () => {
            return {
                '--ninja-table-font-family': tableSettings.table_font_family || 'inherit',
                '--ninja-table-font-size': `${tableSettings.table_font_size || 16}px`
            };
        };

        const generateCustomCss = () => {
            if (tableSettings.table_color_type !== 'custom_color') {
                return null;
            }

            const prefix = `#footable_${tableId}`;
            let css = `
                ${prefix} {
                    background-color: ${tableSettings.table_color_primary} !important;
                    color: ${tableSettings.table_color_secondary} !important;
                }
                ${prefix} thead tr.footable-filtering th {
                    background-color: ${tableSettings.table_search_color_primary} !important;
                    color: ${tableSettings.table_search_color_secondary} !important;
                }
                ${prefix}:not(.hide_all_borders) thead tr.footable-filtering th {
                    ${tableSettings.table_search_color_border ? `
                        border : 1px solid ${tableSettings.table_search_color_border} !important;
                    ` : `
                        border : 1px solid transparent !important;
                    `}
                }
                ${prefix} tr.footable-header, ${prefix} tr.footable-header th {
                    background-color: ${tableSettings.table_header_color_primary} !important;
                    color: ${tableSettings.table_color_header_secondary} !important;
                }
                ${prefix}:not(.hide_all_borders) tr.footable-header th {
                    border-color: ${tableSettings.table_color_header_border} !important;
                }
                ${prefix}:not(.hide_all_borders) tbody tr td {
                    border-color: ${tableSettings.table_color_border} !important;
                }
                ${prefix} tbody tr:hover {
                    background-color: ${tableSettings.table_color_primary_hover} !important;
                    color: ${tableSettings.table_color_secondary_hover} !important;
                }
                ${prefix} tbody tr:hover td {
                    border-color: ${tableSettings.table_color_border_hover} !important;
                }
            `;

            if (tableSettings.alternate_color_status === 'yes') {
                css += `
                    ${prefix} tbody tr:nth-child(even) {
                        background-color: ${tableSettings.table_alt_color_primary} !important;
                        color: ${tableSettings.table_alt_color_secondary} !important;
                    }
                    ${prefix} tbody tr:nth-child(odd) {
                        background-color: ${tableSettings.table_alt_2_color_primary} !important;
                        color: ${tableSettings.table_alt_2_color_secondary} !important;
                    }
                    ${prefix} tbody tr:nth-child(even):hover {
                        background-color: ${tableSettings.table_alt_color_hover} !important;
                    }
                    ${prefix} tbody tr:nth-child(odd):hover {
                        background-color: ${tableSettings.table_alt_2_color_hover} !important;
                    }
                `;
            }

            css += `
                ${prefix} tfoot .footable-paging {
                    background-color: ${tableSettings.table_footer_bg} !important;
                }
                ${prefix} tfoot .footable-paging .footable-page.active a {
                    background-color: ${tableSettings.table_footer_active} !important;
                }
                ${prefix}:not(.hide_all_borders) tfoot .footable-paging td {
                    border-color: ${tableSettings.table_footer_border} !important;
                }
            `;

            return css;
        };

        const renderTable = () => {
            if (!tableConfig || isLoading) return null;

            const appReady = dataLoaded && scriptLoaded;

            return (
                <div>
                    {generateCustomCss() && (
                        <style id="table_designer_css">
                            {generateCustomCss()}
                        </style>
                    )}

                    <div className="ninja_design_wrapper">
                        <div className="design_preview" style={{ background: 'white', padding: '10px 20px' }}>
                            <div className="ninja_title_section" style={{ display: 'flex', alignItems: 'center', marginBottom: '10px' }}>
                                <h3 style={{ marginRight: '15px' }}>Table Style Customization</h3>
                                <ButtonGroup>
                                    <Button
                                        isPressed={showingDevice === 'desktop'}
                                        onClick={() => setShowingDevice('desktop')}
                                    >
                                        <span className="dashicons dashicons-desktop"></span> Desktop
                                    </Button>
                                    <Button
                                        isPressed={showingDevice === 'tablet'}
                                        onClick={() => setShowingDevice('tablet')}
                                    >
                                        <span className="dashicons dashicons-tablet"></span> Tablet
                                    </Button>
                                    <Button
                                        isPressed={showingDevice === 'mobile'}
                                        onClick={() => setShowingDevice('mobile')}
                                    >
                                        <span className="dashicons dashicons-smartphone"></span> Mobile
                                    </Button>
                                </ButtonGroup>
                            </div>

                            <div
                                id={`footable_parent_${tableId}`}
                                className={getWrapperClasses()}
                            >
                                {tableSettings.show_title && (
                                    <h3 className="table_title footable_title">
                                        {tableConfig?.table?.post_title || ''}
                                    </h3>
                                )}

                                {tableSettings.show_description && tableConfig?.table?.post_content && (
                                    <div
                                        className="table_description footable_description"
                                        dangerouslySetInnerHTML={{ __html: tableConfig.table.post_content }}
                                    />
                                )}

                                <table
                                    id={`footable_${tableId}`}
                                    className={getTableClasses()}
                                    style={getFontStyle()}
                                >
                                    <colgroup>
                                        {formattedColumns.map((column, columnIndex) => (
                                            <col
                                                key={columnIndex}
                                                className={`ninja_column_${columnIndex} ${column.breakpoints || ''}`}
                                            />
                                        ))}
                                    </colgroup>
                                </table>
                            </div>

                            <div className="ninja_demo_disclaimer">
                                <hr />
                                {tableSettings.stackable === 'yes' && (
                                    <p>
                                        <b>For Stackable Tables, Live preview is disabled here. Please check on preview url</b>
                                    </p>
                                )}
                                <p>
                                    <b>Note: </b> For preview purpose, you are seeing up to 25 latest rows here and and per page 10
                                    items if you enable paginate. Also note that, The table style may differ at the frontend as your
                                    theme may overwrite few css elements.
                                </p>
                                <p>Some elements like custom filters and row-inline styling is not available in this design mode. Please check on live preview or in your embeded page.</p>
                            </div>
                        </div>
                    </div>
                </div>
            );
        };

        const saveSettings = () => {
            setSavingSettings(true);
            let data = {
                columns: tableConfig?.columns || [],
                table_settings: tableSettings
            };
            Rest.post(`settings/${tableId}`, data)
                .then((res) => {
                    // Optionally show a success notice
                })
                .catch((error) => {
                    console.error('Error saving settings:', error);
                })
                .finally(() => {
                    setSavingSettings(false);
                });
        };

        // Available styling libraries
        const getAvailableTableLibs = () => {
            const libs = tableLibsData[tableSettings.library]?.css_libs || {};
            return Object.keys(libs).map(key => ({
                label: libs[key].title,
                value: key
            }));
        };

        // Available styles for current library
        const getAvailableStyles = () => {
            const libData = tableLibsData[tableSettings.library]?.css_libs[tableSettings.css_lib];
            return libData?.styles || [];
        };

        // Available color options
        const getAvailableColors = () => {
            const colors = tableLibsData[tableSettings.library]?.colors || {};
            return Object.keys(colors).map(key => ({
                label: colors[key],
                value: key
            }));
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
                        <Button
                            isPrimary
                            isBusy={savingSettings}
                            disabled={savingSettings || !tableId}
                            onClick={saveSettings}
                        >
                            Update Settings
                        </Button>
                    </PanelBody>

                    {tableId && tableConfig && (
                        <>
                            <PanelBody title={__('Styling')} initialOpen={false}>
                                <div style={{ marginBottom: '10px' }}>
                                    <h4>{__('Select Styling Library')}</h4>
                                    <ButtonGroup>
                                        {getAvailableTableLibs().map(lib => (
                                            <Button
                                                key={lib.value}
                                                isPressed={tableSettings.css_lib === lib.value}
                                                onClick={() => updateTableSettings('css_lib', lib.value)}
                                            >
                                                {lib.label}
                                            </Button>
                                        ))}
                                    </ButtonGroup>
                                </div>

                                {getAvailableStyles().length > 0 && (
                                    <div style={{ marginTop: '15px' }}>
                                        <h4>Styles</h4>
                                        {getAvailableStyles().map(style => (
                                            <CheckboxControl
                                                key={style.key}
                                                label={style.title}
                                                checked={tableSettings.css_classes?.includes(style.key)}
                                                onChange={(checked) => {
                                                    const currentClasses = tableSettings.css_classes || [];
                                                    const newClasses = checked
                                                        ? [...currentClasses, style.key]
                                                        : currentClasses.filter(cls => cls !== style.key);
                                                    updateTableSettings('css_classes', newClasses);
                                                }}
                                            />
                                        ))}
                                    </div>
                                )}

                                <h4 style={{ marginTop: '15px' }}>Features</h4>
                                <CheckboxControl
                                    label={__('Show Table Title')}
                                    checked={tableSettings.show_title}
                                    onChange={(val) => updateTableSettings('show_title', val)}
                                />
                                <CheckboxControl
                                    label={__('Show Table Description')}
                                    checked={tableSettings.show_description}
                                    onChange={(val) => updateTableSettings('show_description', val)}
                                />
                                <CheckboxControl
                                    label={__('Enable Search')}
                                    checked={tableSettings.enable_search}
                                    onChange={(val) => updateTableSettings('enable_search', val)}
                                />
                                <CheckboxControl
                                    label={__('Enable Column Sorting')}
                                    checked={tableSettings.column_sorting}
                                    onChange={(val) => updateTableSettings('column_sorting', val)}
                                />
                                <CheckboxControl
                                    label={__('Hide Header Row')}
                                    checked={tableSettings.hide_header_row}
                                    onChange={(val) => updateTableSettings('hide_header_row', val)}
                                />
                                <CheckboxControl
                                    label={__('Hide All Borders')}
                                    checked={tableSettings.hide_all_borders}
                                    onChange={(val) => updateTableSettings('hide_all_borders', val)}
                                />
                                <CheckboxControl
                                    label={__('Hide empty items on responsive breakdown')}
                                    checked={tableSettings.hide_on_empty}
                                    onChange={(val) => updateTableSettings('hide_on_empty', val)}
                                />
                                <CheckboxControl
                                    label={__('Hide Labels on responsive breakdown')}
                                    checked={tableSettings.hide_responsive_labels}
                                    onChange={(val) => updateTableSettings('hide_responsive_labels', val)}
                                />

                                <h4 style={{ marginTop: '15px' }}>Stackable Table Configuration</h4>
                                <ToggleControl
                                    label={__('Enable Stackable Table')}
                                    checked={tableSettings.stackable === 'yes'}
                                    onChange={(val) => updateTableSettings('stackable', val ? 'yes' : 'no')}
                                />

                                {tableSettings.stackable === 'yes' && (
                                    <>
                                        <h4 style={{ marginTop: '15px' }}>Target Devices</h4>
                                        {[
                                            { label: 'Mobile Device', value: 'xs' },
                                            { label: 'Tablet Device', value: 'sm' },
                                            { label: 'Laptop', value: 'md' },
                                            { label: 'Large Devices (imac)', value: 'lg' }
                                        ].map(device => (
                                            <CheckboxControl
                                                key={device.value}
                                                label={device.label}
                                                checked={tableSettings.stacks_devices?.includes(device.value)}
                                                onChange={(checked) => {
                                                    const currentDevices = tableSettings.stacks_devices || [];
                                                    const newDevices = checked
                                                        ? [...currentDevices, device.value]
                                                        : currentDevices.filter(d => d !== device.value);
                                                    updateTableSettings('stacks_devices', newDevices);
                                                }}
                                            />
                                        ))}

                                        <h4 style={{ marginTop: '15px' }}>Stacked Appearance</h4>
                                        <CheckboxControl
                                            label={__('Hide column headings')}
                                            checked={tableSettings.stacks_appearances?.includes('hide_stacked_th')}
                                            onChange={(checked) => {
                                                const currentAppearances = tableSettings.stacks_appearances || [];
                                                const newAppearances = checked
                                                    ? [...currentAppearances, 'hide_stacked_th']
                                                    : currentAppearances.filter(a => a !== 'hide_stacked_th');
                                                updateTableSettings('stacks_appearances', newAppearances);
                                            }}
                                        />
                                        <CheckboxControl
                                            label={__('Hide internal borders')}
                                            checked={tableSettings.stacks_appearances?.includes('ninja_stacked_no_cell_border')}
                                            onChange={(checked) => {
                                                const currentAppearances = tableSettings.stacks_appearances || [];
                                                const newAppearances = checked
                                                    ? [...currentAppearances, 'ninja_stacked_no_cell_border']
                                                    : currentAppearances.filter(a => a !== 'ninja_stacked_no_cell_border');
                                                updateTableSettings('stacks_appearances', newAppearances);
                                            }}
                                        />
                                    </>
                                )}
                            </PanelBody>

                            <PanelBody title={__('Table Colors')} initialOpen={false}>
                                <ButtonGroup>
                                    <Button
                                        isPressed={tableSettings.table_color_type === 'pre_defined_color'}
                                        onClick={() => updateTableSettings('table_color_type', 'pre_defined_color')}
                                    >
                                        Pre Defined Scheme
                                    </Button>
                                    <Button
                                        isPressed={tableSettings.table_color_type === 'custom_color'}
                                        onClick={() => updateTableSettings('table_color_type', 'custom_color')}
                                    >
                                        Custom Scheme
                                    </Button>
                                </ButtonGroup>

                                {tableSettings.table_color_type === 'pre_defined_color' ? (
                                    <SelectControl
                                        value={tableSettings.table_color}
                                        options={getAvailableColors()}
                                        onChange={(val) => updateTableSettings('table_color', val)}
                                    />
                                ) : (
                                    <>
                                        <h4 style={{ marginTop: '15px' }}>Search Bar Colors</h4>
                                        <ColorControl
                                            label={__('Background')}
                                            value={tableSettings.table_search_color_primary}
                                            onChange={(val) => updateTableSettings('table_search_color_primary', val)}
                                        />
                                        <ColorControl
                                            label={__('Icon')}
                                            value={tableSettings.table_search_color_secondary}
                                            onChange={(val) => updateTableSettings('table_search_color_secondary', val)}
                                        />
                                        <ColorControl
                                            label={__('Border')}
                                            value={tableSettings.table_search_color_border}
                                            onChange={(val) => updateTableSettings('table_search_color_border', val)}
                                        />

                                        <h4 style={{ marginTop: '15px' }}>Table Header Colors</h4>
                                        <ColorControl
                                            label={__('Background')}
                                            value={tableSettings.table_header_color_primary}
                                            onChange={(val) => updateTableSettings('table_header_color_primary', val)}
                                        />
                                        <ColorControl
                                            label={__('Text')}
                                            value={tableSettings.table_color_header_secondary}
                                            onChange={(val) => updateTableSettings('table_color_header_secondary', val)}
                                        />
                                        <ColorControl
                                            label={__('Border')}
                                            value={tableSettings.table_color_header_border}
                                            onChange={(val) => updateTableSettings('table_color_header_border', val)}
                                        />

                                        <h4 style={{ marginTop: '15px' }}>Table Body Colors</h4>
                                        <h5>Default</h5>
                                        <ColorControl
                                            label={__('Background')}
                                            value={tableSettings.table_color_primary}
                                            onChange={(val) => updateTableSettings('table_color_primary', val)}
                                        />
                                        <ColorControl
                                            label={__('Text')}
                                            value={tableSettings.table_color_secondary}
                                            onChange={(val) => updateTableSettings('table_color_secondary', val)}
                                        />
                                        <ColorControl
                                            label={__('Border')}
                                            value={tableSettings.table_color_border}
                                            onChange={(val) => updateTableSettings('table_color_border', val)}
                                        />

                                        <h5>Hover</h5>
                                        <ColorControl
                                            label={__('Background')}
                                            value={tableSettings.table_color_primary_hover}
                                            onChange={(val) => updateTableSettings('table_color_primary_hover', val)}
                                        />
                                        <ColorControl
                                            label={__('Text')}
                                            value={tableSettings.table_color_secondary_hover}
                                            onChange={(val) => updateTableSettings('table_color_secondary_hover', val)}
                                        />
                                        <ColorControl
                                            label={__('Border')}
                                            value={tableSettings.table_color_border_hover}
                                            onChange={(val) => updateTableSettings('table_color_border_hover', val)}
                                        />

                                        <div style={{ marginTop: '15px' }}>
                                            <ToggleControl
                                                label={__('Use Alternate Color Schema for Table Rows')}
                                                checked={tableSettings.alternate_color_status === 'yes'}
                                                onChange={(val) => updateTableSettings('alternate_color_status', val ? 'yes' : 'no')}
                                            />
                                        </div>

                                        {tableSettings.alternate_color_status === 'yes' && (
                                            <>
                                                <h4 style={{ marginTop: '15px' }}>Odd Row Colors</h4>
                                                <ColorControl
                                                    label={__('Background')}
                                                    value={tableSettings.table_alt_2_color_primary}
                                                    onChange={(val) => updateTableSettings('table_alt_2_color_primary', val)}
                                                />
                                                <ColorControl
                                                    label={__('Text')}
                                                    value={tableSettings.table_alt_2_color_secondary}
                                                    onChange={(val) => updateTableSettings('table_alt_2_color_secondary', val)}
                                                />
                                                <ColorControl
                                                    label={__('Hover Background')}
                                                    value={tableSettings.table_alt_2_color_hover}
                                                    onChange={(val) => updateTableSettings('table_alt_2_color_hover', val)}
                                                />

                                                <h4 style={{ marginTop: '15px' }}>Even Row Colors</h4>
                                                <ColorControl
                                                    label={__('Background')}
                                                    value={tableSettings.table_alt_color_primary}
                                                    onChange={(val) => updateTableSettings('table_alt_color_primary', val)}
                                                />
                                                <ColorControl
                                                    label={__('Text')}
                                                    value={tableSettings.table_alt_color_secondary}
                                                    onChange={(val) => updateTableSettings('table_alt_color_secondary', val)}
                                                />
                                                <ColorControl
                                                    label={__('Hover Background')}
                                                    value={tableSettings.table_alt_color_hover}
                                                    onChange={(val) => updateTableSettings('table_alt_color_hover', val)}
                                                />
                                            </>
                                        )}

                                        <h4 style={{ marginTop: '15px' }}>Footer Colors</h4>
                                        <ColorControl
                                            label={__('Background')}
                                            value={tableSettings.table_footer_bg}
                                            onChange={(val) => updateTableSettings('table_footer_bg', val)}
                                        />
                                        <ColorControl
                                            label={__('Active')}
                                            value={tableSettings.table_footer_active}
                                            onChange={(val) => updateTableSettings('table_footer_active', val)}
                                        />
                                        <ColorControl
                                            label={__('Border')}
                                            value={tableSettings.table_footer_border}
                                            onChange={(val) => updateTableSettings('table_footer_border', val)}
                                        />
                                    </>
                                )}
                            </PanelBody>

                            <PanelBody title={__('Other Settings')} initialOpen={false}>
                                <ToggleControl
                                    label={__('Hide Pagination (Show all data at once)')}
                                    checked={tableSettings.show_all === '1'}
                                    onChange={(val) => updateTableSettings('show_all', val ? '1' : '0')}
                                />

                                {tableSettings.show_all === '0' && (
                                    <>
                                        <RangeControl
                                            label={__('Pagination Items Per Page')}
                                            value={parseInt(tableSettings.perPage)}
                                            onChange={(val) => updateTableSettings('perPage', val)}
                                            min={1}
                                            max={100}
                                        />

                                        <RadioControl
                                            label={__('Pagination Position')}
                                            selected={tableSettings.pagination_position}
                                            options={[
                                                { label: __('Left'), value: 'left' },
                                                { label: __('Center'), value: 'center' },
                                                { label: __('Right'), value: 'right' }
                                            ]}
                                            onChange={(val) => updateTableSettings('pagination_position', val)}
                                        />

                                        <CheckboxControl
                                            label={__('Scroll to table top for pagination change')}
                                            checked={tableSettings.paginate_to_top}
                                            onChange={(val) => updateTableSettings('paginate_to_top', val)}
                                        />

                                        <CheckboxControl
                                            label={__('Show Page sizes change option')}
                                            checked={tableSettings.show_pager}
                                            onChange={(val) => updateTableSettings('show_pager', val)}
                                        />

                                        {tableSettings.show_pager && (
                                            <TextControl
                                                label={__('Page Sizes (Number as Comma Separated)')}
                                                value={tableSettings.paze_sizes}
                                                onChange={(val) => updateTableSettings('paze_sizes', val)}
                                                placeholder="Default: 10,20,50,100"
                                            />
                                        )}
                                    </>
                                )}

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
                                />

                                <CheckboxControl
                                    label={__('Make search input as full width')}
                                    checked={tableSettings.nt_search_full_width}
                                    onChange={(val) => updateTableSettings('nt_search_full_width', val)}
                                />

                                <RadioControl
                                    label={__('Select Sorting Method')}
                                    selected={tableSettings.sorting_type}
                                    options={[
                                        ...(tableConfig?.table?.isCreatedSortable ? [{ label: __('By Created at'), value: 'by_created_at' }] : []),
                                        { label: __('By Column'), value: 'by_column' },
                                        ...(tableConfig?.table?.isSortable ? [{ label: __('Manual Sort'), value: 'manual_sort' }] : []),
                                    ]}
                                    onChange={(val) => {
                                        if (val === 'manual_sort' && !has_pro) {
                                            window.ninjaTableBus.$emit('show_pro_popup', 1);
                                            return;
                                        }
                                        updateTableSettings('sorting_type', val);
                                    }}
                                />

                                {tableConfig?.table?.isCreatedSortable && tableSettings.sorting_type === 'by_created_at' && (
                                    <SelectControl
                                        label={__('Sort Type')}
                                        value={tableSettings.default_sorting}
                                        options={[
                                            { label: __('Show New Items First'), value: 'new_first' },
                                            { label: __('Show Old Items First'), value: 'old_first' }
                                        ]}
                                        onChange={(val) => updateTableSettings('default_sorting', val)}
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
                                            onChange={(val) => updateTableSettings('sorting_column', val)}
                                        />
                                        <SelectControl
                                            label={__('Sort Type')}
                                            value={tableSettings.sorting_column_by}
                                            options={[
                                                { label: __('Ascending Way'), value: 'ASC' },
                                                { label: __('Descending Way'), value: 'DESC' }
                                            ]}
                                            onChange={(val) => updateTableSettings('sorting_column_by', val)}
                                        />
                                    </>
                                )}

                                <RadioControl
                                    label={__('Row Details (Responsive drawer)')}
                                    selected={tableSettings.expand_type}
                                    options={[
                                        { label: __('Default'), value: 'default' },
                                        { label: __('Expand First'), value: 'expandFirst' },
                                        { label: __('Expand All'), value: 'expandAll' }
                                    ]}
                                    onChange={(val) => updateTableSettings('expand_type', val)}
                                />

                                <RadioControl
                                    label={__('Toggle Position')}
                                    selected={tableSettings.togglePosition}
                                    options={[
                                        { label: __('First Column'), value: 'first' },
                                        { label: __('Last Column'), value: 'last' }
                                    ]}
                                    onChange={(val) => updateTableSettings('togglePosition', val)}
                                />

                                <TextControl
                                    label={__('Extra CSS Class for the table')}
                                    value={tableSettings.extra_css_class}
                                    onChange={(val) => updateTableSettings('extra_css_class', val)}
                                />

                                <ToggleControl
                                    label={__('Sticky First Column')}
                                    checked={tableSettings.sticky_first_column === 'yes'}
                                    onChange={(val) => updateTableSettings('sticky_first_column', val ? 'yes' : 'no')}
                                />

                                <ToggleControl
                                    label={__('Sticky Header')}
                                    checked={tableSettings.sticky_header === 'yes'}
                                    onChange={(val) => updateTableSettings('sticky_header', val ? 'yes' : 'no')}
                                />

                                {tableSettings.sticky_header === 'yes' && (
                                    <>
                                        <TextControl
                                            label={__('Sticky Top Offset')}
                                            value={tableSettings.sticky_header_offset}
                                            onChange={(val) => updateTableSettings('sticky_header_offset', val)}
                                            placeholder="positive or negative number"
                                        />
                                        <ToggleControl
                                            label={__('Disable Sticky header for mobile devices')}
                                            checked={tableSettings.disable_sticky_on_mobile === 'yes'}
                                            onChange={(val) => updateTableSettings('disable_sticky_on_mobile', val ? 'yes' : 'no')}
                                        />
                                    </>
                                )}

                                <h4 style={{ marginTop: '15px' }}>Table Font Setting</h4>
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
                                    onChange={(val) => updateTableSettings('table_font_family', val)}
                                />
                                <RangeControl
                                    label={__('Font Size')}
                                    value={parseInt(tableSettings.table_font_size)}
                                    onChange={(val) => updateTableSettings('table_font_size', val)}
                                    min={1}
                                    max={50}
                                />
                            </PanelBody>
                        </>
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
