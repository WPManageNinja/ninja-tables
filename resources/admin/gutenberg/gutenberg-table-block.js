const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
const { PanelBody, SelectControl, Placeholder, Spinner, Button } = wp.components;
const { useState, useEffect, useRef } = wp.element;
const { apiFetch } = wp.apiFetch ? wp : wp.components;

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
        }
    },

    edit: function(props) {
        const { attributes, setAttributes } = props;
        const { tableId } = attributes;
        const blockRef = useRef(null);

        // State for tracking preview loading
        const [tableHtml, setTableHtml] = useState('');
        const [isLoading, setIsLoading] = useState(false);
        const [error, setError] = useState(null);

        // Get block props with ref
        const blockProps = useBlockProps ? useBlockProps({
            ref: blockRef
        }) : {
            className: props.className,
            ref: blockRef
        };

        // Get available tables
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

        // Initialize Footable after HTML is loaded - ONLY ONCE
        useEffect(() => {
            if (tableHtml && !isLoading && !error && window.initNinjaTableFootable) {
                // Use a single timeout to initialize - nothing else
                const timer = setTimeout(() => {
                    window.initNinjaTableFootable();
                }, 300);

                return () => clearTimeout(timer);
            }
        }, [tableHtml]);

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Table Settings')} initialOpen={true}>
                        <SelectControl
                            label={__('Select Table')}
                            value={tableId}
                            options={availableTables}
                            onChange={(value) => setAttributes({ tableId: value })}
                        />

                        {tableId && (
                            <div style={{ marginTop: '10px' }}>
                                <Button
                                    isSecondary
                                    onClick={() => {
                                        if (window.initNinjaTableFootable) {
                                            // Force reload by clearing state and reloading
                                            setTableHtml('');
                                            loadTablePreview(tableId);
                                        }
                                    }}
                                >
                                    {__('Reload Table')}
                                </Button>
                            </div>
                        )}
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
                                options={availableTables}
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
                            <Button
                                isSecondary
                                onClick={() => loadTablePreview(tableId)}
                            >
                                {__('Retry')}
                            </Button>
                        </div>
                    ) : (
                        <div
                            className="ninja-tables-preview"
                            dangerouslySetInnerHTML={{ __html: tableHtml }}
                        />
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
