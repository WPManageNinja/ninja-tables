const { __ }  =  wp.i18n
const {
    RadioControl,
    SelectControl,
    ToggleControl,
    TabPanel,
    Button
}  = wp.components

import {ColorPickerControl} from "../controls/ColorPickerControl";
import {hasPro, customColorCss, tableLibrary} from "../../utils/data";

export default function ColorsTab({ tableSettings, updateTableSettings, tableId, instanceId}) {

    const updateColorSetting = (key, value) => {
        updateTableSettings(key, value, false);
        const updatedSettings = { ...tableSettings, [key]: value };
        customColorCss(tableId, updatedSettings, instanceId);
    };

    return (
        <div className="ninja-tab-content">
            <div className="form_group">
                <h3 className="ninja_inner_title">{__('Select Color Scheme')}</h3>
                <RadioControl
                    selected={tableSettings.table_color_type || 'pre_defined_color'}
                    options={[
                        { label: __('Pre Defined Scheme'), value: 'pre_defined_color' },
                        { label: __('Custom Scheme'), value: 'custom_color' }
                    ]}
                    onChange={(value) => updateTableSettings('table_color_type', value)}
                />
            </div>

            {tableSettings.table_color_type === 'pre_defined_color' ? (
                <div className="form_group">
                    <SelectControl
                        className="form_control"
                        value={tableSettings.table_color || ''}
                        options={
                            Object.entries(tableLibrary()[tableSettings.library]?.colors || {}).map(
                                ([colorKey, colorName]) => ({
                                    label: colorName,
                                    value: colorKey
                                })
                            )
                        }
                        onChange={(value) => updateTableSettings('table_color', value)}
                    />
                </div>
            ) : (
                <div className="form_group ninja_color_customization">
                    <h3 className="ninja_inner_title">{__('Search Bar Colors')}</h3>
                    <div className="ninja_color_blocks" style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                        <ColorPickerControl
                            label="Background"
                            value={tableSettings.table_search_color_primary}
                            onChange={(val) => updateColorSetting('table_search_color_primary', val)}
                            disabled={!hasPro}
                        />
                        <ColorPickerControl
                            label="Icon"
                            value={tableSettings.table_search_color_secondary}
                            onChange={(val) => updateColorSetting('table_search_color_secondary', val)}
                            disabled={!hasPro}
                        />
                        <ColorPickerControl
                            label="Border"
                            value={tableSettings.table_search_color_border}
                            onChange={(val) => updateColorSetting('table_search_color_border', val)}
                            disabled={!hasPro}
                        />
                    </div>

                    <h3 className="ninja_inner_title">{__('Table Header Colors')}</h3>
                    <div className="ninja_color_blocks" style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                        <ColorPickerControl
                            label="Background"
                            value={tableSettings.table_header_color_primary}
                            onChange={(val) => updateColorSetting('table_header_color_primary', val)}
                            disabled={!hasPro}
                        />
                        <ColorPickerControl
                            label="Text"
                            value={tableSettings.table_color_header_secondary}
                            onChange={(val) => updateColorSetting('table_color_header_secondary', val)}
                            disabled={!hasPro}
                        />
                        <ColorPickerControl
                            label="Border"
                            value={tableSettings.table_color_header_border}
                            onChange={(val) => updateColorSetting('table_color_header_border', val)}
                            disabled={!hasPro}
                        />
                    </div>

                    <h3 className="ninja_inner_title">{__('Table Body Colors')}</h3>
                    <TabPanel
                        className="ninja-color-tabs"
                        activeClass="is-active"
                        tabs={[
                            { name: 'default', title: __('Default'), className: 'tab-default' },
                            { name: 'hover', title: __('Hover'), className: 'tab-hover' }
                        ]}
                    >
                        {(tab) => {
                            if (tab.name === 'default') {
                                return (
                                    <div className="ninja_color_blocks" style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                                        <ColorPickerControl
                                            label="Background"
                                            value={tableSettings.table_color_primary}
                                            onChange={(val) => updateColorSetting('table_color_primary', val)}
                                            disabled={!hasPro}
                                        />
                                        <ColorPickerControl
                                            label="Text"
                                            value={tableSettings.table_color_secondary}
                                            onChange={(val) => updateColorSetting('table_color_secondary', val)}
                                            disabled={!hasPro}
                                        />
                                        <ColorPickerControl
                                            label="Border"
                                            value={tableSettings.table_color_border}
                                            onChange={(val) => updateColorSetting('table_color_border', val)}
                                            disabled={!hasPro}
                                        />
                                    </div>
                                );
                            } else {
                                return (
                                    <div className="ninja_color_blocks" style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                                        <ColorPickerControl
                                            label="Background"
                                            value={tableSettings.table_color_primary_hover}
                                            onChange={(val) => updateColorSetting('table_color_primary_hover', val)}
                                            disabled={!hasPro}
                                        />
                                        <ColorPickerControl
                                            label="Text"
                                            value={tableSettings.table_color_secondary_hover}
                                            onChange={(val) => updateColorSetting('table_color_secondary_hover', val)}
                                            disabled={!hasPro}
                                        />
                                        <ColorPickerControl
                                            label="Border"
                                            value={tableSettings.table_color_border_hover}
                                            onChange={(val) => updateColorSetting('table_color_border_hover', val)}
                                            disabled={!hasPro}
                                        />
                                    </div>
                                );
                            }
                        }}
                    </TabPanel>

                    <div className="ninja_switch_wrapper" style={{ margin: '15px 0' }}>
                        <ToggleControl
                            label={__('Use Alternate Color Schema for Table Rows')}
                            checked={tableSettings.alternate_color_status === 'yes'}
                            onChange={(value) => updateColorSetting('alternate_color_status', value ? 'yes' : 'no')}
                            disabled={!hasPro}
                        />
                    </div>

                    {tableSettings.alternate_color_status === 'yes' && (
                        <div className="ninja_alternate_colors">
                            <h3 className="ninja_inner_title">{__('Odd Row Colors')}</h3>
                            <div className="ninja_color_blocks" style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                                <ColorPickerControl
                                    label="Background"
                                    value={tableSettings.table_alt_2_color_primary}
                                    onChange={(val) => updateColorSetting('table_alt_2_color_primary', val)}
                                    disabled={!hasPro}
                                />
                                <ColorPickerControl
                                    label="Text"
                                    value={tableSettings.table_alt_2_color_secondary}
                                    onChange={(val) => updateColorSetting('table_alt_2_color_secondary', val)}
                                    disabled={!hasPro}
                                />
                                <ColorPickerControl
                                    label="Hover Background"
                                    value={tableSettings.table_alt_2_color_hover}
                                    onChange={(val) => updateColorSetting('table_alt_2_color_hover', val)}
                                    disabled={!hasPro}
                                />
                            </div>

                            <h3 className="ninja_inner_title">{__('Even Row Colors')}</h3>
                            <div className="ninja_color_blocks" style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                                <ColorPickerControl
                                    label="Background"
                                    value={tableSettings.table_alt_color_primary}
                                    onChange={(val) => updateColorSetting('table_alt_color_primary', val)}
                                    disabled={!hasPro}
                                />
                                <ColorPickerControl
                                    label="Text"
                                    value={tableSettings.table_alt_color_secondary}
                                    onChange={(val) => updateColorSetting('table_alt_color_secondary', val)}
                                    disabled={!hasPro}
                                />
                                <ColorPickerControl
                                    label="Hover Background"
                                    value={tableSettings.table_alt_color_hover}
                                    onChange={(val) => updateColorSetting('table_alt_color_hover', val)}
                                    disabled={!hasPro}
                                />
                            </div>
                        </div>
                    )}

                    <h3 className="ninja_inner_title">{__('Footer Colors')}</h3>
                    <div className="ninja_color_blocks" style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                        <ColorPickerControl
                            label="Background"
                            value={tableSettings.table_footer_bg}
                            onChange={(val) => updateColorSetting('table_footer_bg', val)}
                            disabled={!hasPro}
                        />
                        <ColorPickerControl
                            label="Active"
                            value={tableSettings.table_footer_active}
                            onChange={(val) => updateColorSetting('table_footer_active', val)}
                            disabled={!hasPro}
                        />
                        <ColorPickerControl
                            label="Border"
                            value={tableSettings.table_footer_border}
                            onChange={(val) => updateColorSetting('table_footer_border', val)}
                            disabled={!hasPro}
                        />
                    </div>

                    {!hasPro && (
                        <div className="pro-notice" style={{
                            margin: '15px 0',
                            padding: '15px',
                            backgroundColor: '#f8f9fa',
                            border: '1px solid #e2e4e7',
                            borderRadius: '4px'
                        }}>
                            <p>{__('Color customization is a PRO feature. Please upgrade to pro to apply this feature.')}</p>
                            <Button isPrimary>{__('Get Pro')}</Button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
