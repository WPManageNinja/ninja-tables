export const loadRequiredScripts = (setScriptLoaded) => {
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

export const initFootables = (scriptLoaded, tableElementId, settings, getTableConfig) => {
    if (!scriptLoaded) return;

    const NinjaTableApp = window.ninjaTableApp;
    const $table = jQuery(`#${tableElementId}`);

    if (settings.hide_on_empty) {
        $table.on('expanded.ft.row', function (e, ft, row) {
            $table.find('table.footable-details td:empty').parent().addClass('nt_has_hide');
        });
    }

    const config = getTableConfig(settings);
    NinjaTableApp.initTable($table, config);
};


export const reInitFootables = (
    scriptLoaded,
    dataLoaded,
    tableElementId,
    tableInnerHtml,
    updatedSettings,
    tableSettings,
    getTableConfig,
    tableId,
    instanceId
) => {
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
    const settings = updatedSettings || tableSettings;
    initFootables(scriptLoaded, tableElementId, settings, getTableConfig);

    // Apply custom color CSS
    const { customColorCss } = require('./data');
    customColorCss(tableId, settings, instanceId);
};

export const getTableConfig = (tableConfig, formattedColumns, settings, wrapperElementId) => {
    if (!tableConfig) return {};

    // Use provided settings or fall back to table config settings
    const configSettings = settings || tableConfig.settings || {};
    const columns = tableConfig.columns || [];

    const customCss = {};
    columns.forEach((column, index) => {
        customCss[`ninja_column_${index}`] = {
            'text-align': column.textAlign,
            'width': `${column.width}px`
        };
    });

    const tableSettings = {
        default_sorting: configSettings.default_sorting || 'old_first',
        defaut_filter: false,
        defaut_filter_column: null,
        expandAll: configSettings.expand_type === "expandAll",
        expandFirst: configSettings.expand_type === "expandFirst",
        filtering: !!configSettings.enable_search,
        i18n: {},
        use_parent_width: false,
        sorting: !!configSettings.column_sorting,
        togglePosition: configSettings.togglePosition
    };

    const initConfig = {
        toggleColumn: configSettings.togglePosition,
        cascade: true,
        useParentWidth: false,
        columns: columns,
        expandFirst: configSettings.expand_type === "expandFirst",
        expandAll: configSettings.expand_type === "expandAll",
        empty: '',
        filtering: {
            enabled: !!configSettings.enable_search
        },
        paging: {
            // Use the passed settings (which may include the latest changes)
            enabled: configSettings.show_all !== '1' && configSettings.show_all !== 1,
            size: parseInt(configSettings.perPage || 10),
            container: `#${wrapperElementId} .paging-ui-container`,
        },
        sorting: {
            enabled: !!configSettings.column_sorting
        },
    };

    return {
        columns: formattedColumns.map(item => Object.assign({}, item)),
        custom_css: customCss,
        settings: tableSettings,
        render_type: 'legacy_table',
        instance_name: 'ninja_table_instance_0',
        table_id: tableConfig.table_id || '',
        title: '',
        init_config: initConfig
    };
};
