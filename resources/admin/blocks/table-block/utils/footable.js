let scriptsAlreadyLoaded = false;

export const loadRequiredScripts = async (setScriptLoaded) => {
    // Avoid loading multiple times across block instances
    if (scriptsAlreadyLoaded) {
        waitForGlobals(setScriptLoaded);
        return;
    }

    const scripts = window.ninja_table_admin?.preview_required_scripts || [];

    scripts.filter(src => src.endsWith('.css')).forEach(href => {
        if (!document.querySelector(`link[href="${href}"]`)) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        }
    });

    const jsScripts = scripts
        .filter(src => src.endsWith('.js'))
        .sort((a, b) => {
            if (a.includes('ninja-tables-footable.js')) return 1;
            if (b.includes('ninja-tables-footable.js')) return -1;

            return 0;
        });

    for (const src of jsScripts) {
        if (document.querySelector(`script[src="${src}"]`)) continue;

        await new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = false;
            script.onload = resolve;
            script.onerror = () => {
                console.error(`Failed to load: ${src}`);
                resolve(); // continue loading others
            };
            document.head.appendChild(script);
        });
    }

    scriptsAlreadyLoaded = true;
    waitForGlobals(setScriptLoaded);
};

const waitForGlobals = (setScriptLoaded, retryCount = 0) => {
    if (typeof window.FooTable !== 'undefined' && typeof window.ninjaTableApp !== 'undefined') {
        setScriptLoaded(true);
    } else if (retryCount < 10) {
        setTimeout(() => waitForGlobals(setScriptLoaded, retryCount + 1), 300);
    } else {
        console.warn('Scripts loaded but FooTable or ninjaTableApp still missing.');
    }
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
