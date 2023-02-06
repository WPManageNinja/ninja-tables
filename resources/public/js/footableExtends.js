(function (F) {
    /**
     * Checks if the supplied string contains the exact given substring.
     * @memberof FooTable.str
     * @function contains
     * @param {string} str - The string to check.
     * @param {string} contains - The string to check for.
     * @param {boolean} [ignoreCase=false] - Whether or not to ignore casing when performing the check.
     * @returns {boolean}
     */
    F.str.containsExact = function (str, contains, ignoreCase) {
        if (F.is.emptyString(str) || F.is.emptyString(contains) || contains.length > str.length) return false;
        if (!ignoreCase) {
            str = str.toLowerCase();
            contains = contains.toLowerCase();
        }

        const ifDigit = /^\d+$/.test(contains);
        if (ifDigit) {
            return isNumber(contains, str);
        }

        // adds cyrillic support
        var regexPattern = isNaN(Number(contains))
            ? '^(?:^|\\W)'+F.str.escapeRegExp(contains) + '(?:$|\\W)$'
            : '^\\b' + F.str.escapeRegExp(contains) + '\\b(?!\\.)$';
        return new RegExp(regexPattern, ignoreCase ? 'i' : '').test(str);
    };

    F.Table.prototype.ninjaTablesGetRows = function(){
        return this.use(FooTable.Export).snapshot;
    };

    F.Column.prototype.sorter = function(a, b){
        if (window.ninjaTablesCustomSorter) {
            return window.ninjaTablesCustomSorter(a, b);
        } else {
            if (typeof a === 'string' && typeof b === 'string') {
                return a.localeCompare(b);
            } else {
                if (a === b) return 0;
                if (a < b) return -1;
                return 1;
            }
        }
    }

    /**
     * when reset button is clicked, this function is called
     */
    F.Filtering.prototype._onSearchButtonClicked = function (e) {
        e.preventDefault();
        var self = e.data.self;
        if (self._filterTimeout != null) clearTimeout(self._filterTimeout);
        var $icon = self.$button.children('.fooicon');
        if ($icon.hasClass('fooicon-remove')) self.clear();
        else {
            var query = self.$input.val();
            if (query.length >= self.min) {
                if (self.exactMatch && !self._exactRegExp.test(query)) {
                    query = '"' + query + '"';
                }
                self.addFilter('search', query);
                self.filter(self.focus);
            }
        }

        const $ = jQuery;
        const table = self.ft.$el[0];
        const tableInstance = $(table).data('ninja_table_instance');
        const useFiltering = window[tableInstance]['init_config']['filtering']['filters'];
        if (useFiltering && useFiltering[0]['hidden']) {
            const getAllCustomFilter = $('.footable-filtering').find('.ninja-custom-filter');
            getAllCustomFilter.each(function (index, el) {
                const elementType = $(el).data('element_type');
                if (elementType == 'select') {
                    const selectElement = $(el).find('select');
                    selectElement.each(function (index, item) {
                        const select = $(item);
                        select[0].sumo.unSelectAll();
                    })
                } else if (elementType === 'radio' || elementType === 'checkbox') {
                    $(this).find('input').prop('checked', false);
                } else {
                    const typedValue = $(el).find('input');
                    typedValue.val('');
                }
            });
        }
    }

    /**
     * @memberof FooTable.Cell
     * Extend it only for add a class in th when the responsive breakpoint is available
     */
    F.Cell.prototype.$create = function(){
        let $ = jQuery;
        if (this.created) return;
        (this.$el = F.is.jq(this.$el) ? this.$el : $('<td/>'))
            .data('value', this.value)
            .contents().detach().end()
            .append(this.format(this.value));

        this._setClasses(this.$el);
        this._setStyle(this.$el);

        this.$detail = $('<tr/>').addClass(this.row.classes.join(' '))
            .data('__FooTableCell__', this)
            .append($('<th/>').addClass('ninja-th-'+this.column.name))
            .append($('<td/>'));

        this.created = true;
    }

    const isNumber = (contains, str) => {
        let arr = str.split(",");
        arr = arr.map(Number);
        return arr.includes(Number(contains));
    }

})(FooTable);
