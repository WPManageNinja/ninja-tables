export const manageDataElement = {
    computed: {
        margin() {
            return {
                'margin-bottom': this.item.data.style.margin.bottom + 'px',
                'margin-left': this.item.data.style.margin.left + 'px',
                'margin-right': this.item.data.style.margin.right + 'px',
                'margin-top': this.item.data.style.margin.top + 'px'
            }
        },
        padding() {
            return {
                'padding-top': this.item.data.style.padding.top + 'px',
                'padding-bottom': this.item.data.style.padding.bottom + 'px',
                'padding-left': this.item.data.style.padding.left + 'px',
                'padding-right': this.item.data.style.padding.right + 'px'
            }
        },
        fontSize() {
            return {
                'font-size': (this.item.data.style.fontSize === '' || this.item.data.style.fontSize === 10) ? this.setting.global_styling.options.font_size.value + 'px' : this.item.data.style.fontSize + 'px',
            }
        },
        displayBlock() {
            return {
                'display': 'block'
            }
        },
        displayFlex() {
            return {
                'display': 'flex'
            }
        },
        textAlign() {
            return {
                'text-align': this.item.data.style.alignment
            }
        },
        dBlockAlign() {
            const alignment = this.item.data.style.alignment;
            if (alignment === 'left') {
                return {
                    'margin-right': 'auto'
                }
            } else if (alignment === 'right') {
                return {
                    'margin-left': 'auto',
                }
            }
            return {
                'margin-left': 'auto',
                'margin-right': 'auto'
            }
        },
        color() {
            return {
                'color': this.item.data.style.color === '' || this.item.data.style.color === '#000001' ? this.setting.global_styling.options.color.value : this.item.data.style.color,
            }
        },
        backgroundColor() {
            return {
                'background': this.item.data.style.backgroundColor
            }
        },
        border() {
            return {
                'border': `${this.item.data.style.borderSize}px solid ${this.item.data.style.borderColor}`,
            }
        },
        buttonIconHover() {
            let iconColorHover = this.item.data.style.hoverIconColor;
            let iconColor = this.item.data.style.iconColor;
            if (this.item.data.style.isHover === true) {
                iconColorHover = !this.svgExtension(this.item.data.style.iconName) ? 'transparent' : (iconColorHover === '' ? this.setting.global_styling.options.color.value : iconColorHover)
            } else {
                iconColorHover = !this.svgExtension(this.item.data.style.iconName) ? 'transparent' : (iconColor === '' ? this.setting.global_styling.options.color.value : iconColor)
            }
            return {
                '--icon-color-hover': iconColorHover,
            }
        },
        iconStyle() {
            let data = {};
            let iconFontSize = this.item.data.style.fontSize;
            let iconColor = this.item.data.style.color;
            const fontSize = (iconFontSize === '' || iconFontSize === 10) ? this.setting.global_styling.options.font_size.value : iconFontSize;
            const color = (iconColor === '' || iconColor === '#000001') ? this.setting.global_styling.options.color.value : iconColor;
            data['width'] = fontSize + 'px';
            data['height'] = fontSize + 'px';
            if (this.svgExtension(this.item.data.value)) {
                data['mask-image'] = 'url(' + this.getAsset(this.item.data.value) + ')';
                data['-webkit-mask-image'] = 'url(' + this.getAsset(this.item.data.value) + ')';
                data['background'] = color;
            } else {
                data['background-image'] = 'url(' + this.item.data.value + ')';
                data['background-size'] = 'cover';
            }
            return data;
        },
        iconWithOtherComponent() {
            let data = {};
            let iconFontSize = this.item.data.style.iconFontSize;
            let iconColor = this.item.data.style.iconColor;
            const fontSize = (iconFontSize === '' || iconFontSize === 10) ? this.setting.global_styling.options.font_size.value : iconFontSize;
            const color = (iconColor === '' || iconColor === '#000001') ? this.setting.global_styling.options.color.value : iconColor;
            data['width'] = fontSize + 'px';
            data['height'] = fontSize + 'px';
            if (this.svgExtension(this.item.data.style.iconName)) {
                data['mask-image'] = 'url(' + this.getAsset(this.item.data.style.iconName) + ')';
                data['-webkit-mask-image'] = 'url(' + this.getAsset(this.item.data.style.iconName) + ')';
                data['background'] = color;
            } else {
                data['background-image'] = 'url(' + this.item.data.style.iconName + ')';
                data['background-size'] = 'cover';
            }
            return data;
        },
        buttonStyle() {
            const size = this.item.data.style.buttonSize;
            const isFullWidth = this.item.data.style.fullWidth;

            let width = '90px';
            let height = '30px';

            if (size === 'S') {
                width =  '60px';
                height = '27px';

            } else if (size === 'L') {
                width =  '120px';
                height = '40px';
            }

            return {
                'text-decoration': 'none',
                'border-radius': this.item.data.style.borderRadius + 'px',
                'width': (isFullWidth === 'true' || isFullWidth === true) ? '100%' : width,
                'height': (isFullWidth === 'true' || isFullWidth === true) ? 'auto' : height,
                'text-align': this.item.data.style.contentAlignment,
                'overflow': 'hidden',
                'line-height': 1,
                'display': 'inline-block',
                '--padding-dashboard': (isFullWidth === 'true' || isFullWidth === true) ? '10px' : '5px',
                '--padding-frontend':  (isFullWidth === 'true' || isFullWidth === true) ? '10px' : '0',
            }
        },
        justifyContent() {
            let justifyContent = 'center'
            if (this.item.data.style.alignment === 'left') {
                justifyContent = 'flex-start'
            } else if (this.item.data.style.alignment === 'right') {
                justifyContent = 'flex-end'
            }
            return {
                'justify-content': justifyContent
            }
        },
        lineHeight() {
            return {
                'line-height': this.item.data.style.lineHeight + 'px'
            }
        },
        listStyle() {
            return {
                'padding': 0,
                'margin': 15 + 'px' + 0 + 'px',
                'list-style-position': 'inside',
                'list-style-type': this.item.data.type === 'list' ? this.item.data.style.listStyle : 'none',
            }
        },
        progressBarTextStyle() {
            const progressBarTxtStyle = this.item.data.style;
            const progressbarFontSize = (progressBarTxtStyle.fontSize === '' || progressBarTxtStyle.fontSize === 10) ? this.setting.global_styling.options.font_size.value : progressBarTxtStyle.fontSize;

            return {
                '--progress-bar-text-color': progressBarTxtStyle.color === '' ? this.setting.global_styling.options.color.value : progressBarTxtStyle.color,
                '--progress-bar-font-size': progressbarFontSize + 'px',
            }
        },
        ratingStyle() {
            let ratingSize = this.item.data.style.ratingSize ? this.item.data.style.ratingSize : 12;
            const ratingFontSize = (ratingSize === '' || ratingSize === 10) ? this.setting.global_styling.options.font_size.value : ratingSize;

            return {
                '--rating-font-size': ratingFontSize + 'px',
            }
        },
        showRatingScore() {
            if (this.item.data.style.showRatingValue === "true" || this.item.data.style.showRatingValue === true) {
                return true;
            }
            return false;
        },
        scoreTemplate() {
            const ratingValue = this.item.data.value;
            return Number.isInteger(ratingValue) ? (`${ratingValue}/${this.item.data.style.maxStar}`) : (`${ratingValue.toFixed(1)}/${this.item.data.style.maxStar}`)
        },
        starRatingStyling() {
            const starRatingColor = this.item.data.style.color;
            const globalColor = this.setting.global_styling.options.color.value;
            return starRatingColor === '' ? [globalColor, globalColor, globalColor] : [starRatingColor, starRatingColor, starRatingColor];
        },
        fontWeight() {
            const fontWeightData = this.item.data.style.fontWeight
            if (fontWeightData && fontWeightData.length >= 0) {
                let fontWeight = 'normal'
                let fontStyle = 'normal'
                let textDecoration = 'none'

                for (let i = 0; i < fontWeightData.length; i++) {

                    if (fontWeightData[i] === "bold") {
                        fontWeight = 'bold'
                    }
                    if (fontWeightData[i] === "italic") {
                        fontStyle = 'italic'
                    }
                    if (fontWeightData[i] === "underline") {
                        textDecoration = 'underline'
                    }
                }
                return {
                    'font-weight': fontWeight, 'font-style': fontStyle, 'text-decoration': textDecoration
                }
            }
        },
        borderRadius() {
            const shape = this.item.data.style.shape
            let radius = (shape === 'round') ? 50 : 0
            return {
                'border-radius': radius + '%'
            }
        },
        ribbonSize() {
            const type = this.item.data.style.ribbonType;
            let height = '';
            let width = '';
            if (type === 'bookmark') {
                width = this.item.data.style.bookmarkWidth;
                height = this.item.data.style.bookmarkHeight;
            } else if (type === 'side') {
                width = this.item.data.style.sideWidth;
                height = this.item.data.style.sideHeight;
            } else if (type === 'horizontal') {
                width = this.item.data.style.horizontalWidth;
                height = this.item.data.style.horizontalHeight;
            } else {
                width = this.item.data.style.width;
                height = this.item.data.style.height;
            }
            return {
                'width': width + 'px', 'height': height + 'px'
            }
        },
        xAxisRibbon() {
            const style = this.item.data.style;
            const cellPadding = this.setting.general.options.cell_padding.value;
            if (style.ribbonType === 'corner') {
                return Number(style.cornerXAxis - cellPadding + 10) + 'px';
            } else {
                return Number(style.xAxis - cellPadding + 10) + 'px'
            }
        },
        yAxisRibbon() {
            return Number(this.item.data.style.yAxis - this.setting.general.options.cell_padding.value + 10) + 'px'
        },
        textStyle() {
            return {
                'opacity': this.item.data.value === '' ? '.4' : 1, 'line-height': 1.2
            }
        },
        verticalAlignMiddle() {
            return {
                'vertical-align': 'middle'
            }
        },
        linkAttributes() {
            const rel = this.item.data.style.linkAttributes;
            let relAsString = '';
            if (typeof rel === 'object' && rel.length > 0) {
                relAsString = rel.join(' ');
            }
            return relAsString;
        }
    },
    methods: {
        svgExtension(path) {
            if (path) {
                const last4 = path.slice(-4);
                return (last4.slice(-4) === '.svg' || !last4.includes('.'));
            }
        },
    }
}
