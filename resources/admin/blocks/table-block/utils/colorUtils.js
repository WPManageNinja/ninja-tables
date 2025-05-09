import {instanceUID} from "./data";
export default function generateColorCss(tableId, settings) {
    const instanceId = instanceUID();
    if (settings.table_color_type !== 'custom_color') {
        const styleElement = document.getElementById(`ninja_table_custom_css_${tableId}_${instanceId}`);
        if (styleElement) {
            styleElement.innerHTML = '';
        }
        return;
    }

    const prefix = `#footable_${tableId}_${instanceId}`;
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
        'border: 1px solid transparent !important;'}
        }
        ${prefix} tr.footable-header, ${prefix} tr.footable-header th {
            background-color: ${settings.table_header_color_primary || 'initial'} !important;
            color: ${settings.table_color_header_secondary || 'initial'} !important;
        }
        ${prefix} tr.footable-header th {
            border-color: ${settings.table_color_header_border || 'initial'} !important;
        }
        ${prefix} tbody tr td {
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
        ${prefix} tfoot .footable-paging td {
            border-color: ${settings.table_footer_border || 'initial'} !important;
        }
    `;

    let styleElement = document.getElementById(`ninja_table_custom_css_${tableId}_${instanceId}`);
    if (!styleElement) {
        styleElement = document.createElement('style');
        styleElement.id = `ninja_table_custom_css_${tableId}_${instanceId}`;
        document.head.appendChild(styleElement);
    }
    styleElement.innerHTML = css;
}
