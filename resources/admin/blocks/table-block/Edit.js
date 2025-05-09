import Rest from "../../Bits/Rest";
import {tableLibs} from "../../data/data";
import ColorsTab from "./ui/tabs/ColorsTab";

const {InspectorControls, useBlockProps} = wp.blockEditor || wp.editor;
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

const {__} = wp.i18n;
const {useState, useEffect} = wp.element;
import {instanceUID} from "./utils/data";
import StyleTab from "./ui/tabs/StyleTab";

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
        initializeColorSettings(selectedTableId, tableSettings);
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
        generateColorCss(tableId, updatedSettings || tableSettings);
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
    const generateColorCss = (tableId, settings) => {
        if (settings.table_color_type !== 'custom_color') {
            // Clear custom CSS if using predefined colors
            const styleElement = document.getElementById(`ninja_table_custom_css_${tableId}_${instanceId}`);
            if (styleElement) {
                styleElement.innerHTML = '';
            }
            return;
        }

        const prefix = `#${tableElementId}`;
        const css = `
        ${prefix} {
            background-color: ${settings.table_color_primary || 'initial'} !important;
            color: ${settings.table_color_secondary || 'initial'} !important;
        }
        ${prefix} thead tr.footable-filtering th {
            background-color: ${settings.table_search_color_primary || 'initial'} !important;
            color: ${settings.table_search_color_secondary || 'initial'} !important;
        }
        ${prefix}:not(.hide_all_borders) thead tr.footable-filtering th {
            ${settings.table_search_color_border ?
            `border: 1px solid ${settings.table_search_color_border} !important;` :
            'border: 1px solid transparent !important;'
        }
        }
        ${prefix} .input-group-btn:last-child > .btn:not(:last-child):not(.dropdown-toggle) {
            background-color: ${settings.table_search_color_secondary || 'initial'} !important;
            color: ${settings.table_search_color_primary || 'initial'} !important;
        }
        ${prefix} tr.footable-header, ${prefix} tr.footable-header th {
            background-color: ${settings.table_header_color_primary || 'initial'} !important;
            color: ${settings.table_color_header_secondary || 'initial'} !important;
        }
        ${prefix} tr.footable-header, ${prefix} tr.footable-header th span::before {
            background-color: ${settings.table_color_header_secondary || 'initial'} !important;
        }
        ${prefix}:not(.hide_all_borders) tr.footable-header th {
            border-color: ${settings.table_color_header_border || 'initial'} !important;
        }
        ${prefix}:not(.hide_all_borders) tbody tr td {
            border-color: ${settings.table_color_border || 'initial'} !important;
        }
        ${prefix} tbody tr:hover {
            background-color: ${settings.table_color_primary_hover || 'initial'} !important;
            color: ${settings.table_color_secondary_hover || 'initial'} !important;
        }
        ${prefix} tbody tr:hover td {
            border-color: ${settings.table_color_border_hover || 'initial'} !important;
        }

        ${settings.alternate_color_status === 'yes' ? `
            ${prefix} tbody tr:nth-child(even) {
                background-color: ${settings.table_alt_color_primary || 'initial'} !important;
                color: ${settings.table_alt_color_secondary || 'initial'} !important;
            }
            ${prefix} tbody tr:nth-child(odd) {
                background-color: ${settings.table_alt_2_color_primary || 'initial'} !important;
                color: ${settings.table_alt_2_color_secondary || 'initial'} !important;
            }
            ${prefix} tbody tr:nth-child(even):hover {
                background-color: ${settings.table_alt_color_hover || 'initial'} !important;
            }
            ${prefix} tbody tr:nth-child(odd):hover {
                background-color: ${settings.table_alt_2_color_hover || 'initial'} !important;
            }
        ` : ''}

        ${prefix} tfoot .footable-paging {
            background-color: ${settings.table_footer_bg || 'initial'} !important;
        }
        ${prefix} tfoot .footable-paging .footable-page.active a {
            background-color: ${settings.table_footer_active || 'initial'} !important;
        }
        ${prefix}:not(.hide_all_borders) tfoot .footable-paging td {
            border-color: ${settings.table_footer_border || 'initial'} !important;
        }
    `;

        // Apply the CSS - create or update the style element
        let styleElement = document.getElementById(`ninja_table_custom_css_${tableId}_${instanceId}`);
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = `ninja_table_custom_css_${tableId}_${instanceId}`;
            document.head.appendChild(styleElement);
        }
        styleElement.innerHTML = css;
    };

    const initializeColorSettings = (tableId, settings) => {
        // Set initial CSS
        generateColorCss(tableId, settings);

        // Return a function to update colors when settings change
        return (newSettings) => {
            generateColorCss(tableId, newSettings);
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
                                                                {label: __('Left'), value: 'left'},
                                                                {label: __('Center'), value: 'center'},
                                                                {label: __('Right'), value: 'right'}
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
                                                            {label: __('Left'), value: 'left'},
                                                            {label: __('Center'), value: 'center'},
                                                            {label: __('Right'), value: 'right'},
                                                            {label: __('Default'), value: ''}
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
                                                    <label style={{
                                                        display: 'block',
                                                        marginBottom: '8px'
                                                    }}>{__('Select Sorting Method')}</label>
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
                                                                {label: __('Show New Items First'), value: 'new_first'},
                                                                {label: __('Show Old Items First'), value: 'old_first'}
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
                                                                    {label: __('Ascending Way'), value: 'ASC'},
                                                                    {label: __('Descending Way'), value: 'DESC'}
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
                                                    <label style={{display: 'block', marginBottom: '8px'}}>
                                                        {__('Row Details (Responsive drawer)')} {!has_pro &&
                                                        <span>({__('PRO')})</span>}
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
                                                                help: __('If you use responsive breakdown then the ' + ' icon will show at the first visible column')
                                                            },
                                                            {
                                                                label: __('Last Column'),
                                                                value: 'last',
                                                                help: __('If you use responsive breakdown then the ' + ' icon will show at the last visible column')
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
                                                            {label: __('theme-font'), value: 'inherit'},
                                                            {label: 'cursive', value: 'cursive'},
                                                            {label: 'fantasy', value: 'fantasy'},
                                                            {label: 'monospace', value: 'monospace'},
                                                            {label: 'sans-serif', value: 'sans-serif'},
                                                            {label: 'serif', value: 'serif'},
                                                            {label: 'system-ui', value: 'system-ui'},
                                                            {label: 'ui-monospace', value: 'ui-monospace'},
                                                            {label: 'ui-rounded', value: 'ui-rounded'},
                                                            {label: 'ui-sans-serif', value: 'ui-sans-serif'},
                                                            {label: 'ui-serif', value: 'ui-serif'}
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
                ) : dataSource === 'drag_and_drop' ? (
                    renderDragAndDropTable()
                ) : (
                    renderTable()
                )}
            </div>
        </div>
    );
}
