const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor; // Fallback for older WordPress versions
const { PanelBody, SelectControl, Placeholder, Spinner } = wp.components;
const { useState, useEffect } = wp.element;
const { apiFetch } = wp.apiFetch ? wp : wp.components; // Ensure apiFetch is available

// Register the Ninja Tables block
registerBlockType('ninja-tables/table-block', {
    title: __('Ninja Tables'),
    icon: 'grid-view',
    category: 'widgets',
    keywords: [__('table'), __('ninja'), __('data')],
    attributes: {
        tableId: {
            type: 'string',
            default: ''
        },
        customClassName: {
            type: 'string',
            default: ''
        }
    },

    edit: function(props) {
        const { attributes, setAttributes, className } = props;
        const { tableId } = attributes;

        // Add customClassName to attributes for additional styling
        setAttributes({ customClassName: className });

        // State for tracking table preview
        const [tableHtml, setTableHtml] = useState('');
        const [isLoading, setIsLoading] = useState(false);
        const [error, setError] = useState(null);

        // Get block props
        const blockProps = useBlockProps ? useBlockProps() : { className };

        // Get available tables from data passed via wp_localize_script
        const availableTables = window.ninjaTablesGutenberg ?
            window.ninjaTablesGutenberg.availableTables : [];

        // Load table preview when tableId changes
        useEffect(() => {
            if (!tableId) {
                setTableHtml('');
                return;
            }

            loadTablePreview(tableId);
        }, [tableId]);

        // Function to load table preview
        const loadTablePreview = async (id) => {
            if (!id) return;

            setIsLoading(true);
            setError(null);

            try {
                // Use REST API to get table preview
                const response = await apiFetch({
                    path: `/ninja-tables/v1/get-table-preview?table_id=${id}`,
                    method: 'GET'
                });

                if (response && response.success && response.html) {
                    setTableHtml(response.html);
                } else {
                    setError(__('Failed to load table preview.'));
                }
            } catch (err) {
                console.error('Error loading table preview:', err);
                setError(__('Failed to load table preview. Check browser console for details.'));
            } finally {
                setIsLoading(false);
            }
        };

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Table Settings')} initialOpen={true}>
                        <SelectControl
                            label={__('Select Table')}
                            value={tableId}
                            options={[
                                { label: __('-- Select a Table --'), value: '' },
                                ...availableTables
                            ]}
                            onChange={(value) => setAttributes({ tableId: value })}
                        />
                    </PanelBody>
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
                                options={[
                                    { label: __('-- Select a Table --'), value: '' },
                                    ...availableTables
                                ]}
                                onChange={(value) => setAttributes({ tableId: value })}
                            />
                        </Placeholder>
                    ) : isLoading ? (
                        <div className="ninja-tables-loading">
                            <Spinner />
                            <p>{__('Loading table preview...')}</p>
                        </div>
                    ) : error ? (
                        <div className="ninja-tables-error">
                            <p>{error}</p>
                            <button
                                className="components-button is-secondary"
                                onClick={() => loadTablePreview(tableId)}
                            >
                                {__('Retry')}
                            </button>
                        </div>
                    ) : (
                        <div className="ninja-tables-preview" dangerouslySetInnerHTML={{ __html: tableHtml }} />
                    )}
                </div>
            </div>
        );
    },

    save: function() {
        // Return null to use the render_callback for server-side rendering
        return null;
    }
});

// Initialize Footable tables in the editor preview after block updates
wp.data.subscribe(() => {
    setTimeout(() => {
        // Check if jQuery and Footable plugin are available
        if (window.jQuery && typeof window.jQuery.fn.footable !== 'undefined') {
            // Find footable tables in the editor and initialize them
            window.jQuery('.ninja-tables-preview table.footable').each(function() {
                try {
                    window.jQuery(this).footable();
                } catch(e) {
                    console.warn('Error initializing Footable in Gutenberg editor:', e);
                }
            });
        }
    }, 500); // Small delay to ensure DOM is updated
});
