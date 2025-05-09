const { __ } = wp.i18n;
const {
    ToggleControl,
    TextControl,
    RadioControl,
    CheckboxControl,
    SelectControl,
    RangeControl,
    ButtonGroup,
    Button
} = wp.components;

import {hasPro} from "../../utils/data";

export default function OtherTab({ tableSettings, updateTableSettings, tableConfig }) {
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
                        />
                    </div>

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
                        label={<span>{__('Scroll to table top for pagination change')} {!hasPro && <span> ({__('Pro Only')})</span>}</span>}
                        checked={tableSettings.paginate_to_top}
                        onChange={(val) => updateTableSettings('paginate_to_top', val, false)}
                        disabled={!hasPro}
                        help={__('If you enable this then on pagination change, the table will be scrolled to top')}
                    />

                    <CheckboxControl
                        label={<span>{__('Show Page sizes change option')} {!hasPro && <span> ({__('Pro Only')})</span>}</span>}
                        checked={tableSettings.show_pager}
                        onChange={(val) => updateTableSettings('show_pager', val)}
                        disabled={!hasPro}
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
                        { label: __('Left'), value: 'left' },
                        { label: __('Center'), value: 'center' },
                        { label: __('Right'), value: 'right' },
                        { label: __('Default'), value: '' }
                    ]}
                    onChange={(val) => updateTableSettings('search_position', val)}
                    disabled={!hasPro}
                />

                <CheckboxControl
                    label={<span>{__('Make search input as full width')} {!hasPro && <span> ({__('Pro Only')})</span>}</span>}
                    checked={tableSettings.nt_search_full_width}
                    onChange={(val) => updateTableSettings('nt_search_full_width', val)}
                    disabled={!hasPro}
                    help={__('If You enable this, Then the search input will take all the available space (100% width)')}
                />
            </div>

            {/* Sorting Settings */}
            <div className="form_group">
                <label style={{ display: 'block', marginBottom: '8px' }}>{__('Select Sorting Method')}</label>
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
                            { label: __('Show New Items First'), value: 'new_first' },
                            { label: __('Show Old Items First'), value: 'old_first' }
                        ]}
                        onChange={(val) => updateTableSettings('default_sorting', val, false)}
                    />
                )}

                {tableSettings.sorting_type === 'by_column' && (
                    <>
                        <SelectControl
                            label={__('Select Column')}
                            value={tableSettings.sorting_column}
                            options={tableConfig?.columns?.map(column => ({ label: column.name, value: column.key })) || []}
                            onChange={(val) => updateTableSettings('sorting_column', val, false)}
                        />
                        <SelectControl
                            label={__('Sort Type')}
                            value={tableSettings.sorting_column_by}
                            options={[
                                { label: __('Ascending Way'), value: 'ASC' },
                                { label: __('Descending Way'), value: 'DESC' }
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
                <label style={{ display: 'block', marginBottom: '8px' }}>
                    {__('Row Details (Responsive drawer)')} {!hasPro && <span>({__('PRO')})</span>}
                </label>
                <RadioControl
                    selected={tableSettings.expand_type}
                    options={[
                        { label: __('Default'), value: 'default' },
                        { label: __('Expand First'), value: 'expandFirst' },
                        { label: __('Expand All'), value: 'expandAll' }
                    ]}
                    onChange={(val) => updateTableSettings('expand_type', val, false)}
                    disabled={!hasPro}
                />
            </div>

            {/* Toggle Position */}
            <div className="form_group">
                <RadioControl
                    label={__('Toggle Position')}
                    selected={tableSettings.togglePosition}
                    options={[
                        { label: __('First Column'), value: 'first' },
                        { label: __('Last Column'), value: 'last' }
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
                    label={<span>{__('Sticky First Column')} {!hasPro && <span> ({__('Pro')})</span>}</span>}
                    checked={tableSettings.sticky_first_column === 'yes'}
                    onChange={(val) => updateTableSettings('sticky_first_column', val ? 'yes' : 'no', false)}
                    disabled={!hasPro}
                />

                <CheckboxControl
                    label={<span>{__('Sticky Header')} {!hasPro && <span> ({__('Pro')})</span>}</span>}
                    checked={tableSettings.sticky_header === 'yes'}
                    onChange={(val) => updateTableSettings('sticky_header', val ? 'yes' : 'no', false)}
                    disabled={!hasPro}
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
                            disabled={!hasPro}
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
}
