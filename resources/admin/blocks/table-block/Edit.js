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
import BlockPreview from "./components/BlockPreview";

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

            <BlockPreview
                tableId={tableId}
                dataSource={dataSource}
                handleTableSelect={handleTableSelect}
                tableHtml={tableHtml}
                isLoading={isLoading}
                tableConfig={tableConfig}
                formattedColumns={formattedColumns}
                instanceId={instanceId}
                tableElementId={tableElementId}
                wrapperElementId={wrapperElementId}
            />
        </div>
    );
}
