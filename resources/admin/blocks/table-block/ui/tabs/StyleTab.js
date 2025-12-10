const {
    CheckboxControl,
    RadioControl,
    Tooltip,
    PanelBody
} = wp.components
const {__} = wp.i18n
import {tableLibrary, hasPro} from "../../utils/data";
import {isTruthy} from "../../utils/helpers";

export default function StyleTab({tableSettings, updateTableSettings}) {
    const libs = tableLibrary();
    const currentTableLibs = libs[tableSettings.library]?.css_libs || {};
    const currentLib = currentTableLibs[tableSettings.css_lib] || {};
    const availableStyles = currentLib?.styles || [];

    const renderStylingLibrarySection = () => (
        <div className="form_group">
            <RadioControl
                selected={tableSettings.css_lib}
                options={Object.entries(currentTableLibs).map(([key, lib]) => ({
                    label: (
                        <span>
                            {lib.title}
                            <Tooltip text={__(lib.description)}>
                                <span className="dashicons dashicons-info"
                                      style={{marginLeft: '5px', fontSize: '16px'}}>
                                </span>
                            </Tooltip>
                        </span>
                    ),
                    value: key
                }))}
                onChange={(value) => updateTableSettings('css_lib', value)}
            />
        </div>
    );

    const renderStylesSection = () => {
        if (!availableStyles.length) return null;

        return (
            <div className="form_group label-normalize">
                <div className="styles-checkboxes">
                    {availableStyles.map(style => (
                        <div key={style.key} className="style-checkbox-row">
                            <CheckboxControl
                                label={
                                    <span>
                                        {style.title}
                                        <Tooltip text={__(style.description)}>
                                            <span className="dashicons dashicons-info"
                                                  style={{marginLeft: '5px', fontSize: '16px'}}/>
                                        </Tooltip>
                                    </span>
                                }
                                checked={(tableSettings.css_classes || []).includes(style.key)}
                                onChange={(checked) => {
                                    let cssClasses = Array.isArray(tableSettings.css_classes)
                                        ? [...tableSettings.css_classes]
                                        : [];

                                    if (checked) {
                                        if (!cssClasses.includes(style.key)) cssClasses.push(style.key);
                                    } else {
                                        cssClasses = cssClasses.filter(cls => cls !== style.key);
                                    }

                                    updateTableSettings('css_classes', cssClasses);
                                }}
                            />
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    const renderFeaturesSection = () => (
        <div className="form_group label-normalize">
            <CheckboxControl
                label={__('Show Table Title')}
                checked={isTruthy(tableSettings.show_title)}
                onChange={(value) => updateTableSettings('show_title', value)}
            />

            <CheckboxControl
                label={__('Show Table Description')}
                checked={isTruthy(tableSettings.show_description)}
                onChange={(value) => updateTableSettings('show_description', value)}
            />

            <CheckboxControl
                label={__('Enable the visitor to filter or search the table')}
                checked={isTruthy(tableSettings.enable_search)}
                onChange={(value) => updateTableSettings('enable_search', value)}
            />

            <CheckboxControl
                label={__('Enable sorting of the table by the visitor')}
                checked={isTruthy(tableSettings.column_sorting)}
                onChange={(value) => updateTableSettings('column_sorting', value)}
                disabled={!hasPro}
            />

            <CheckboxControl
                label={__('Hide Header Row')}
                checked={isTruthy(tableSettings.hide_header_row)}
                onChange={(value) => updateTableSettings('hide_header_row', value)}
            />

            <CheckboxControl
                label={__('Hide All Borders')}
                checked={isTruthy(tableSettings.hide_all_borders)}
                onChange={(value) => updateTableSettings('hide_all_borders', value)}
            />

            <CheckboxControl
                label={__('Hide empty items on responsive breakdown')}
                checked={isTruthy(tableSettings.hide_on_empty)}
                onChange={(value) => updateTableSettings('hide_on_empty', value)}
                disabled={!hasPro}
            />

            <CheckboxControl
                label={__('Hide Labels on responsive breakdown')}
                checked={isTruthy(tableSettings.hide_responsive_labels)}
                onChange={(value) => updateTableSettings('hide_responsive_labels', value)}
                disabled={!hasPro}
            />
        </div>
    );

    const renderStackableConfigSection = () => (
        <div className="form_group label-normalize">
            <CheckboxControl
                label={
                    <span>
                      {__('Enable Stackable Table')}
                        <Tooltip
                            text={__('With stackable table, You can show your rows as list item. You can target by device width')}>
                            <span className="dashicons dashicons-info"
                                  style={{marginLeft: '5px', fontSize: '16px'}}>
                            </span>
                        </Tooltip>
                    </span>
                }

                checked={tableSettings.stackable === 'yes'}
                onChange={(val) => updateTableSettings('stackable', val ? 'yes' : 'no', false)}
            />

            {tableSettings.stackable === 'yes' && (
                <>
                    <h4 className="ninja_inner_title">{__('Target Devices')}</h4>
                    {['xs', 'sm', 'md', 'lg'].map(device => (
                        <CheckboxControl
                            key={device}
                            label={device === 'xs' ? __('Mobile') :
                                device === 'sm' ? __('Tablet') :
                                    device === 'md' ? __('Laptop') :
                                        __('Large Devices (iMac)')}
                            checked={(tableSettings.stacks_devices || []).includes(device)}
                            onChange={(checked) => {
                                const devices = Array.isArray(tableSettings.stacks_devices) ? [...tableSettings.stacks_devices] : [];
                                if (checked) {
                                    if (!devices.includes(device)) devices.push(device);
                                } else {
                                    const index = devices.indexOf(device);
                                    if (index !== -1) devices.splice(index, 1);
                                }
                                updateTableSettings('stacks_devices', devices, false);
                            }}
                        />
                    ))}

                    <h4 className="ninja_inner_title">{__('Stacked Appearance')}</h4>
                    {[
                        {key: 'hide_stacked_th', label: __('Hide column headings')},
                        {key: 'ninja_stacked_no_cell_border', label: __('Hide internal borders')}
                    ].map(option => (
                        <CheckboxControl
                            key={option.key}
                            label={option.label}
                            checked={(tableSettings.stacks_appearances || []).includes(option.key)}
                            onChange={(checked) => {
                                const appearances = Array.isArray(tableSettings.stacks_appearances) ? [...tableSettings.stacks_appearances] : [];
                                if (checked) {
                                    if (!appearances.includes(option.key)) appearances.push(option.key);
                                } else {
                                    const index = appearances.indexOf(option.key);
                                    if (index !== -1) appearances.splice(index, 1);
                                }
                                updateTableSettings('stacks_appearances', appearances, false);
                            }}
                        />
                    ))}
                </>
            )}
        </div>
    );

    return (
        <div className="ntb-tab-content">
            <PanelBody title={__('Select Styling Library')} initialOpen={true}>
                {renderStylingLibrarySection()}
            </PanelBody>
            <PanelBody title={__('Select Styles')} initialOpen={true}>
                {renderStylesSection()}
            </PanelBody>
            <PanelBody title={__('Table Features')} initialOpen={true}>
                {renderFeaturesSection()}
            </PanelBody>
            <PanelBody title={__('Stackable Table Configuration')} initialOpen={true}>
                {renderStackableConfigSection()}
            </PanelBody>
        </div>
    );
}
