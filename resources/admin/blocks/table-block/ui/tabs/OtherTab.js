import {isTruthy} from "../../utils/helpers";

const { __ } = wp.i18n;
const {
    ToggleControl,
    TextControl,
    RadioControl,
    CheckboxControl,
    SelectControl,
    RangeControl,
    ButtonGroup,
    Button,
    PanelBody
} = wp.components;

import { hasPro } from "../../utils/data";
import {
    EXPAND_TYPE_OPTIONS,
    FONT_FAMILY_OPTIONS,
    PAGINATION_POSITION_OPTIONS,
    SEARCH_POSITION_OPTIONS,
    TOGGLE_POSITION_OPTIONS
} from "../../utils/constants";

export default function OtherTab({ tableSettings, updateTableSettings, tableConfig }) {
    return (
        <div className="ntb-tab-content label-normalize">
            <PanelBody title={__('Pagination Settings')} initialOpen={true}>
                <ToggleControl
                    label={__('Hide Pagination (Show all data at once)')}
                    checked={tableSettings.show_all == '1'}
                    onChange={(val) => updateTableSettings('show_all', val ? '1' : '0')}
                />
                {tableSettings.show_all !== '1' && (
                    <>
                        <TextControl
                            label={__('Pagination Items Per Page')}
                            type="number"
                            value={tableSettings.perPage}
                            onChange={(val) => updateTableSettings('perPage', val)}
                        />
                        <RadioControl
                            label={__('Pagination Position')}
                            selected={tableSettings.pagination_position}
                            options={PAGINATION_POSITION_OPTIONS}
                            onChange={(val) => updateTableSettings('pagination_position', val)}
                        />
                        <CheckboxControl
                            label={<span>{__('Scroll to table top for pagination change')} {!hasPro && <span> ({__('Pro')})</span>}</span>}
                            checked={tableSettings.paginate_to_top}
                            onChange={(val) => updateTableSettings('paginate_to_top', val, false)}
                            disabled={!hasPro}
                            help={__('If enabled, table will scroll to top when pagination changes')}
                        />
                        <CheckboxControl
                            label={<span>{__('Show page size changer')} {!hasPro && <span> ({__('Pro')})</span>}</span>}
                            checked={tableSettings.show_pager}
                            onChange={(val) => updateTableSettings('show_pager', val)}
                            disabled={!hasPro}
                            help={__('Allows users to select number of items per page')}
                        />
                        {tableSettings.show_pager && (
                            <TextControl
                                label={__('Page Sizes (Comma-separated)')}
                                value={tableSettings.paze_sizes}
                                onChange={(val) => updateTableSettings('paze_sizes', val, false)}
                                placeholder="Default: 10,20,50,100"
                            />
                        )}
                    </>
                )}
            </PanelBody>

            <PanelBody title={__('Search Settings')} initialOpen={false}>
                <RadioControl
                    label={__('Search Bar Position')}
                    selected={tableSettings.search_position}
                    options={SEARCH_POSITION_OPTIONS}
                    onChange={(val) => updateTableSettings('search_position', val)}
                    disabled={!hasPro}
                />
                <CheckboxControl
                    label={<span>{__('Full-width Search Input')} {!hasPro && <span> ({__('Pro')})</span>}</span>}
                    checked={isTruthy(tableSettings.nt_search_full_width)}
                    onChange={(val) => updateTableSettings('nt_search_full_width', val)}
                    disabled={!hasPro}
                    help={__('Makes the search input take full width')}
                />
            </PanelBody>

            <PanelBody title={__('Sorting Settings')} initialOpen={false}>
                <ButtonGroup>
                    {tableConfig?.table?.isCreatedSortable && (
                        <Button
                            isSmall
                            isPressed={tableSettings.sorting_type === 'by_created_at'}
                            onClick={() => updateTableSettings('sorting_type', 'by_created_at', false)}
                        >{__('Created at')}</Button>
                    )}
                    <Button
                        isSmall
                        isPressed={tableSettings.sorting_type === 'by_column'}
                        onClick={() => updateTableSettings('sorting_type', 'by_column', false)}
                    >{__('By Column')}</Button>
                    {tableConfig?.table?.isSortable && (
                        <Button
                            isSmall
                            isPressed={tableSettings.sorting_type === 'manual_sort'}
                            onClick={() => updateTableSettings('sorting_type', 'manual_sort', false)}
                        >{__('Manual Sort')}</Button>
                    )}
                </ButtonGroup>
                {tableSettings.sorting_type === 'by_created_at' && (
                    <SelectControl
                        label={__('Sort Type')}
                        value={tableSettings.default_sorting}
                        options={[
                            { label: __('Newest First'), value: 'new_first' },
                            { label: __('Oldest First'), value: 'old_first' }
                        ]}
                        onChange={(val) => updateTableSettings('default_sorting', val, false)}
                    />
                )}
                {tableSettings.sorting_type === 'by_column' && (
                    <>
                        <SelectControl
                            label={__('Column')}
                            value={tableSettings.sorting_column}
                            options={tableConfig?.columns?.map(col => ({ label: col.name, value: col.key })) || []}
                            onChange={(val) => updateTableSettings('sorting_column', val, false)}
                        />
                        <SelectControl
                            label={__('Sort Order')}
                            value={tableSettings.sorting_column_by}
                            options={[
                                { label: __('Ascending'), value: 'ASC' },
                                { label: __('Descending'), value: 'DESC' }
                            ]}
                            onChange={(val) => updateTableSettings('sorting_column_by', val, false)}
                        />
                    </>
                )}
            </PanelBody>

            <PanelBody title={__('Sticky & Row Details')} initialOpen={false}>
                <RadioControl
                    label={__('Row Details (Responsive Drawer)')}
                    selected={tableSettings.expand_type}
                    options={EXPAND_TYPE_OPTIONS}
                    onChange={(val) => updateTableSettings('expand_type', val, false)}
                    disabled={!hasPro}
                />
                <RadioControl
                    label={__('Toggle Button Position')}
                    selected={tableSettings.togglePosition}
                    options={TOGGLE_POSITION_OPTIONS}
                    onChange={(val) => updateTableSettings('togglePosition', val, false)}
                />
                <CheckboxControl
                    label={__('Sticky First Column')}
                    checked={tableSettings.sticky_first_column === 'yes'}
                    onChange={(val) => updateTableSettings('sticky_first_column', val ? 'yes' : 'no', false)}
                    disabled={!hasPro}
                />
                <CheckboxControl
                    label={__('Sticky Header')}
                    checked={tableSettings.sticky_header === 'yes'}
                    onChange={(val) => updateTableSettings('sticky_header', val ? 'yes' : 'no', false)}
                    disabled={!hasPro}
                />
                {tableSettings.sticky_header === 'yes' && (
                    <>
                        <TextControl
                            label={__('Sticky Offset')}
                            value={tableSettings.sticky_header_offset}
                            onChange={(val) => updateTableSettings('sticky_header_offset', val, false)}
                            placeholder="positive or negative number"
                            help={__('Offset in px or jQuery selector for sticky header')}
                            type="number"
                        />
                        <CheckboxControl
                            label={__('Disable Sticky on Mobile')}
                            checked={tableSettings.disable_sticky_on_mobile === 'yes'}
                            onChange={(val) => updateTableSettings('disable_sticky_on_mobile', val ? 'yes' : 'no', false)}
                            disabled={!hasPro}
                        />
                    </>
                )}
            </PanelBody>
            <PanelBody title={__('Font & Custom Class')} initialOpen={false}>
                <TextControl
                    label={__('Extra CSS Class for Table')}
                    value={tableSettings.extra_css_class}
                    onChange={(val) => updateTableSettings('extra_css_class', val, false)}
                />
                <SelectControl
                    label={__('Font Family')}
                    value={tableSettings.table_font_family}
                    options={FONT_FAMILY_OPTIONS}
                    onChange={(val) => updateTableSettings('table_font_family', val, false)}
                />
                <RangeControl
                    label={__('Font Size')}
                    value={parseInt(tableSettings.table_font_size) || 16}
                    onChange={(val) => updateTableSettings('table_font_size', val, false)}
                    min={1}
                    max={50}
                />
            </PanelBody>
        </div>
    );
}
