const { __ }  =  wp.i18n
export const ColorPickerControl = ({label, value, onChange, disabled = false}) => {
    return (
        <div className="ninja_color_block">
            <label style={{display: 'block', marginBottom: '5px'}}>{__(label)}</label>
            <div className="components-base-control">
                <input
                    type="color"
                    value={value || '#ffffff'}
                    onChange={(e) => onChange(e.target.value)}
                    disabled={disabled}
                    style={{
                        width: '100%',
                        height: '30px',
                        padding: '0',
                        cursor: disabled ? 'not-allowed' : 'pointer'
                    }}
                />
            </div>
        </div>
    );
};
