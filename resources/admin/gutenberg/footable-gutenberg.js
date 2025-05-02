/**
 * Ninja Tables - Footable Integration for Gutenberg
 * Handles FooTable initialization in the Gutenberg editor
 */

(function($) {
    // Enhanced state tracking
    var initializedTables = {};
    var isInitializing = false;
    var currentTableId = null;

    // Main FooTable initialization function
    window.initNinjaTableFootable = function(forceTableId) {
        if (isInitializing) return;
        isInitializing = true;

        try {
            // Store the current table ID if provided
            if (forceTableId) {
                currentTableId = forceTableId;
            }

            // Find all tables in the editor
            var $tables = $('.ninja-tables-gutenberg-preview table.ninja_footable');
            console.log('Ninja Tables: Found ' + $tables.length + ' tables');

            if ($tables.length === 0) {
                console.log('Ninja Tables: No tables found to initialize');
                isInitializing = false;
                return;
            }

            // Always destroy all existing instances first
            cleanupAllFooTableInstances();

            // Process each table
            $tables.each(function() {
                var $table = $(this);
                var tableId = $table.attr('id');

                if (!tableId) {
                    console.log('Ninja Tables: Table has no ID, skipping');
                    return;
                }

                try {
                    // Make sure table is visible for initialization
                    $table.css({
                        'width': '100%',
                        'min-width': '400px',
                        'visibility': 'visible',
                        'display': 'table'
                    });

                    // Find parent and remove loading class
                    var $parent = $table.closest('.footable_parent');
                    if ($parent.length) {
                        $parent.removeClass('loading_ninja_table');
                    }

                    // Basic config
                    var config = {
                        toggleColumn: 'first',
                        breakpoints: {
                            phone: 480,
                            tablet: 767
                        },
                        filtering: {
                            enabled: true,
                            placeholder: 'Search',
                            delay: 100
                        },
                        paging: {
                            enabled: true,
                            size: 10,
                            limit: 10,
                            countFormat: '{CP} of {TP}'
                        },
                        sorting: {
                            enabled: true
                        }
                    };

                    // Initialize FooTable
                    $table.footable(config);
                    initializedTables[tableId] = true;
                    console.log('Ninja Tables: Initialized table ' + tableId);
                } catch(e) {
                    console.error('Ninja Tables: Error initializing table', e);
                }
            });
        } catch(e) {
            console.error('Ninja Tables: Initialization error', e);
        } finally {
            isInitializing = false;
        }
    };

    /**
     * Cleanup all FooTable instances
     */
    function cleanupAllFooTableInstances() {
        try {
            for (var tableId in initializedTables) {
                if (initializedTables.hasOwnProperty(tableId)) {
                    try {
                        if (FooTable && FooTable.get) {
                            var ft = FooTable.get('#' + tableId);
                            if (ft) {
                                console.log('Ninja Tables: Destroying instance for', tableId);
                                ft.destroy();
                            }
                        }
                    } catch(e) {
                        console.warn('Ninja Tables: Could not destroy instance for', tableId, e);
                    }
                }
            }
            initializedTables = {};
        } catch(e) {
            console.error('Ninja Tables: Error cleaning up instances', e);
        }
    }

    /**
     * Reset the initialization state
     * @param {string} tableId - Optional specific table ID
     */
    window.resetNinjaTableState = function(tableId) {
        cleanupAllFooTableInstances();
    };

    // Run initialization when document is ready
    $(document).ready(function() {
        // Initial attempt
        setTimeout(function() {
            window.initNinjaTableFootable();
        }, 1000);
    });

    // Watch for Gutenberg block selection
    if (wp && wp.data && wp.data.subscribe) {
        var blockChangeTimer = null;
        var lastSelectedBlockId = null;

        wp.data.subscribe(function() {
            var editor = wp.data.select('core/block-editor') || wp.data.select('core/editor');
            if (!editor) return;

            var selectedBlock = editor.getSelectedBlock();

            if (selectedBlock &&
                selectedBlock.name === 'ninja-tables/table-block' &&
                selectedBlock.clientId !== lastSelectedBlockId) {

                lastSelectedBlockId = selectedBlock.clientId;

                clearTimeout(blockChangeTimer);
                blockChangeTimer = setTimeout(function() {
                    // Always clean up and force reinitialization when block changes
                    cleanupAllFooTableInstances();
                    window.initNinjaTableFootable(selectedBlock.attributes.tableId);
                }, 500);
            }
        });
    }
})(jQuery);
