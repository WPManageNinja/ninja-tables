export default {
	SetCellColor: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.css('color', condition.targetValueColor);
	},
    SetCellBgColor: function($elements, $table, condition, colIndex, column) {
        $elements.length && $elements.css('background-color', condition.targetValueColor);
    },
	ResetCellColorToDefault: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.css('color', 'initial');
	},
    ResetCellBgColorToDefault: function($elements, $table, condition, colIndex, column) {
        $elements.length && $elements.css('background-color', 'initial');
    },
	SetCellContent: function($elements, $table, condition, colIndex, column) {
        if ($elements.length) {
            $elements.each((i, cell) => {
                let $this = jQuery(cell);
                if (condition.targetValue && !$this.hasClass('ninja_column_conditionally_transformed')) {
                	let targetValue = condition.targetValue;
                	let replaced = targetValue.replace(/{row.([^\}]*)}/g, function(match) {
                        let rowKey = match.substring(5, match.length - 1);
                        let defaultValue = '';
                        let separatorIndex = rowKey.indexOf("|");

                        if(separatorIndex !== -1) {
                            defaultValue = rowKey.substring(separatorIndex + 1, rowKey.length);
                            rowKey = rowKey.substring(0, separatorIndex);
                        }
                        return $this.closest('tr').find('td.ninja_clmn_nm_'+rowKey).html() || defaultValue;
                    });
                    $this.html(replaced).addClass('ninja_column_conditionally_transformed');
                }
            });
        }
	},
	SetCellCssClass: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.addClass(condition.targetValue);
	},
	RemoveCellCssClass: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.removeClass(condition.targetValue);
	},
	SetRowColor: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.closest('tr').find('> td').css('color', condition.targetValueColor);
	},
    SetRowBgColor: function($elements, $table, condition, colIndex, column) {
        $elements.length && $elements.closest('tr').find('> td').css('background-color', condition.targetValueColor);
    },
    ResetRowBgColor: function($elements, $table, condition, colIndex, column) {
        $elements.length && $elements.closest('tr').find('> td').css('background-color', 'initial');
    },
	ResetRowColorToDefault: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.closest('tr').find('> td').css('color', 'initial');
	},
	SetRowCssClass: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.closest('tr').addClass(condition.targetValue);
	},
	RemoveRowCssClass: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.closest('tr').removeClass(condition.targetValue);
	},
	SetColumnColor: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.closest('table').find(
			'tbody tr td:nth-child('+(colIndex+1)+')'
		).css('color', condition.targetValueColor);
	},
    SetColumnBgColor: function($elements, $table, condition, colIndex, column) {
        $elements.length && $elements.closest('table').find(
            'tbody tr td:nth-child('+(colIndex+1)+')'
        ).css('background-color', condition.targetValueColor);
    },
	AddColumnCssClass: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.closest('table').find(
			'tbody tr td:nth-child('+(colIndex+1)+')'
		).addClass(condition.targetValue);
	},
    RemoveColumnCssClass: function($elements, $table, condition, colIndex, column) {
		$elements.length && $elements.closest('table').find(
			'tbody tr td:nth-child('+(colIndex+1)+')'
		).removeClass(condition.targetValue);
	},
};
