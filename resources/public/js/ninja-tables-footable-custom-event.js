import Event from './EventBus';
import Actions from './ninja-tables-footable-custom-handlers';

Event.on('ninja-tables-apply-conditional-formatting', function(e, $table, config) {
	if(!config || !config.columns || !config.columns.length) {
		return;
	}

    jQuery.each(config.columns, function(colIndex, column) {
    	if(!column || !column.conditions || !column.conditions.length) return;
        // Conditional formatting
        jQuery.each(column.conditions, function(i, condition) {
        	if(condition && condition.targetAction) {
                let action = getActionName(condition);
                if(action in Actions) {
                    let $elements = getElements($table, condition, colIndex, column);
                    Actions[action]($elements, $table, condition, colIndex, column);
                }
			}
        });
    });
});

function getActionName(condition) {
	return condition.targetAction.split('-').map(
		chr => chr.substr(0, 1).toUpperCase() + chr.substr(1)
	).join('');
};

function getElements($table, condition, colIndex, column) {
	let $elements = jQuery({});
	let curentColumnClassPrefix = 'ninja_column_';
	let cellClass = curentColumnClassPrefix + colIndex;

	if (condition.conditionalOperator == 'equal') {
    	$elements = getEquals($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'not-equal') {
    	$elements = getNotEquals($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'contains') {
    	$elements = getContains($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'does-not-contain') {
    	$elements = getDoesNotContains($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'less-than') {
    	$elements = getLessThan($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'less-than-or-equal-to') {
    	$elements = getLessThanOrEqualTo($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'greater-than') {
    	$elements = getGreaterThan($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'greater-than-or-equal-to') {
    	$elements = getGreaterThanOrEqualTo($table, cellClass, condition, column);
	} else if (condition.conditionalOperator == 'between') {
    	$elements = getBetween($table, cellClass, condition, column);
	}

	return $elements;
};

function getEquals($table, cellClass, condition, column) {
	return $table.find('tbody .' + cellClass).filter((i, td) => {
		return jQuery(td).html() == condition.conditionalValue;
	});
}

function getNotEquals($table, cellClass, condition, column) {
	return $table.find('tbody .' + cellClass).filter((i, td) => {
		return jQuery(td).html() != condition.conditionalValue;
	});
}

function getContains($table, cellClass, condition, column) {
	return $table.find(
		'tbody .' + cellClass + ':contains('+condition.conditionalValue+')'
	);
}

function getDoesNotContains($table, cellClass, condition, column) {
	return $table.find(
		'tbody .' + cellClass + ':not(:contains('+condition.conditionalValue+'))'
	);
}

function getLessThan($table, cellClass, condition, column) {
	return $table.find('tbody .' + cellClass).filter((i, td) => {
        let cellValue = column.sortValue(jQuery(td).text());
        return cellValue < column.sortValue(condition.conditionalValue);
	});
}

function getLessThanOrEqualTo($table, cellClass, condition, column) {
	return $table.find('tbody .' + cellClass).filter((i, td) => {
        let cellValue = column.sortValue(jQuery(td).text());
        return cellValue <= column.sortValue(condition.conditionalValue);
	});
}

function getGreaterThan($table, cellClass, condition, column) {
	return $table.find('tbody .' + cellClass).filter((i, td) => {
        let cellValue = column.sortValue(jQuery(td).text());
		return cellValue > column.sortValue(condition.conditionalValue);
	});
}

function getGreaterThanOrEqualTo($table, cellClass, condition, column) {
	return $table.find('tbody .' + cellClass).filter((i, td) => {
        let cellValue = column.sortValue(jQuery(td).text());
		return cellValue >= column.sortValue(condition.conditionalValue);
	});
}

function getBetween($table, cellClass, condition, column) {

	return $table.find('tbody .' + cellClass).filter((i, td) => {
        let cellValue = jQuery(td).text();
        let minVal = condition.conditionalValue;
        let maxVal = condition.conditionalValue2;

		cellValue = column.sortValue(cellValue);
		minVal = column.sortValue(condition.conditionalValue);
		maxVal = column.sortValue(condition.conditionalValue2);

		return cellValue >= minVal && cellValue <= maxVal;
	});
}

function isValidNumber(value, column) {
	return value && column.type == 'numeric' || jQuery.isNumeric(
		value.replace(/[^0-9\.,-]+/g, "")
	);
}
