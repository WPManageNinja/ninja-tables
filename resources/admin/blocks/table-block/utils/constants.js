/**
 * Default table settings
 */
export const DEFAULT_TABLE_SETTINGS = {
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

/**
 * Font family options for the table
 */
export const FONT_FAMILY_OPTIONS = [
    { label: 'theme-font', value: 'inherit' },
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
];

/**
 * Pagination position options
 */
export const PAGINATION_POSITION_OPTIONS = [
    { label: 'Left', value: 'left' },
    { label: 'Center', value: 'center' },
    { label: 'Right', value: 'right' }
];

/**
 * Search position options
 */
export const SEARCH_POSITION_OPTIONS = [
    { label: 'Left', value: 'left' },
    { label: 'Center', value: 'center' },
    { label: 'Right', value: 'right' },
    { label: 'Default', value: '' }
];

/**
 * Toggle position options
 */
export const TOGGLE_POSITION_OPTIONS = [
    {
        label: 'First Column',
        value: 'first',
        help: 'If you use responsive breakdown then the + icon will show at the first visible column'
    },
    {
        label: 'Last Column',
        value: 'last',
        help: 'If you use responsive breakdown then the + icon will show at the last visible column'
    }
];

/**
 * Expand type options for responsive tables
 */
export const EXPAND_TYPE_OPTIONS = [
    {
        label: 'Default',
        value: 'default',
        help: 'Show All the responsive columns data into the responsive drawer'
    },
    {
        label: 'Expand First',
        value: 'expandFirst',
        help: 'This will automatically expand the first row of the table when displayed on a device that hides any columns.'
    },
    {
        label: 'Expand All',
        value: 'expandAll',
        help: 'This will automatically expand all rows of the table when displayed on a device that hides any columns.'
    }
];
