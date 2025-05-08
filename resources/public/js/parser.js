class FormulaParser {
    /**
     * Instantiate the formula parser
     * @return {Object} Instance of formulaParser
     * @see https://github.com/handsontable/formula-parser#api-methods for more
     */
    constructor() {
        // Check if formulaParser is available in window object
        this.formulaParser = window.formulaParser || null;

        if (this.formulaParser) {
            this.instance = new this.formulaParser.Parser()

            this._addFunctionNUMVAL(
                this.instance
            );
            this._addFunctionDATEVAL(
                this.instance
            );
        } else {
            this.instance = false;
        }
    }

    parse(value, decimalSeparator = '.') {
        if(!value || !this.instance) {
            return value;
        }

        // We allow here for multiple formula implementation
        // Here We will Allow like
        // [f]FORMULA1(param1,param2)[/f] <p>[f]FORMULA1(param1,param2)[/f]</p> like this

        let parsableShortcodes = this.advancedParsablesShortcodes(value);
        if(parsableShortcodes) {
            parsableShortcodes.forEach((shortcode) => {
                let formula =  shortcode.replace('[f]', '').replace('[/f]', '');
                if(formula) {
                    let parsed = this.instance.parse(formula);
                    let parsedValue = parsed.result ? parsed.result : '';
                    value = value.replace(shortcode, parsedValue);
                }
            });
        }

        if (this.isParsable(value)) {
            let matched = value.substr(1);
            if (matched) {
                let parsed = this.instance.parse(matched);
                value = parsed.result ? parsed.result : '';
            }
        }

        return value;
    }

    isParsable(value) {
        if(typeof value == "string") {
            return value.indexOf('=') === 0;
        }
        return false;
    }

    advancedParsablesShortcodes(value) {
        if(value.indexOf('[f]', value) == -1) {
            return false;
        }
        let matched = value.match(/\[f][\s\S]*?\[\/f]/g);
        if(!matched) {
            return false;
        }
        return matched;
        var shortCodes = {};
        matched.forEach((match) => {
            shortCodes[match] = match.replace('[f]', '').replace('[/f]', '');
        });
        return shortCodes;
    }

    getSupportedFormulas() {
        // Check if formulaParser is available
        if (!this.formulaParser) {
            return [];
        }

        let formulas = this.formulaParser.SUPPORTED_FORMULAS;

        if (!formulas.includes('NUMVAL')) {
            formulas.push('NUMVAL');
            formulas.push('DATEVAL');
        }

        return formulas;
    }

    getInstance() {
        return this.instance;
    }

    _addFunctionNUMVAL(instance) {
        instance.setFunction('NUMVAL', params => {
            let value = params[0] || '';
            
            if (!value) return value;

            value = value.toString().replace(/[^\d\.,]+/g, '');

            let sep = params[1] || ',';
            if (value.indexOf(sep) > -1) {
                value = value.split(sep).join('');
            } else {
                value = value.split(',').join('');
            }

            let dec = params[2] || '.';
            if (value.indexOf(dec) > -1) {
                value = value.split(dec).join(".");
            }

            let numVal = Number(value);

            return isNaN(numVal) ? value : numVal;
        });
    }

    _addFunctionDATEVAL(instance) {
        instance.setFunction('DATEVAL', params => {
            let value = params[0] || '';
            let format = params[1] || '';
            if (!value || !format) return value;
            return moment(value, format).unix();
        });
    }
}

export default new FormulaParser;
