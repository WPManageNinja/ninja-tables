const {__} = wp.i18n;
const {
    PanelBody,
    ToggleControl,
    RadioControl,
    SelectControl,
    TabPanel,
    Button
} = wp.components;

const {
    PanelColorSettings
} = wp.blockEditor;

import {hasPro, customColorCss, tableLibrary} from "../../utils/data";

export default function ColorsTab({tableSettings, updateTableSettings, tableId, instanceId}) {
    const updateColorSetting = (key, value) => {
        updateTableSettings(key, value, false);
        const updatedSettings = {...tableSettings, [key]: value};
        customColorCss(tableId, updatedSettings, instanceId);
    };

    const renderColorSettings = (colors, title = 'Color Settings') => (
        <PanelColorSettings
            title={title}
            initialOpen={true}
            colorSettings={colors.map(({label, key}) => {
                return {
                    value: tableSettings[key] || '',
                    onChange: (value) => updateColorSetting(key, value),
                    label,
                    disableCustomColors: false
                };
            })}
        />
    );

    return (
        <div className="ntb-tab-content label-normalize">
            <PanelBody title={__('General Settings')} initialOpen={true}>
                <RadioControl
                    label={__('Select Color Scheme')}
                    selected={tableSettings.table_color_type || 'pre_defined_color'}
                    options={[
                        {label: __('Pre Defined Scheme'), value: 'pre_defined_color'},
                        {label: __('Custom Scheme'), value: 'custom_color'}
                    ]}
                    onChange={(value) => updateTableSettings('table_color_type', value)}
                />

                {tableSettings.table_color_type === 'pre_defined_color' ? (
                    <SelectControl
                        value={tableSettings.table_color || ''}
                        options={
                            Object.entries(tableLibrary()[tableSettings.library]?.colors || {}).map(
                                ([key, label]) => ({value: key, label})
                            )
                        }
                        onChange={(value) => updateTableSettings('table_color', value)}
                    />
                ) : (
                    <>
                        {renderColorSettings([
                            {label: __('Background'), key: 'table_search_color_primary'},
                            {label: __('Icon'), key: 'table_search_color_secondary'},
                            {label: __('Border'), key: 'table_search_color_border'}
                        ], 'Search Bar Colors')}

                        {renderColorSettings([
                            {label: __('Background'), key: 'table_header_color_primary'},
                            {label: __('Text'), key: 'table_color_header_secondary'},
                            {label: __('Border'), key: 'table_color_header_border'}
                        ], 'Table Header Colors')}

                        <TabPanel
                            className="ninja-color-tabs"
                            activeClass="is-active"
                            tabs={[
                                {name: 'default', title: __('Default')},
                                {name: 'hover', title: __('Hover')}
                            ]}
                        >
                            {(tab) => {
                                const suffix = tab.name === 'default' ? '' : '_hover';
                                return renderColorSettings([
                                    {label: __('Background'), key: `table_color_primary${suffix}`},
                                    {label: __('Text'), key: `table_color_secondary${suffix}`},
                                    {label: __('Border'), key: `table_color_border${suffix}`}
                                ], 'Table Body Colors');
                            }}
                        </TabPanel>

                        <ToggleControl
                            label={__('Use Alternate Color Schema for Table Rows')}
                            checked={tableSettings.alternate_color_status === 'yes'}
                            onChange={(value) => updateColorSetting('alternate_color_status', value ? 'yes' : 'no')}
                        />

                        {tableSettings.alternate_color_status === 'yes' && (
                            <>
                                {renderColorSettings([
                                    {label: __('Background'), key: 'table_alt_2_color_primary'},
                                    {label: __('Text'), key: 'table_alt_2_color_secondary'},
                                    {label: __('Hover Background'), key: 'table_alt_2_color_hover'}
                                ], 'Odd Row Colors')}

                                {renderColorSettings([
                                    {label: __('Background'), key: 'table_alt_color_primary'},
                                    {label: __('Text'), key: 'table_alt_color_secondary'},
                                    {label: __('Hover Background'), key: 'table_alt_color_hover'}
                                ], 'Even Row Colors')}
                            </>
                        )}

                        {renderColorSettings([
                            {label: __('Background'), key: 'table_footer_bg'},
                            {label: __('Active'), key: 'table_footer_active'},
                            {label: __('Border'), key: 'table_footer_border'}
                        ], 'Footer Colors')}
                    </>
                )}

                {!hasPro && (
                    <div className="pro-notice" style={{
                        marginTop: '24px',
                        padding: '16px',
                        backgroundColor: '#f8f9fa',
                        borderRadius: '8px',
                        border: '1px solid #e0e0e0'
                    }}>
                        <p style={{margin: '0 0 12px 0', fontWeight: '500'}}>
                            {__('Color customization is a PRO feature. Please upgrade to pro to apply this feature.')}
                        </p>
                        <Button isPrimary target="_blank"
                                href={'https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade'}>
                            {__('Get Pro')}
                        </Button>
                    </div>
                )}
            </PanelBody>
        </div>
    );
}
