<template>
    <div>
        <h3 class="font-semibold">{{ $t('General Color Options') }}</h3>
        <div class="component-wrapper">
            <color-input :label="$t('Header Background')" v-model="headerBackground"></color-input>
            <color-input :label="$t('Even Row Background')" v-model="manageCell.data.setting.background.options.even_row_background.value"></color-input>
            <color-input :label="$t('Odd Row Background')" v-model="manageCell.data.setting.background.options.odd_row_background.value"></color-input>
        </div>

        <div v-if="manageCell.data.tdIds.length === 1">
            <br>
            <h3 class="font-semibold">{{ $t('Selected Color Options') }}</h3>
            <div class="component-wrapper">
                <div class="block space-between">
                    <span>{{ $t('Selected Cell Row Background') }}</span>
                    <color-input v-model="trBackground" @active-change="changeTrBackground" @change="changeTrBackground"
                                 :predefine="predefineColors"></color-input>
                </div>
                <div class="block space-between">
                    <span>{{ $t('Selected Cell Column Background') }}</span>
                    <color-input v-model="columnBackground" @active-change="changeTdBackground"
                                 @change="changeTdBackground" :predefine="predefineColors"></color-input>
                </div>
            </div>
        </div>

        <div class="block component-spacing space-between" v-if="manageCell.data.tdIds.length >= 1">
            <span>{{ $t('Selected Cell Background') }}</span>
            <color-input v-model="cellBackground" @active-change="changeSelectedCellBackground"
                         @change="changeSelectedCellBackground" :predefine="predefineColors"></color-input>
        </div>

      <h5 v-if="manageCell.data.tdIds.length <= 0">
        <b>{{
            $t('Select a row/column/cell to change their background properties. Hold "SHIFT" and click on cells to select multiple cells.')
          }}</b>
      </h5>
    </div>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";

export default {
    name: "BackgroundColor",
    components: {ColorInput},
    props: ['manageCell'],
    data: () => ({
        predefineColors: ["#ff4500", "#ff8c00", "#ffd700", "#90ee90", "#00ced1", "#1e90ff", "#c71585", "#000000", "#dddddd", "#379D13"]
    }),
    mounted() {
        this.initializePrioritySystem();
    },
    methods: {
        initializePrioritySystem() {
            const {tableData, headers} = this.manageCell.data;
            if (!tableData) return;

            const patterns = this.detectPatterns(tableData, headers);
            this.assignPriorities(tableData, patterns);
        },

        detectPatterns(tableData, headers) {
            const rowBackgrounds = new Map();
            const columnBackgrounds = new Map();

            // Detect row patterns
            tableData.forEach((row, rowIndex) => {
                const cells = Object.values(row.rows);
                const firstColor = cells[0]?.style?.backgroundColor;
                if (firstColor && cells.every(cell => cell?.style?.backgroundColor === firstColor)) {
                    rowBackgrounds.set(rowIndex, firstColor);
                }
            });

            // Detect column patterns
            if (headers) {
                Object.entries(headers).forEach(([, columnIndex]) => {
                    const columnCells = tableData.map(row => row.rows[columnIndex]).filter(Boolean);
                    const firstColor = columnCells[0]?.style?.backgroundColor;
                    if (firstColor && columnCells.every(cell => cell?.style?.backgroundColor === firstColor)) {
                        columnBackgrounds.set(columnIndex, firstColor);
                    }
                });
            }

            return {rowBackgrounds, columnBackgrounds};
        },

        assignPriorities(tableData, {rowBackgrounds, columnBackgrounds}) {
            tableData.forEach((row, rowIndex) => {
                Object.entries(row.rows).forEach(([columnIndex, cell]) => {
                    if (!cell.style.backgroundPriority && cell.style.backgroundColor) {
                        const cellColor = cell.style.backgroundColor;
                        cell.style.backgroundPriority = {};

                        if (rowBackgrounds.get(rowIndex) === cellColor) {
                            cell.style.backgroundPriority.row = cellColor;
                            cell.style.backgroundPriority.rowTimestamp = Date.now() - 1000;
                        } else if (columnBackgrounds.get(columnIndex) === cellColor) {
                            cell.style.backgroundPriority.column = cellColor;
                            cell.style.backgroundPriority.columnTimestamp = Date.now() - 500;
                        } else {
                            cell.style.backgroundPriority.default = cellColor;
                        }
                    }
                });
            });
        },

        applyBackgroundColor(cell, color, type) {
            // Always ensure priority system exists
            if (!cell.style.backgroundPriority) {
                cell.style.backgroundPriority = {};
            }

            // Set or remove color for type
            if (!color) {
                delete cell.style.backgroundPriority[type];
                if (type === 'row') delete cell.style.backgroundPriority.rowTimestamp;
                if (type === 'column') delete cell.style.backgroundPriority.columnTimestamp;
            } else {
                cell.style.backgroundPriority[type] = color;
                // Set timestamp when applying row/column colors
                if (type === 'row') {
                    cell.style.backgroundPriority.rowTimestamp = cell.style.backgroundPriority.rowTimestamp || Date.now();
                } else if (type === 'column') {
                    cell.style.backgroundPriority.columnTimestamp = cell.style.backgroundPriority.columnTimestamp || Date.now();
                }
            }

            // Apply priority: selected > row/column (latest) > default
            const priority = cell.style.backgroundPriority;
            let finalColor = priority.selected || this.getLatestRowColumn(priority) || priority.default || '';
            cell.style.backgroundColor = finalColor;
        },

        getLatestRowColumn(priority) {
            if (!priority.row && !priority.column) return '';
            if (!priority.row) return priority.column;
            if (!priority.column) return priority.row;
            return priority.rowTimestamp > priority.columnTimestamp ? priority.row : priority.column;
        },

        updateCells(selector, color, type) {
            const cl = color || '';
            const timestamp = Date.now();

            selector().forEach(cell => {
                // Always ensure priority system exists
                if (!cell.style.backgroundPriority) {
                    cell.style.backgroundPriority = {};
                    // Preserve existing background as default if it exists
                    if (cell.style.backgroundColor) {
                        cell.style.backgroundPriority.default = cell.style.backgroundColor;
                    }
                }

                // Set timestamp for row/column types
                if (type === 'row') {
                    cell.style.backgroundPriority.rowTimestamp = timestamp;
                } else if (type === 'column') {
                    cell.style.backgroundPriority.columnTimestamp = timestamp;
                }

                this.applyBackgroundColor(cell, cl, type);
            });
        },

        changeTdBackground(color) {
            const columnIndex = this.manageCell.data.headers[this.manageCell.data.table.columnIndex];
            this.updateCells(() =>
                    this.manageCell.data.tableData.map(row => row.rows[columnIndex]).filter(Boolean),
                color, 'column'
            );
        },

        changeSelectedCellBackground(color) {
            this.updateCells(() => {
                const cells = [];
                this.manageCell.data.tableData.forEach(row => {
                    Object.values(row.rows).forEach(cell => {
                        if (this.manageCell.data.tdIds.includes(cell.style.tdId)) {
                            cells.push(cell);
                        }
                    });
                });
                return cells;
            }, color, 'selected');
        },

        changeTrBackground(color) {
            const rowIndex = this.manageCell.data.table.rowIndex;
            if (rowIndex >= 0 && rowIndex < this.manageCell.data.tableData.length) {
                this.updateCells(() =>
                        Object.values(this.manageCell.data.tableData[rowIndex].rows),
                    color, 'row'
                );
            }
        },

        getBackgroundByType(type, identifier) {
            const {tableData, headers, table, tdIds} = this.manageCell.data;

            if (type === 'selected' && tdIds.length > 0) {
                for (let row of tableData) {
                    for (let cell of Object.values(row.rows)) {
                        if (tdIds.includes(cell.style.tdId)) {
                            return cell.style.backgroundPriority?.selected || '';
                        }
                    }
                }
            }

            if (type === 'column') {
                const columnIndex = headers[table.columnIndex];
                const cell = tableData[0]?.rows[columnIndex];
                return this.getColorIfUniform(cell, () =>
                    tableData.map(row => row.rows[columnIndex]).filter(Boolean), 'column'
                );
            }

            if (type === 'row') {
                const rowIndex = table.rowIndex;
                if (rowIndex >= 0 && rowIndex < tableData.length) {
                    const firstCell = Object.values(tableData[rowIndex].rows)[0];
                    return this.getColorIfUniform(firstCell, () =>
                        Object.values(tableData[rowIndex].rows), 'row'
                    );
                }
            }

            return '';
        },

        getColorIfUniform(cell, getCells, type) {
            if (cell?.style?.backgroundPriority?.[type]) {
                return cell.style.backgroundPriority[type];
            }

            if (cell?.style?.backgroundColor) {
                const color = cell.style.backgroundColor;
                const allSame = getCells().every(c => c?.style?.backgroundColor === color);
                return allSame ? color : '';
            }

            return '';
        }
    },
    computed: {
        headerBackground: {
            get() {
                return this.manageCell.data.setting.background.options.header_background.value;
            },
            set(newValue) {
                const bg = this.manageCell.data.setting.background;
                bg.options.header_background.value = newValue || '#fff';
            }
        },
        cellBackground: {
            get() {
                return this.getBackgroundByType('selected');
            },
            set(newValue) {
                this.changeSelectedCellBackground(newValue);
            }
        },
        columnBackground: {
            get() {
                return this.getBackgroundByType('column');
            },
            set(newValue) {
                this.changeTdBackground(newValue);
            }
        },
        trBackground: {
            get() {
                return this.getBackgroundByType('row');
            },
            set(newValue) {
                this.changeTrBackground(newValue);
            }
        }
    }
}
</script>

<style scoped>
.color-picker-margin {
  margin-right: 10px;
}
</style>
