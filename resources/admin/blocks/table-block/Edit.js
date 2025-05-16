import Rest from "../../Bits/Rest";
import ColorsTab from "./ui/tabs/ColorsTab";

const {InspectorControls, useBlockProps} = wp.blockEditor || wp.editor;
const {
    SelectControl,
    TabPanel
} = wp.components;

const {__} = wp.i18n;
const {useState, useEffect} = wp.element;
import StyleTab from "./ui/tabs/StyleTab";
import OtherTab from "./ui/tabs/OtherTab";
import {customColorCss} from "./utils/data";
import BlockPreview from "./components/BlockPreview";
import {DEFAULT_TABLE_SETTINGS} from "./utils/constants";
import './style.scss';
import {
    loadRequiredScripts,
    reInitFootables,
    getTableConfig
} from "./utils/footable";

export default function Edit(props) {
    const {attributes, setAttributes} = props;
    const {tableId, dataSource, activeDesign} = attributes;
    const [tableHtml, setTableHtml] = useState('');
    const [instanceId] = useState(() => Math.random().toString(36).substring(2, 10));

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
    const defaultSettings = DEFAULT_TABLE_SETTINGS;

    // Merge default settings with table config settings
    const tableSettings = tableConfig?.settings ?
        {...defaultSettings, ...tableConfig.settings} :
        defaultSettings;

    useEffect(async () => {
        await loadRequiredScripts(setScriptLoaded);
    }, []);

    useEffect(() => {
        if (tableId && dataSource === 'drag_and_drop') {
            fetchDragAndDropTable(tableId);
        } else if (tableId) {
            fetchConfig(tableId);
        }
    }, [tableId]);

    useEffect(() => {
        if (scriptLoaded && dataLoaded && tableInnerHtml) {
            // Use the utility function for reinitialization
            const getConfigFunction = (settings) => getTableConfig(
                tableConfig,
                formattedColumns,
                settings,
                wrapperElementId
            );

            reInitFootables(
                scriptLoaded,
                dataLoaded,
                tableElementId,
                tableInnerHtml,
                null,
                tableSettings,
                getConfigFunction,
                tableId,
                instanceId
            );
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
                // Use the utility function for reinitialization
                const getConfigFunction = (settings) => getTableConfig(
                    tableConfig,
                    formattedColumns,
                    settings,
                    wrapperElementId
                );

                reInitFootables(
                    scriptLoaded,
                    dataLoaded,
                    tableElementId,
                    tableInnerHtml,
                    newSettings,
                    tableSettings,
                    getConfigFunction,
                    tableId,
                    instanceId
                );
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
                <div style={{padding: '0 15px'}}>
                    <SelectControl
                        label={__('Select Table')}
                        value={tableId}
                        options={availableTables}
                        onChange={handleTableSelect}
                    />
                </div>

                {tableId && tableConfig && dataSource !== 'drag_and_drop' && (
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
                                        instanceId={instanceId}
                                    />);
                                case 'colors':
                                    return (
                                        <ColorsTab
                                            tableSettings={tableSettings}
                                            updateTableSettings={updateTableSettings}
                                            tableId={tableId}
                                            instanceId={instanceId}
                                        />
                                    );
                                case 'other':
                                    return (
                                        <OtherTab
                                            tableSettings={tableSettings}
                                            updateTableSettings={updateTableSettings}
                                            tableConfig={tableConfig}
                                            instanceId={instanceId}
                                        />
                                    );
                                default:
                                    return null;
                            }
                        }}
                    </TabPanel>
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
