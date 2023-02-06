export const manageRowColumn = {
    data() {
        return {
            tdIds: [],
            merge: {
                items: [],
                rowColumn: [],
                history: {},
                visible: false
            },
            split: {
                visible: false
            }
        }
    },
    methods: {
        addTopRow() {
            this.tableData.data.unshift(this.row())
            this.tableData.table.tr = Number(this.tableData.table.tr) + 1
            this.updateHistoryRowIndex()
        },
        addBottomRow() {
            this.tableData.data.push(this.row())
            this.tableData.table.tr = Number(this.tableData.table.tr) + 1;
            this.updateHistoryRowIndex()
        },
        addLeftColumn() {
            this.tableData.data.forEach((row, rowIndex) => {
                this.$set(row.rows, this.columnName, this.defaultTd())
            })
            this.tableData.headers.unshift(this.columnName);
            this.tableData.table.tc = Number(this.tableData.table.tc) + 1;
            this.updateHistoryColumnIndex();
        },
        addRightColumn() {
            this.tableData.data.forEach((row, rowIndex) => {
                this.$set(row.rows, this.columnName, this.defaultTd())
            })
            this.tableData.headers.push(this.columnName);
            this.tableData.table.tc = Number(this.tableData.table.tc) + 1;
            this.updateHistoryColumnIndex();
        },
        defaultItem() {
            const placeholderItem = this.initialData.components.general.fields[0];
            return {
                id: this.id(),
                data: this.deepClone(placeholderItem)
            }
        },
        row() {
            let data = {}
            this.tableData.headers.forEach(key => {
                data[key] = this.defaultTd()
            })
            return {
                rows: data,
                style: this.trStyle()
            }
        },
        defaultTd() {
            return {
                'style': this.tdStyle(),
                'columns': [this.defaultItem()]
            }
        },
        tdStyle(style = {}) {
            return {
                tdId: this.id(8),
                backgroundColor: style.backgroundColor ? style.backgroundColor : '',
                columnWidth: style.columnWidth ? style.columnWidth : '',
                emptyCell: style.emptyCell ? style.emptyCell : '',
                verticalAlignment: style.verticalAlignment ? style.verticalAlignment : '',
                rowspan: (style.rowspan || style.rowspan === 0) ? style.rowspan : 1,
                colspan: (style.colspan || style.colspan === 0) ? style.colspan : 1,
                highlighted: {
                    active: style.highlighted ? style.highlighted.active : false,
                    height: style.highlighted ? style.highlighted.height : 10,
                    shadowColor: style.highlighted ? style.highlighted.shadowColor : '#888',
                    offset_x: style.highlighted ? style.highlighted.offset_x : 0,
                    offset_y: style.highlighted ? style.highlighted.offset_y : 10,
                    blur_radius: style.highlighted ? style.highlighted.blur_radius : 10,
                }
            }
        },
        trStyle(style = {}) {
            return {
                trId: this.id(7),
                backgroundColor: style.backgroundColor ? style.backgroundColor : '',
                rowHeight: style.rowHeight ? style.rowHeight : ''
            }
        },
        copyRow(selectedRow = {}, rowIndex = this.table.rowIndex) {
            let data = {};
            let row = this.isEmpty(selectedRow) ? this.table.row : selectedRow;
            this.tableData.headers.forEach(header => {
                let column = row.rows[header];
                let columns = [];
                column.columns.forEach(item => {
                    const items = {
                        id: this.id(),
                        data: this.deepClone(item.data)
                    }
                    columns.push(items)
                })
                data[header] = {
                    style: this.tdStyle(column.style),
                    columns: columns
                }
            })

            return {
                style: this.trStyle(row.style),
                rows: data
            };
        },
        removeRow() {
            const style = this.selectedColumnStyle;
            const removeRow = (index) => {
                this.tableData.data.splice(index, 1);
                this.tableData.table.tr = Number(this.tableData.table.tr) - 1;
                this.updateHistoryRowIndex();
            }
            const movedDataToNextRow = (column, index, header) => {
                let style = column.style;
                let columns = column.columns;
                style.rowspan = style.rowspan - 1;
                let nextColumn = this.tableData.data[index + 1].rows[header];
                nextColumn.columns = columns;
                nextColumn.style = style;
            }
            const rowIndex = this.table.rowIndex;
            const rows = this.mergedIndexes('rowIndexes').sort();
            const rowSpans = this.selectedRowSpans();

            if (this.allEqual(rowSpans)) {
                removeRow(rowIndex)
            } else if (style && style.rowspan > 1) {
                rows.forEach(index => {
                    this.tableData.headers.forEach(header => {
                        let column = this.tableData.data[index] && this.tableData.data[index].rows[header];
                        if (index === rowIndex) {
                            let history = this.merge.history[column.style.tdId];
                            if (history) {
                                delete this.merge.history[column.style.tdId];
                            }
                        } else if (column.style.rowspan > 1) {
                            movedDataToNextRow(column, index, header)
                            this.updateHistoryByRow(column.style.tdId, index, 'pop')
                        }
                    });
                })
                this.$nextTick(() => {
                    let length = rows.length - style.rowspan;
                    rows.splice(0, length);
                    rows.reverse().forEach(index => {
                        removeRow(index)
                    })
                })
            } else {
                rows.forEach(index => {
                    this.tableData.headers.forEach(header => {
                        let column = this.tableData.data[index] && this.tableData.data[index].rows[header];
                        if ((index === rowIndex) && column.style.rowspan > 1) {
                            movedDataToNextRow(column, index, header)
                            this.updateHistoryByRow(column.style.tdId, index, 'pop')
                        } else if (column && column.style.rowspan > 1 && index !== Math.max(...rows)) {
                            column.style.rowspan = column.style.rowspan - 1;
                            this.updateHistoryByRow(column.style.tdId, index, 'pop')
                        }
                    });
                })
                this.$nextTick(() => {
                    removeRow(rowIndex)
                })
            }
            this.clearMerge();
        },
        removeColumn() {
            const style = this.selectedColumnStyle;
            const deleteColumn = (columnIndex) => {
                const index = this.tableData.headers.indexOf(this.headerName(columnIndex));
                if (index > -1) {
                    this.tableData.data.forEach(item => {
                        delete item.rows[this.headerName(columnIndex)];
                    })
                    this.tableData.headers.splice(index, 1);
                    this.tableData.table.tc = Number(this.tableData.table.tc) - 1
                }
                this.updateHistoryColumnIndex();
            }

            const movedDataToNextColumn = (row, column, index, selectedIndex = null) => {
                let style = column.style;
                let columns = column.columns;
                if (column.style.colspan > 1) {
                    style.colspan = style.colspan - 1;
                    if (columnIndex === selectedIndex ? selectedIndex : cols[0]) {
                        let nextColumn = row.rows[this.headerName(index + 1)];
                        nextColumn.columns = columns;
                        nextColumn.style = style;
                    }
                    this.updateHistoryByColumn(index + 1, column.style.tdId, 'pop')
                }
            }

            const columnIndex = this.table.columnIndex;
            const cols = this.mergedIndexes('columnIndexes').sort();
            const colSpans = this.selectedColSpans();

            if (this.allEqual(colSpans)) {
                deleteColumn(columnIndex)
            } else if (style && style.colspan > 1) {
                cols.forEach(index => {
                    this.tableData.data.forEach(row => {
                        let column = row.rows[this.headerName(index)];
                        if (index === columnIndex) {
                            let history = this.merge.history[column.style.tdId];
                            if (history) {
                                delete this.merge.history[column.style.tdId];
                            }
                        } else {
                            movedDataToNextColumn(row, column, index)
                        }
                    })
                })
                this.$nextTick(() => {
                    let length = cols.length - style.colspan;
                    cols.splice(0, length);
                    cols.reverse().forEach(index => {
                        deleteColumn(index)
                    })
                })
            } else {
                cols.forEach(index => {
                    this.tableData.data.forEach(row => {
                        let column = row.rows[this.headerName(index)];
                        if ((index === columnIndex) && column.style.colspan > 1) {
                            movedDataToNextColumn(row, column, index, index)
                        } else if (column && column.style.colspan > 1 && index !== Math.max(...cols)) {
                            column.style.colspan = column.style.colspan - 1;
                            this.updateHistoryByColumn(index + 1, column.style.tdId, 'pop')
                        }
                    })
                })
                this.$nextTick(() => {
                    deleteColumn(columnIndex)
                })
            }
            this.clearMerge();
        },
        duplicateRow() {
            const style = this.selectedColumnStyle;
            let rowSpans = this.selectedRowSpans();
            let rows = this.mergedIndexes('rowIndexes').sort();
            rows = rows.length > 0 ? rows : [this.table.rowIndex];
            const rowIndex = this.table.rowIndex;

            const duplicateRow = (rowIndex, row = {}) => {
                this.tableData.data.splice(rowIndex + 1, 0, this.copyRow(row, rowIndex));
                this.tableData.table.tr = Number(this.tableData.table.tr) + 1;
                this.updateHistoryRowIndex();
            }

            if (this.allEqual(rowSpans)) {
                duplicateRow(rowIndex)
            } else if (style && style.rowspan > 1 || style.colspan > 1) {
                // Todo: Need to update duplicate row system
                let index = 0;
                for (let i = rowIndex; i <= Math.max(...rows); i++) {
                    let row = this.tableData.data[i];
                    duplicateRow(Math.max(...rows) + index, row);
                    index++;
                    // Update merge history
                    this.tableData.headers.forEach((header, hIndex) => {
                        let column = row && row.rows[header];
                        let hRow = this.tableData.data[Math.max(...rows) + index];
                        let hColumn = hRow && hRow.rows[header];
                        // Todo: need to improve
                        if (column && column.style.rowspan > 1 || column.style.colspan > 1) {
                            const history = this.merge.history[column.style.tdId];
                            const cpHistory = this.deepClone(history);
                            if (hColumn && hColumn.style.rowspan > 1 || hColumn.style.colspan > 1) {
                                this.merge.history[hColumn.style.tdId] = cpHistory;
                                this.updateHistoryRowIndex();
                            }
                        }
                        if (column.style.rowspan > 1 && Math.min(...rows) !== i && rowIndex === Math.min(...rows)) {
                            let his = this.merge.history[column.style.tdId];
                            let temp = [];
                            let columnIndexes = [];
                            his.forEach((item, ind) => {
                                columnIndexes.push(item.columnIndex)
                            })
                            let uniqueColumns1 = [...new Set(columnIndexes)];
                            let uniqueColumns2 = [...new Set(columnIndexes)];
                            let mergedColumns = uniqueColumns1.concat(uniqueColumns2)

                            his.forEach((item, ind) => {
                                if (Math.min(...rows) !== item.rowIndex && item.rowIndex <= Math.max(...rows)) {
                                    temp.push({rowIndex: item.rowIndex, columnIndex: mergedColumns[ind]})
                                }
                            })

                            this.merge.history[column.style.tdId] = temp;
                            column.style.rowspan = column.style.rowspan - 1;
                            column.columns = [this.defaultItem()];

                        } else if (i === rowIndex && hColumn.style.rowspan === 0 && (rowSpans[hIndex - 1] === 0 || rowSpans[hIndex + 1] === 0)) {
                            hColumn.style.colspan = 1;
                            hColumn.style.rowspan = 1;
                            hColumn.columns = [this.defaultItem()];
                        }
                    })
                }
            } else {
                duplicateRow(rowIndex)
                for (let i = Math.min(...rows); i <= rowIndex; i++) {
                    let row = this.tableData.data[i];
                    (Object.entries(row.rows)).forEach((item) => {
                        let column = item[1];
                        if (column.style.rowspan > 1) {
                            column.style.rowspan = column.style.rowspan + 1;
                            this.updateHistoryByRow(column.style.tdId, rowIndex, 'push')
                        }
                    })
                }
                if (this.greaterThanOneNumber(rowSpans, 1)) {
                    rowSpans = this.selectedRowSpans(this.tableData.data[rowIndex]);
                }
                let newRow = this.tableData.data[rowIndex + 1];
                rowSpans.forEach((span, columnIndex) => {
                    if (span === 0 || span > 1) {
                        newRow.rows[this.headerName(columnIndex)].style.rowspan = 0;
                    }
                })
            }
            this.clearMerge();
        },
        duplicateColumn() {
            const style = this.selectedColumnStyle;
            const columnIndex = this.table.columnIndex;
            const cols = this.mergedIndexes('columnIndexes').sort();
            let colSpans = this.selectedColSpans();
            const duplicate = (colIndex, index = 0, cond = '') => {
                this.tableData.data.forEach((row, rowIndex) => {
                    let columns = []
                    const column = row.rows[this.headerName(colIndex)];
                    column.columns.forEach(item => {
                        const items = {
                            id: this.id(),
                            data: this.deepClone(item.data)
                        }
                        columns.push(items)
                    })
                    let cloneColumn = {
                        style: this.tdStyle(column.style),
                        columns: columns
                    }

                    if (cond === 'else') {
                        if (colIndex === Math.min(...cols) && (column.style.colspan > 1 || column.style.colspan === 0)) {
                            column.style.colspan = 0;
                        } else if (colIndex !== Math.min(...cols) && column.style.colspan > 1) {
                            column.style.colspan = 0
                            column.style.rowspan = 0
                        }
                    } else if (cond === 'elseif') {
                        if (Math.min(...cols) !== colIndex && column.style.colspan > 1) {
                            // Todo: need to improve
                            column.style.colspan = column.style.colspan - 1;
                            column.columns = [this.defaultItem()];
                        } else if (cloneColumn.style.colspan === 0 && colSpans[rowIndex] === 0 && (colSpans[rowIndex - 1] === 0 || colSpans[rowIndex + 1] === 0)) {
                            // Todo: need to improve
                            cloneColumn.style.colspan = 1;
                            cloneColumn.style.rowspan = 1;
                            cloneColumn.columns = [this.defaultItem()];
                        }
                    }

                    this.$set(row.rows, this.columnName, cloneColumn);

                    let history = this.merge.history[column.style.tdId];
                    if (history) {
                        const cloneHistory = this.deepClone(history);
                        if (cloneColumn.style.colspan > 1) {
                            this.merge.history[cloneColumn.style.tdId] = cloneHistory;
                        }
                    }
                })

                const position = cond === 'else' ? columnIndex : cols.length > 0 ? Math.max(...cols) + index : columnIndex;
                this.tableData.headers.splice(position, 0, this.columnName);
                this.tableData.table.tc = Number(this.tableData.table.tc) + 1;
                this.updateHistoryColumnIndex();
            }

            if (this.allEqual(colSpans)) {
                duplicate(columnIndex)
            } else if (style && style.colspan > 1) {
                let length = cols.length - style.colspan;
                cols.splice(0, length);
                cols.forEach((columnIndex, index) => {
                    duplicate(columnIndex, ++index, 'elseif')
                })
            } else {
                cols.forEach(index => {
                    this.tableData.data.forEach(row => {
                        let column = row.rows[this.headerName(index)];
                        if (column.style.colspan > 1) {
                            column.style.colspan = column.style.colspan + 1;
                            this.updateHistoryByColumn(index, column.style.tdId, 'push')
                        }
                    })
                })
                duplicate(columnIndex, 0, 'else')
            }
            this.clearMerge();
        },
        insertColumnBefore(index = this.table.columnIndex, position = 'before') {
            const insertColumn = (position) => {
                this.tableData.data.forEach((row, rowIndex) => {
                    this.$set(row.rows, this.columnName, this.defaultTd())
                })
                this.tableData.headers.splice(position, 0, this.columnName);
                this.tableData.table.tc = Number(this.tableData.table.tc) + 1;
                this.updateHistoryColumnIndex();
            }

            const cpColIndex = this.deepClone(index);
            const style = this.selectedColumnStyle;
            const colSpan = style && style.colspan;
            const cols = this.mergedIndexes('columnIndexes').sort();
            let colSpans = this.selectedColSpans();

            if ((position === 'before') && (this.allEqual(colSpans) || (cpColIndex === Math.min(...cols)))) {
                insertColumn(index)
            } else if ((position === 'after') && ((Math.max(...cols) === (index - 1)) || this.allEqual(colSpans))) {
                if (colSpan > 1) {
                    index = Math.max(...cols) + 1;
                }
                insertColumn(index)
            } else {
                insertColumn(index)
                for (let i = Math.min(...cols); i <= cpColIndex; i++) {
                    this.tableData.data.forEach((row, index) => {
                        let column = row.rows[this.headerName(i)];
                        if (this.greaterThanOneNumber(colSpans, 1)) {
                            colSpans = this.selectedColSpans(cpColIndex - 1);
                        }
                        if (column.style.colspan > 1) {
                            column.style.colspan = column.style.colspan + 1;
                            this.updateHistoryByColumn(cpColIndex, column.style.tdId, 'push')
                        } else if (colSpans[index] === 0 || colSpans[index] > 1) {
                            column.style.colspan = 0;
                            column.columns = [];
                        }
                    })
                }
            }
            this.clearMerge();
        },
        insertColumnAfter() {
            this.insertColumnBefore(this.table.columnIndex + 1, 'after')
        },
        mergedIndexes(type = 'rowIndexes') {
            const mergedHistory = Object.entries(this.merge.history);
            mergedHistory.map(merge => {
                let history = merge[1];
                history.map(item => {
                    if (type === 'rowIndexes' && (item.rowIndex === this.table.rowIndex) && (!this.tdIds.includes(merge[0]))) {
                        this.tdIds.push(merge[0])
                    } else if (type === 'columnIndexes' && (item.columnIndex === this.table.columnIndex) && (!this.tdIds.includes(merge[0]))) {
                        this.tdIds.push(merge[0])
                    }
                })
            });
            let indexes = [];
            this.tdIds.forEach(tdId => {
                let history = this.merge.history[tdId];
                if (history) {
                    history.map(item => {
                        indexes.push(type === 'rowIndexes' ? item.rowIndex : item.columnIndex)
                    })
                }
            })
            return [...new Set(indexes)];
        },
        selectedColSpans(colIndex = this.table.columnIndex) {
            let colSpans = [];
            this.tableData.data.forEach((row, index) => {
                let column = row.rows[this.headerName(colIndex)];
                colSpans.push(column.style.colspan)
            })
            return colSpans;
        },
        selectedRowSpans(singleRow = {}) {
            const row = this.isEmpty(singleRow) ? this.table.row : singleRow;
            let rowSpans = [];
            row && Object.values(row.rows).forEach((column) => {
                rowSpans.push(column.style.rowspan)
            });
            return rowSpans;
        },
        insertRowBefore(index = this.table.rowIndex, position = 'before') {
            const style = this.selectedColumnStyle;
            const rowSpan = style && style.rowspan;

            const insertRow = (index) => {
                this.tableData.data.splice(index, 0, this.row())
                this.tableData.table.tr = Number(this.tableData.table.tr) + 1;
                this.updateHistoryRowIndex();
            }

            const cpRowIndex = this.deepClone(index);
            const rows = this.mergedIndexes('rowIndexes');
            let rowSpans = this.selectedRowSpans();

            if ((position === 'before') && (this.allEqual(rowSpans) || (cpRowIndex === Math.min(...rows)))) {
                insertRow(index)
            } else if ((position === 'after') && ((Math.max(...rows) === (index - 1)) || this.allEqual(rowSpans))) {
                if (rowSpan > 1) {
                    index = Math.max(...rows) + 1;
                }
                insertRow(index)
            } else {
                insertRow(index)
                for (let i = Math.min(...rows); i < cpRowIndex; i++) {
                    let row = this.tableData.data[i];
                    (Object.entries(row.rows)).forEach((item) => {
                        let column = item[1];
                        if (column.style.rowspan > 1) {
                            column.style.rowspan = column.style.rowspan + 1;
                            this.updateHistoryByRow(column.style.tdId, cpRowIndex, 'push')
                        }
                    })
                }
                if (this.greaterThanOneNumber(rowSpans, 1)) {
                    rowSpans = this.selectedRowSpans(this.tableData.data[index - 1]);
                }
                let newRow = this.tableData.data[index];
                rowSpans.forEach((span, columnIndex) => {
                    if (span === 0 || span > 1) {
                        newRow.rows[this.headerName(columnIndex)].style.rowspan = 0;
                    }
                })
            }
            this.clearMerge();
        },
        updateHistoryColumnIndex() {
            this.tableData.data.forEach((row, index) => {
                this.tableData.headers.forEach((header, columnIndex) => {
                    const column = row.rows[header];
                    const history = this.merge.history[column.style.tdId];
                    if (history) {
                        history.forEach((item, i) => {
                            item.columnIndex = columnIndex + (Math.floor(i / 2))
                        })
                    }
                })
            })
        },
        updateHistoryRowIndex() {
            this.tableData.data.forEach((row, index) => {
                const columns = Object.values(row.rows);
                columns.forEach((column) => {
                    const history = this.merge.history[column.style.tdId];
                    if (history) {
                        const columnIndexes = history.map((item) => {
                            return item.columnIndex;
                        });
                        const columnEquals = this.allEqual(columnIndexes);

                        if (columnEquals) {
                            history.forEach((item, i) => {
                                item.rowIndex = index + i;
                            })
                        } else {
                            history.forEach((item, i) => {
                                item.rowIndex = index + (Math.floor(i / 2));
                            })
                        }
                    }
                })
            })
        },
        insertRowAfter() {
            this.insertRowBefore(this.table.rowIndex + 1, 'after')
        },
        dragColumn($event) {
            if ($event.moved) {
                this.updateHistoryColumnIndex();
                this.clearMerge();
            }
        },
        dragRow($event) {
            if ($event.moved) {
                this.updateHistoryRowIndex();
                this.clearMerge();
            }
        },
        clearMerge() {
            this.tdIds = [];
            this.merge.items = [];
            this.merge.rowColumn = [];
            this.split.visible = false;
            this.merge.visible = false;
        },
        selectItem(rowIndex, header, columnIndex, column, row, multiple = false) {
            this.setTdIndex() //Todo: need to be improve set TdIndex
            this.table.rowIndex = rowIndex;
            this.table.columnIndex = columnIndex;
            this.table.row = row;
            this.table.column = column;
            const cellIndex = jQuery('#td_id_' + column.style.tdId).attr("cellIndex")
            if (this.activeTab === 'cells') {
                if (!multiple) {
                    this.clearMerge();
                    if (column.style.colspan > 1 || column.style.rowspan > 1) {
                        const arrKeys = Object.keys(this.merge.history);
                        const history = arrKeys.map((item) => {
                            return parseInt(item);
                        });
                        this.split.visible = history.includes(parseInt(column.style.tdId));
                    } else {
                        this.split.visible = false;
                    }
                } else {
                    this.split.visible = false;
                }
                if (this.tdIds.includes(column.style.tdId)) {
                    column.columns.forEach((item) => {
                        let i = this.merge.items.indexOf(item);
                        if (i >= 0) {
                            this.merge.items.splice(i, 1);
                        }
                    })
                    let length = 1;
                    const colSpan = column.style.colspan;
                    const rowSpan = column.style.rowspan;
                    if (colSpan > 1 && rowSpan > 1) {
                        length = colSpan * rowSpan;
                    } else if (colSpan > 1) {
                        length = colSpan;
                    } else if (rowSpan > 1) {
                        length = rowSpan;
                    }
                    for (let i = 0; i < length; i++) {
                        const cellInd = this.merge.rowColumn.findIndex(el => el.cellIndex === cellIndex);
                        if (cellInd >= 0) {
                            this.merge.rowColumn.splice(cellInd, 1);
                        }
                    }

                    let index = this.tdIds.indexOf(column.style.tdId);
                    if (index >= 0) {
                        this.tdIds.splice(index, 1);
                    }
                } else {
                    this.tdIds.push(column.style.tdId)
                    column.columns.forEach((item) => {
                        this.merge.items.push(item);
                    })
                    if (this.keyExist(this.merge.history, column.style.tdId)) {
                        this.merge.history[column.style.tdId].forEach((item) => {
                            this.merge.rowColumn.push({
                                rowIndex: item.rowIndex,
                                columnIndex: item.columnIndex,
                                cellIndex: cellIndex,
                                tdId: column.style.tdId
                            })
                        })
                    } else {
                        this.merge.rowColumn.push({
                            rowIndex: rowIndex,
                            columnIndex: columnIndex,
                            cellIndex: cellIndex,
                            tdId: column.style.tdId
                        })
                    }
                }
                const rowArray = this.merge.rowColumn.map(item => {
                    return item.rowIndex;
                });
                const columnArray = this.merge.rowColumn.map(item => {
                    return item.columnIndex;
                });
                this.merge.visible = this.groupByData(rowArray, columnArray);

            } else {
                if (multiple) {
                    if (this.tdIds.includes(column.style.tdId)) {
                        let index = this.tdIds.indexOf(column.style.tdId);
                        if (index >= 0) {
                            this.tdIds.splice(index, 1);
                        }
                    } else {
                        this.tdIds.push(column.style.tdId)
                    }
                } else {
                    this.tdIds = [];
                    this.tdIds.splice(0, 1, column.style.tdId);
                }
            }
            this.manageCell(true, this.activeTab);
        },
        groupByData(rowArray, columnArray) {
            if (rowArray.length > 1) {
                const countRowObj = rowArray.reduce((acc, val) => (acc[val] = acc[val] ? acc[val] + 1 : 1, acc), {});
                const rValues = Object.values(countRowObj);
                const rStatus = rValues.every((val, i, arr) => val === arr[0]);

                const countColumnObj = columnArray.reduce((acc, val) => (acc[val] = acc[val] ? acc[val] + 1 : 1, acc), {});
                const cValues = Object.values(countColumnObj);
                const cStatus = cValues.every((val, i, arr) => val === arr[0]);

                if (rStatus && cStatus) {
                    const sortedRowArr = [...new Set(rowArray)].sort();
                    const sortedColumnArr = [...new Set(columnArray)].sort();
                    const r = this.findElement(sortedRowArr);
                    const c = this.findElement(sortedColumnArr);
                    if (r && c) {
                        if (rowArray.length === 2 && columnArray.length === 2) {
                            const rowIndex = this.merge.rowColumn.map(item => {
                                return Number(item.rowIndex)
                            });
                            const columnIndex = this.merge.rowColumn.map(item => {
                                return Number(item.columnIndex)
                            });
                            if ((rowIndex[0] === rowIndex[1]) || (columnIndex[0] === columnIndex[1])) {
                                return true
                            } else {
                                return false;
                            }
                        }
                        return true
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            return false;
        },
        findElement(arr) {
            for (let i = 1; i < arr.length; i++) {
                if (arr[i] - arr[i - 1] > 1) {
                    return false
                }
            }
            return true;
        },
        mergeCell() {
            const minIndex = Math.min(...this.merge.rowColumn.map(item => item.cellIndex));
            const minItem = this.merge.rowColumn.find(item => item.cellIndex == minIndex);
            const rowSpan = this.merge.rowColumn.map(item => item.rowIndex)
                .filter((value, index, self) => self.indexOf(value) === index);
            const colSpan = this.merge.rowColumn.map(item => item.columnIndex)
                .filter((value, index, self) => self.indexOf(value) === index)
            let temp = [];
            this.merge.rowColumn.forEach((rowColumn) => {
                let columnIndex = rowColumn.columnIndex;
                let rowIndex = rowColumn.rowIndex;
                let columnName = this.headerName(columnIndex);
                let column = this.tableData.data[rowIndex].rows[columnName];
                temp.push({rowIndex: rowIndex, columnIndex: columnIndex})
                if (minItem.tdId === column.style.tdId) {
                    column.columns = this.merge.items;
                    column.style.rowspan = rowSpan.length;
                    column.style.colspan = colSpan.length;
                    this.merge.history[minItem.tdId] = temp;
                } else {
                    column.style.rowspan = 0;
                    column.style.colspan = 0;
                }
            })
            this.clearMerge();
            temp = [];
            this.tableData.table.merge.history = this.merge.history;
        },
        splitCell() {
            const column = this.table.column;
            this.merge.history[column.style.tdId].forEach((item) => {
                const columnName = this.headerName(item.columnIndex);
                const col = this.tableData.data[item.rowIndex].rows[columnName];
                if (col.style.rowspan === 0 && col.style.colspan === 0) {
                    col.columns = [this.defaultItem()]
                }
                col.style.rowspan = 1;
                col.style.colspan = 1;
            })
            delete this.merge.history[column.style.tdId];
            this.clearMerge();
        },
        tdActiveRowSpan(trId) {
            return (this.selectedTrId === trId ? this.selectedRowSpan : 1)
        },
        tdActiveInactiveClass(trId) {
            return (this.selectedTrId === trId ? 'td-active' : 'td-inactive')
        },
        highlightClass(row, header) {
            const highlightColumn = row.rows[header].style.highlighted ? row.rows[header].style.highlighted.active : false;
            return highlightColumn ? 'td-highlight' : '';
        },
        highlightedColumnStyle(row, header) {
            const highlightColumn = row.rows[header].style.highlighted ? row.rows[header].style.highlighted.active : false;
            if (highlightColumn) {
                const height = row.rows[header].style.highlighted.height;
                const shadowColor = row.rows[header].style.highlighted.shadowColor;
                const offset_x = row.rows[header].style.highlighted.offset_x;
                const offset_y = row.rows[header].style.highlighted.offset_y;
                const blur_radius = row.rows[header].style.highlighted.blur_radius;
                return {
                    '--highlight-height-top': `-${height}px`,
                    '--highlight-height-bottom': `${height}px`,
                    '--shadow-color': `${shadowColor}`,
                    '--offset-x': `${offset_x}px`,
                    '--offset-y': `${offset_y}px`,
                    '--blur-radius': `${blur_radius}px`,
                }
            } else {
                return {};
            }
        },
        tdClass(row, header, key) {
            return ((this.table.columnIndex === key) && this.tdIds.length === 1) ? 'single-row-column' : 'td_class_' + this.tdId(row, header)
        },
        thActiveInactiveClass(header) {
            return (this.headerName(this.table.columnIndex) === header) ? 'th-active' : 'th-inactive';
        },
        trId(trId) {
            return this.tdIds.includes(trId) ? 'selected-item' : 'tr_id_' + trId;
        },
        trInlineStyle(row, index) {
            const style = row.style;
            const bgOptions = this.setting.background.options;
            let bgColor = '';
            if (index === 0 && style.backgroundColor === '') {
                bgColor = bgOptions.header_background.value;
            } else if (index % 2 === 0 && index !== 0 && style.backgroundColor === '') {
                bgColor = bgOptions.even_row_background.value;
            } else if (index % 1 === 0 && index !== 0 && style.backgroundColor === '') {
                bgColor = bgOptions.odd_row_background.value;
            } else {
                bgColor = style.backgroundColor;
            }

            return {
                height: style.rowHeight > '50' ? style.rowHeight + 'px' : '',
                background: bgColor,
            }
        },
        tdInlineStyle(row, header) {
            const padding = this.setting.general.options.cell_padding.value;
            return {
                padding: padding + 'px',
                'max-width': this.columnWidth(row, header) + 'px',
                'min-width': this.columnWidth(row, header) + 'px',
                'background-color': row.rows[header] ? row.rows[header].style.backgroundColor : '',
                'display': (row.rows[header].style.colspan > 0 && row.rows[header].style.rowspan > 0) ? '' : 'none'
            }
        },
        tdInlineStyleResponsive(itemStyle, rowStyle, rowIndex, topRowAsHeader, itemsPerRow, currentDevice, cell_direction) {
            const headerLength = Object.keys(this.headerInfo).length;
            let devices = this.responsive.responsive_settings.options.devices;
            let cellPadding = this.setting.general.options.cell_padding.value;

            const totalItems = (topRowAsHeader ? 1 : 0) + itemsPerRow;
            const options = this.setting.background.options;
            let totalWidth = '';

            if (currentDevice === 'mobile') {
                cellPadding = devices.mobile.mobile_cell_padding.value;
                totalWidth = this.responsiveInitial.mobileActivePixel * 0.8;
            } else if (currentDevice === 'tablet') {
                cellPadding = devices.tablet.tablet_cell_padding.value;
                totalWidth = this.responsiveInitial.tabletActivePixel * 0.8;
            } else {
                cellPadding = this.setting.general.options.cell_padding.value;
                totalWidth = 300;
            }

            // const individualTdWidth = this.tableData.table.tr == 1 && topRowAsHeader ? totalWidth + 'px' : (totalWidth / totalItems) + 'px';
            let backgroundColor = '';

            let individualTdWidth = '';
            if (this.tableData.table.tr == 1) {
                individualTdWidth = topRowAsHeader ? totalWidth + 'px' : (totalWidth / itemsPerRow) + 'px';
            } else if (topRowAsHeader && cell_direction == 'column') {
                individualTdWidth = totalWidth / headerLength + 'px';
            } else {
                individualTdWidth = (totalWidth / totalItems) + 'px';
            }


            if (itemStyle.backgroundColor !== '') {
                backgroundColor = itemStyle.backgroundColor;
            } else if (rowIndex === 0) {
                backgroundColor = rowStyle.backgroundColor === '' ? options.header_background.value : rowStyle.backgroundColor;
            } else if (rowIndex % 2 === 0) {
                backgroundColor = rowStyle.backgroundColor === '' ? options.even_row_background.value : rowStyle.backgroundColor;
            } else if (rowIndex % 1 === 0) {
                backgroundColor = rowStyle.backgroundColor === '' ? options.odd_row_background.value : rowStyle.backgroundColor;
            } else {
                backgroundColor = '';
            }

            return {
                'padding': cellPadding + 'px',
                'height': rowStyle.rowHeight !== '' ? rowStyle.rowHeight + 'px' : '',
                'max-width': individualTdWidth,
                'min-width': individualTdWidth,
                'background-color': backgroundColor,
                'box-sizing': 'border-box',
                'overflow': 'hidden',
                'display': (itemStyle.colspan > 0 && itemStyle.rowspan > 0) ? '' : (this.mobileDeviceShowHeader ? '' : 'none')
            }
        },
        headerName(columnIndex) {
            return this.tableData.headers[columnIndex];
        },
        updateHistoryByColumn(columnIndex, tdId, type) {
            let history = this.merge.history[tdId];
            if (history && type) {
                if (type === 'push') {
                    const maxColumnIndex = Math.max(...history.map(item => {
                        return item.columnIndex;
                    }))
                    const uniqueRowIndexes = [...new Set(history.map(item => item.rowIndex))]
                    uniqueRowIndexes.forEach(rowIndex => {
                        let historyObj = {
                            rowIndex: rowIndex,
                            columnIndex: (maxColumnIndex + 1)
                        };
                        history.push(historyObj)
                    })
                } else if (type === 'pop') {
                    history.forEach((item, ind) => {
                        if (item.columnIndex === columnIndex) {
                            for (let i = 0; i < 2; i++) {
                                history.splice(ind, 1);
                            }
                        }
                    })
                }
            }
        },
        updateHistoryByRow(tdId, rowIndex, type) {
            let history = this.merge.history[tdId];
            if (history && type) {
                if (type === 'push') {
                    const maxRowIndex = Math.max(...history.map(item => {
                        return item.rowIndex;
                    }))
                    history.forEach((item, ind) => {
                        if (item.rowIndex === rowIndex) {
                            let historyObj = {
                                rowIndex: (maxRowIndex + 1),
                                columnIndex: item.columnIndex
                            };
                            history.push(historyObj)
                        }
                    })
                } else if (type === 'pop') {
                    history.forEach((item, ind) => {
                        if (item.rowIndex === rowIndex) {
                            for (let i = 0; i < 2; i++) {
                                history.splice(ind, 1);
                            }
                        }
                    })
                }
            }
        },
        mergedTdIndexes() {
            let rowIndexes = []
            const style = this.selectedColumnStyle;
            const tdId = style && style.tdId;
            const history = this.merge.history[tdId];
            if (history) {
                const uniqueHistory = [...new Set(history.map(item => item.rowIndex))];
                for (let i = 0; i < uniqueHistory.length; i++) {
                    rowIndexes.push(i + this.table.rowIndex);
                }
                rowIndexes.shift();
            }
            return rowIndexes;
        },
        tableAlign(currentDevice) {
            let devices = this.responsive.responsive_settings.options.devices;
            let align = this.setting.general.options.table_alignment.value;

            if (currentDevice === 'mobile') {
                align = devices.mobile.mobile_table_alignment.value;
            } else if (currentDevice === 'tablet') {
                align = devices.tablet.tablet_table_alignment.value;
            }
            return this.responsiveAlign(align);
        },
        responsiveAlign(align) {
            let style = {};
            if (align === "center") {
                style = {'margin-left': 'auto', 'margin-right': 'auto'}
            } else if (align === "left") {
                style = {'margin-right': 'auto'};
            } else if (align === "right") {
                style = {'margin-left': 'auto'};
            }
            return style;
        },
    },
    computed: {
        tableMarginTop() {
            return {
                'margin-top': this.setting.global_styling.options.margin_top.value + 'px'
            }
        },
        tableMarginBottom() {
            return {
                'margin-bottom': this.setting.global_styling.options.margin_bottom.value + 'px'
            }
        },
        tableInlineStyle() {
            const general = this.setting.general.options;
            const border = this.setting.border.options;
            const borderCollapse = general.columns_rows_separate;
            const borderCollapsed = (this.getBoolean(borderCollapse.value)) ? 'separate' : 'collapse';

            return {
                'table-layout': 'fixed',
                'border-collapse': borderCollapsed,
                'border': border.table_border.value + 'px solid ' + border.border_color.value,
                'font-family': this.setting.global_styling.options.font_family.value,
                'border-spacing': `${(this.getBoolean(borderCollapse.value)) ?
                    borderCollapse.childs.space_between_column.value + 'px' + ' ' +
                    borderCollapse.childs.space_between_row.value + 'px' : '0px'}`
            }
        },
        tableWrapperStyle() {
            const maxWidth = this.setting.general.options.container_max_width_switch;
            return {
                'max-width': this.getBoolean(maxWidth.value) ? maxWidth.childs.container_max_width.value + 'px' : '',
                'max-height': this.setting.general.options.container_max_height.value + 'px'
            }
        },
        selectedTrId() {
            return (this.table.row && this.table.row.style && this.table.row.style.trId);
        },
        selectedRowSpan() {
            const style = this.selectedColumnStyle;
            return style && style.rowspan ? style.rowspan : 1;
        },
        columnName() {
            let headers = this.tableData.headers;
            let numbers = [];
            headers.forEach(header => {
                numbers.push(header.slice(7, header.length))
            })
            return 'column_' + (Math.max(...numbers) + 1);
        },
        selectedColumnStyle() {
            return this.table.column && this.table.column.style;
        }
    }
}
