import {tableLibs} from "../../../data/data";

const {Placeholder, SelectControl, Spinner} = wp.components;
const {__} = wp.i18n;
import {hasPro, availableTables} from "../utils/data";

export default function BlockPreview({
                                         tableId,
                                         dataSource,
                                         handleTableSelect,
                                         tableHtml,
                                         isLoading,
                                         tableConfig,
                                         formattedColumns,
                                         instanceId,
                                         tableElementId,
                                         wrapperElementId
                                     }) {

    const getWrapperClasses = () => {
        if (!tableConfig?.settings) return '';
        const settings = tableConfig.settings;
        const classes = [];

        if (settings.css_lib) {
            classes.push(settings.css_lib);
        }
        if (settings.table_color_type === 'custom_color' || (settings.table_color && settings.table_color !== 'ninja_no_color_table')) {
            classes.push('colored_table');
        }
        return classes.join(' ');
    };

    const getTableClasses = () => {
        if (!tableConfig?.settings) return 'table foo-table ninja_footable';

        const settings = tableConfig.settings;
        let classes = ['table', 'foo-table', 'ninja_footable'];

        if (tableId) {
            classes.push(`foo_table_${tableId}`);
        }
        if (settings.table_color_type === 'custom_color') {
            classes.push('inverted', 'ninja_custom_color');
        } else if (settings.table_color && settings.table_color !== 'ninja_no_color_table') {
            classes.push('inverted', settings.table_color);
        }
        // Layout
        if (settings.pagination_position) {
            classes.push(`footable-paging-${settings.pagination_position}`);
        } else {
            classes.push('footable-paging-right');
        }
        if (settings.hide_header_row) classes.push('ninjatable_hide_header_row');
        if (settings.hide_all_borders) classes.push('hide_all_borders');
        if (hasPro) classes.push('ninja_table_pro');
        if (settings.search_position) classes.push(`ninja_search_${settings.search_position}`);
        if (settings.hide_responsive_labels) classes.push('nt_hide_breakpoint_labels');
        if (settings.nt_search_full_width) classes.push('nt_search_full_width');
        if (settings.css_lib === 'semantic_ui') classes.push('ui');

        let table_css_classes = [];
        if (settings.css_classes && Array.isArray(settings.css_classes)) {
            const availableCssClasses = getAvailableCssClasses();
            table_css_classes = availableCssClasses.filter(value =>
                settings.css_classes.indexOf(value) !== -1
            );
        }

        return [...table_css_classes, ...classes].join(' ');
    };

    const getAvailableCssClasses = () => {
        if (!tableConfig?.settings?.css_lib || !tableConfig?.settings?.library) {
            return [];
        }

        const libs = tableLibs()
        const currentLib = libs[tableConfig.settings.library]?.css_libs?.[tableConfig.settings.css_lib];

        if (!currentLib || !currentLib.styles) {
            return [];
        }

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

    const renderStyles = () => {
        return (
            <style>
                {`
				.striped > tbody > :nth-child(odd) { background: transparent; }
				.footable_parent.ninja_device_mobile { width: 480px; margin: 0 auto; }
				.footable_parent.ninja_device_tablet { max-width: 768px; padding: 0 20px; margin: 0 auto; }
				.footable_parent .footable-header th, .footable_parent .footable-paging {
					font-size: var(--ninja-table-font-size);
				}
				.ninja_footable tbody tr td {
					font-size: var(--ninja-table-font-size);
					font-family: var(--ninja-table-font-family);
				}
			`}
            </style>
        );
    };

    const renderDragAndDropTable = () => {
        if (isLoading) {
            return <div style={{textAlign: "center"}}><Spinner/></div>;
        }
        return (
            <div
                id={`ninja_table_builder_${tableId}_${instanceId}`}
                className="ninja-table-builder-preview"
                dangerouslySetInnerHTML={{__html: tableHtml}}
            />
        );
    };

    const renderStandardTable = () => {
        if (isLoading) {
            return <div style={{textAlign: "center"}}><Spinner/></div>;
        }
        if (!tableConfig) return null;

        return (
            <div className="ninja_design_wrapper">
                {renderStyles()}
                <div className="design_preview" style={{background: 'white', padding: '10px 20px'}}>
                    {tableConfig?.settings?.show_title && tableConfig?.table?.post_title && (
                        <h3 className="table_title footable_title">{tableConfig.table.post_title}</h3>
                    )}

                    {tableConfig?.settings?.show_description && tableConfig?.table?.post_content && (
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
                        >
                            <colgroup>
                                {formattedColumns?.map((column, columnIndex) => (
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

    // Render Wrapper
    return (
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
                renderStandardTable()
            )}
        </div>
    );
}
