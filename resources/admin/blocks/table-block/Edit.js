import Rest from "../../Bits/Rest";
import {tableLibs} from "../../data/data";
import ColorsTab from "./ui/tabs/ColorsTab";

const {InspectorControls, useBlockProps} = wp.blockEditor || wp.editor;
const {
    PanelBody,
    SelectControl,
    Placeholder,
    TabPanel
} = wp.components;

const {__} = wp.i18n;
const {useState, useEffect} = wp.element;
import {instanceUID} from "./utils/data";
import StyleTab from "./ui/tabs/StyleTab";
import OtherTab from "./ui/tabs/OtherTab";
import {customColorCss} from "./utils/data";

export default function Edit(props) {
    const {attributes, setAttributes} = props;
    const {tableId, dataSource, activeDesign} = attributes;
    const [tableHtml, setTableHtml] = useState('');
    const instanceId = instanceUID();

    const [tableConfig, setTableConfig] = useState(null);
    const [tableInnerHtml, setTableInnerHtml] = useState('');
    const [formattedColumns, setFormattedColumns] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [scriptLoaded, setScriptLoaded] = useState(false);
    const [dataLoaded, setDataLoaded] = useState(false);

    const tableElementId = `footable_${tableId}_${instanceId}`;
    const wrapperElementId = `footable_parent_${tableId}_${instanceId}`;


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
        {...defaultSettings, ...tableConfig.settings} :
        defaultSettings;

    useEffect(() => {
        if (tableId && dataSource === 'drag_and_drop') {
            fetchDragAndDropTable(tableId);
        } else if (tableId) {
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
        initializeColorSettings(selectedTableId, tableSettings, instanceId);
    };

    const renderDragAndDropTable = () => {
        if (isLoading) {
            return <div className="loading-spinner">Loading...</div>;
        }

        return (
            <div
                id={`ninja_table_builder_${tableId}_${instanceId}`}
                className="ninja-table-builder-preview"
                dangerouslySetInnerHTML={{__html: tableHtml}}
            />
        );
    };

    const fetchDragAndDropTable = (tableId) => {
        setIsLoading(true);
        Rest.get(`tables/${tableId}/drag_and_drop_html`)
            .then(response => {
                setTableHtml(response.html);
                setIsLoading(false);
            })
            .catch(error => {
                setIsLoading(false);
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
            const ft = FooTable.get(`#${tableElementId}`);
            if (ft) {
                ft.destroy();
            }
        }

        const $table = jQuery(`#${tableElementId}`);
        $table.find('thead,tbody,tfoot').remove();
        $table.append(tableInnerHtml);

        // Pass the updated settings (if available)
        initFootables(updatedSettings);
        customColorCss(tableId, updatedSettings || tableSettings, instanceId);
    };


    const initFootables = (updatedSettings = null) => {
        if (!scriptLoaded) return;

        const NinjaTableApp = window.ninjaTableApp;
        const $table = jQuery(`#${tableElementId}`);

        // Use updated settings if provided, otherwise use the state
        const settings = updatedSettings || tableSettings;

        if (settings.hide_on_empty) {
            $table.on('expanded.ft.row', function (e, ft, row) {
                $table.find('table.footable-details td:empty').parent().addClass('nt_has_hide');
            });
        }

        const config = getTableConfig(settings);
        NinjaTableApp.initTable($table, config);
        // initializeColorSettings(tableId, tableSettings);
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
                container: `#${wrapperElementId} .paging-ui-container`,
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
    const renderTable = () => {
        if (!tableConfig || isLoading || !scriptLoaded) return null;

        const appReady = dataLoaded && scriptLoaded;

        return (
            <div className="ninja_design_wrapper">
                {renderStyles()}
                <div className="design_preview" style={{background: 'white', padding: '10px 20px'}}>
                    {tableSettings.show_title && tableConfig.table?.post_title && (
                        <h3 className="table_title footable_title">
                            {tableConfig.table.post_title}
                        </h3>
                    )}

                    {tableSettings.show_description && tableConfig.table?.post_content && (
                        <div
                            className="table_description footable_description"
                            dangerouslySetInnerHTML={{__html: tableConfig.table.post_content}}
                        />
                    )}
                    <div
                        id={wrapperElementId}
                        className={`footable_parent ninja_table_wrapper loading_ninja_table wp_table_data_press_parent ${getWrapperClasses()}`}
                    >
                        <table
                            id={tableElementId}
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

    // This function generates CSS for the table colors based on the settings


    const initializeColorSettings = (tableId, settings, instanceId) => {
        // Set initial CSS
        customColorCss(tableId, settings, instanceId);

        // Return a function to update colors when settings change
        return (newSettings) => {
            customColorCss(tableId, newSettings, instanceId);
        };
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

                {tableId && tableConfig && dataSource !== 'drag_and_drop' && (
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
                            onSelect={(tabName) => setAttributes({activeDesign: tabName})}
                            initialTabName={activeDesign}
                        >
                            {(tab) => {
                                switch (tab.name) {
                                    case 'styling':
                                        return (<StyleTab
                                            tableSettings={tableSettings}
                                            updateTableSettings={updateTableSettings}
                                        />);
                                    case 'colors':
                                        return (
                                            <div className="ninja-tab-content">
                                                <ColorsTab
                                                    tableSettings={tableSettings}
                                                    updateTableSettings={updateTableSettings}
                                                    tableId={tableId}
                                                />
                                            </div>
                                        );
                                    case 'other':
                                        return (
                                            <OtherTab
                                                tableSettings={tableSettings}
                                                updateTableSettings={updateTableSettings}
                                                tableConfig={tableConfig}
                                            />
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
                ) : dataSource === 'drag_and_drop' ? (
                    renderDragAndDropTable()
                ) : (
                    renderTable()
                )}
            </div>
        </div>
    );
}
