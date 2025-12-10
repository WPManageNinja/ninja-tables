<template>
    <div class="ninja-tables-layout w-full">
        <div class="table-customize-button" v-if="selectedDevice === ''">
            <el-button-group v-if="activeTab === 'background'" class="button-group" style="margin-top: 0">
                <el-button
                    type="primary"
                    size="default"
                    @click="manageCell(true, 'cells')"
                >
                    <el-icon><Grid /></el-icon>
                    {{ $t('Manage Cells') }}
                </el-button>

                <el-button
                    type="primary"
                    size="default"
                    @click="manageCell(true, 'background')"
                >
                    <el-icon><IceCreamSquare /></el-icon>
                    {{ $t('Background') }}
                </el-button>
            </el-button-group>
            <div class="button-group" style="margin-top: 0" v-else>
                <el-button
                    type="primary"
                    @click="mergeCell()"
                    :disabled="tdIds.length < 2 || merge.visible === false"
                >
                    <el-icon class="mr-1"><FolderChecked /></el-icon>
                    {{ $t('Merge') }}
                </el-button>

                <el-button
                    type="primary"
                    @click="splitCell"
                    :disabled="split.visible === false"
                >
                    <el-icon class="mr-1"><Scissor /></el-icon>
                    {{ $t('Split') }}
                </el-button>

            </div>
        </div>
        <div class="ntb_table_wrapper"
            v-if="setting ? (setting.general && setting.border && setting.background) : false"
            id="ninja_tables_builder_id" :style="[tableWrapperStyle, tableWrapperActivePadding]">
            <div class="pixel-bar-slider" v-if="responsiveIsEnabled && showPixelSlider.preview">
                <el-slider class="nt_slider_input" v-model="pixelBarValue" :max="showPixelSlider.max" :min="showPixelSlider.min" show-input>
                </el-slider>
            </div>
            <table id="ntb_table" :role="`${setting.accessibility.options.table_role.value}`"
                :class="'table ninja_tables_builder_class_' + tableData['id'] + ' ntb_' + tableData['id']"
                :style="[tableMarginTop, tableMarginBottom, tableInlineStyle, tableAlign(selectedDevice)]">

                <div class="table-header" v-if="manage" :style="tableBorder">
                    <draggable
                        v-model="tableData.headers"
                        tag="tr"
                        @change="dragColumn"
                        item-key="header"
                    >
                        <template #item="{element: header, index: index}" :key="header" >
                            <th scope="col"
                                :colspan="headerColSpan(header)" :class="thActiveInactiveClass(header)"
                                :style="[tdThActiveMargin, thInlineStyle(header)]">
                                <template
                                    v-if="(headerName(table.columnIndex) === header) && tdIds.length === 1 && ((!merge.visible) || split.visible)">
                                    <el-dropdown trigger="click" class="column-options mt-[6px]">
                                        <span class="el-dropdown-link">
                                            <el-icon
                                                :class="index === table.columnIndex ? 'i-active' : ''"
                                                class="el-icon--right"
                                            >
                                                <CaretBottom />
                                            </el-icon>
                                        </span>
                                        <template #dropdown>
                                            <el-dropdown-menu>
                                            <el-dropdown-item @click.native="insertColumnBefore()">{{ $t('Insert column before') }}
                                            </el-dropdown-item>
                                            <el-dropdown-item @click.native="insertColumnAfter">{{ $t('Insert column after')
                                                }}
                                            </el-dropdown-item>
                                            <el-dropdown-item @click.native="addLeftColumn">{{ $t('Insert left column')
                                                }}</el-dropdown-item>
                                            <el-dropdown-item @click.native="addRightColumn">{{ $t('Insert right column')
                                                }}</el-dropdown-item>
                                            <el-dropdown-item divided @click.native="duplicateColumn">{{
                                                    $t('Duplicate column')
                                                }}
                                            </el-dropdown-item>
                                            <el-dropdown-item @click.native="removeColumn">
                                                <p style="color:red"> {{ $t('Remove column') }}</p>
                                            </el-dropdown-item>
                                        </el-dropdown-menu>
                                        </template>
                                    </el-dropdown>
                                </template>
                            </th>
                        </template>
                    </draggable>
                </div>

                <draggable v-model="tableData.data" class="tbody" tag="tbody" @change="dragRow"
                    :style="tdThActiveMargin"
                     item-key="style.trId"
                    v-if="selectedDevice === '' || selectedDevice === 'desktop' || !responsiveIsEnabled || (selectedDevice === 'mobile' && mobileDeviceBreakpoint) || (selectedDevice === 'tablet' && tabletDeviceBreakpoint)">

                    <template #item="{element: row, index: index}" :key="index" >
                        <tr class="desktop-view"
                            :class="`${table.rowIndex === index && tdIds.length <= 0 ? 'single-row-column' : 'tr_class_' + row.style.trId} ${firstRowSticky & index == 0 ? 'firstRowSticky' : ''}`"
                            :id="trId(row.style.trId)"
                            :style="[trInlineStyle(row, index)]">
                            <td v-if="manage && tdIds.length === 1 && !mergedTdIndexes().includes(index)"
                                :rowspan="tdActiveRowSpan(row.style.trId)" :class="tdActiveInactiveClass(row.style.trId)">
                                <el-dropdown trigger="click" placement="top-start" class="row-options">

                                <span class="el-dropdown-link">
                                    <el-icon><CaretRight /></el-icon>
                                </span>
                                <template #dropdown>
                                    <el-dropdown-menu >
                                        <el-dropdown-item @click.native="insertRowBefore()">
                                            {{ $t('Insert row before') }}
                                        </el-dropdown-item>
                                        <el-dropdown-item @click.native="insertRowAfter">
                                            {{ $t('Insert row after') }}
                                        </el-dropdown-item>
                                        <el-dropdown-item @click.native="addTopRow">
                                            {{ $t('Insert top row') }}
                                        </el-dropdown-item>
                                        <el-dropdown-item @click.native="addBottomRow">
                                            {{ $t('Insert bottom row') }}
                                        </el-dropdown-item>
                                        <el-dropdown-item divided @click.native="duplicateRow">
                                            {{ $t('Duplicate row') }}
                                        </el-dropdown-item>
                                        <el-dropdown-item @click.native="removeRow">
                                            <p style="color:red"> {{ $t('Remove row') }}</p>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                                   
                                </el-dropdown>
                            </td>

                            <td v-for="(header, key) in tableData.headers" :key="header"
                                @click.shift="manage ? selectItem(index, header, key, row.rows[header], row, true) : ''"
                                @click.exact="manage ? selectItem(index, header, key, row.rows[header], row, false) : ''"
                                :id="'td_id_' + tdId(row, header)"
                                :class="[tdClass(row, header, key), highlightClass(row, header), 'table-data']"
                                :rowspan="row.rows[header].style.rowspan" :colspan="row.rows[header].style.colspan"
                                :style="[tdInlineStyle(row, header), highlightedColumnStyle(row, header), showInnerBorder ? innerBorder(index) : '', firstColumnSticky && key === 0 ? stickyColumn() : '']">

                                <draggable :list="row.rows[header].columns" group="people"
                                           :id="tdIds.includes(tdId(row, header)) ? 'selected-item' : 'cell_' + tdId(row, header)"
                                           :move="onMove" itemKey="id">
                                    <template #item="{element: item, index: ind}">
                                        <div class="single-item"
                                             :class="[item.id === itemId ? 'item-active' : '', manage ? 'single-item-edit' : (selectedDevice === '' ? item.data.type === 'ribbon' ? 'only-ribbon' : 'other-item' : 'responsive-mode')]"
                                             :key="ind">
                                            <TableData :manage="manage" :setting="setting" :reference="`${index}_${key}_${row.rows[header].columns.length-(ind+1)}`"
                                                        @click.native="!manage && selectedDevice === '' ? styleChange(item, index, key, row.rows[header], row) : ''"
                                                        :item="item">
                                            </TableData>
                                            <template v-if="!manage && selectedDevice === ''">
                                                <div class="icon-style remove-elements text-[20px]" :style="iconSpacing(item)">
                                                    <el-icon><Rank/></el-icon>
                                                    <el-icon @click="copyItem(index, header, item)"><CopyDocument/></el-icon>
                                                    <el-icon @click="deleteItem(index, header, ind)"><Delete/></el-icon>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </draggable>

                            </td>
                        </tr>
                    </template>
                </draggable>

                <tbody v-else :key="tableData">
                <tr v-for="(item, index) in responsiveInitial.tableData" :key="index"
                    :class="`${selectedDevice === 'mobile' ? 'mobile-view tr_class_mobile_' + index : 'tablet-view tr_class_tablet_' + index}`"
                    :id="`${selectedDevice === 'mobile' ? 'tr_id_mobile_' + index : 'tr_id_tablet_' + index}`"
                    :style="[responsiveInitial.showHeader ? bottomBorderResponsive(index, responsiveInitial.cell_direction, responsiveInitial.itemsPerRow, responsive.mode_options.options.devices) : '']"
                >
                    <template v-for="(singleTd, tdIndex) in item" :key="tdIndex">
                        <td v-if="singleTd && singleTd.style && singleTd.style.rowspan > 0 && singleTd.style.colspan > 0"
                            :id="`td_id_${singleTd.style.tdId}`"
                            :colspan="calculateColSpan(singleTd.style)"
                            :rowspan="calculateRowSpan(singleTd.style)"
                            :style="[tdInlineStyleResponsive(singleTd.style, singleTd.rowStyle, singleTd.rowIndex, responsiveInitial.showHeader, responsiveInitial.itemsPerRow, selectedDevice, responsiveInitial.cell_direction), innerBorder(singleTd.rowIndex)]"
                        >
                            <template v-if="singleTd.columns">
                                    <span v-for="(singleItem, idx) in singleTd.columns" :key="idx">
                                        <TableData
                                            class="responsive-mode"
                                            :reference="`${index}_${idx}`"
                                            :setting="setting"
                                            v-if="item.id != itemId"
                                            :item="singleItem"
                                        ></TableData>
                                    </span>
                            </template>
                        </td>
                    </template>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

</template>
<script>
import draggable from "vuedraggable";
import TableData from "./_Datas.vue"
import { manageRowColumn } from "../Mixin/manageRowColumn.js";
import { manageResponsiveData } from "../Mixin/manageResponsiveData.js";
import { helpers } from "../Mixin/helpers.js";
import { useEventBus } from './../../../eventBus.js';
import {
    CaretBottom,
    CaretRight,
    CopyDocument,
    Delete,
    FolderChecked,
    Grid,
    IceCreamSquare,
    Rank,
    Scissor,
    Search
} from "@element-plus/icons-vue";

export default {
    name: "Layout",
    mixins: [manageRowColumn, manageResponsiveData, helpers],
    props: ["setting", "tableData", "responsive", "selectedDevice", "initialData"],
    mounted() {
        this.$nextTick(() => {
            jQuery('body').click((e) => {
                const a = document.querySelector('.tbody');
                jQuery('#leftside').click((l) => {
                    this.isSelectedTdId = true
                })
                jQuery('.el-color-dropdown').click((l) => {
                    this.isSelectedTdId = true
                })
                a.onclick = () => {
                    this.isSelectedTdId = true
                }
                if (this.isSelectedTdId === true) {
                    this.isSelectedTdId = false
                } else {
                    this.selectedTdId !== null ? this.$emit('editItem', '') : '';
                    this.selectedTdId = null;
                    this.itemId = -1;
                }
            });
        })
    },
    data() {
        return {
            bus : useEventBus(),
            countChangeContent: 0,
            initialTableInfo: {
                tempTableData: null,
                tempSetting: null,
                tempResponsive: null,
            },
            isSelectedTdId: true,
            selectedTdId: null,
            itemId: -1,
            manage: false,
            activeTab: 'background',
            table: {
                multipleTd: false,
                rowIndex: null,
                columnIndex: null,
                row: {},
                column: {},
                columns: [],
                rows: [],
            },
            maxWidth: ''
        };
    },
    components: {
        CaretBottom,
        Scissor,
        FolderChecked,
        Delete,
        CopyDocument,
        Rank,
        CaretRight,
        IceCreamSquare,
        Grid,
        draggable,
        TableData,
    },
    methods: {
        handleEdit(data){
            this.$emit('editItem', data)
        },
        iconSpacing(item) {
            if (item.data.type === 'ribbon') {
                return {
                    'margin-top': item.data.style.yAxis - Number(this.setting.general.options.cell_padding.value - 10) + 'px',
                    'margin-left': (item.data.style.ribbonType === 'corner' ? Number(item.data.style.cornerXAxis - this.setting.general.options.cell_padding.value + 10) : item.data.style.xAxis - Number(this.setting.general.options.cell_padding.value - 10)) + 'px',
                }
            }
            return {
                'margin-top': item.data.style.yAxis - Number(this.setting.general.options.cell_padding.value - 10) + Number(item.data.style.margin.top) + 'px',
                'margin-left': `${item.data.style.margin.left}px`,
                'margin-right': `${item.data.style.margin.right}px`,
                'width': 'auto'
            }

        },
        stickyColumn() {
            return {
                position: 'sticky',
                left: 0,
                zIndex: 1,
            }
        },
        onMove($event) {
            if ($event && $event.to.id) {
                this.bus.emit('singleTdId', $event.to.id)
            }
        },
        thInlineStyle(header) {
            return {
                padding: `0px ${this.setting.general.options.cell_padding.value}px`,
                width: `${this.tableHeadWidth(header)}px`,
                display: `${this.displayHeader(header)}`
            }
        },
        displayHeader(header) {
            if (this.merge && this.merge.rowColumn.length > 1 && this.split.visible) {
                const headers = Object.values(this.merge.rowColumn);
                const arr = headers.map(item => {
                    return this.headerName(item.columnIndex);
                });
                if ((header !== this.headerName(this.table.columnIndex)) && arr.includes(header)) {
                    return 'none';
                } else {
                    return '';
                }
            } else {
                return '';
            }
        },
        headerColSpan(header) {
            const style = this.selectedColumnStyle;
            return (style && style.colspan && this.headerName(this.table.columnIndex) === header) ? style && style.colspan : 1
        },
        columnWidth(row, header) {
            return (row.rows && row.rows[header] && row.rows[header].style.columnWidth) ? row.rows[header].style.columnWidth : this.setting.general.options.cell_min_auto_width.value;
        },
        tdId(row, header) {
            return row.rows[header] ? row.rows[header].style.tdId : null
        },
        dragRowColumn(item, rowIndex, header, columnIndex, column, row) {
            this.table.columnIndex = columnIndex;
            this.table.rowIndex = rowIndex;
            this.manageCell(true, this.activeTab)
        },
        setTdIndex() {
            const table = document.getElementById('ntb_table');
            const trs = table.getElementsByTagName('tbody');
            const rows = trs[0].rows;

            const tableSize = {
                rows: rows.length,
                cols: rows[0].getElementsByTagName('td').length - 1
            };

            for (var r = 0; r < trs.length; r++) {
                var tds = trs[r].getElementsByTagName('td');
                for (var t = 0; t < tds.length; t++) {
                    jQuery(tds[t]).attr("cellIndex", ((t + r * tableSize.cols) + 1));
                }
            }
        },
        manageCell(status, activeTab = 'background') {
            this.isSelectedTdId = true
            this.selectedTdId = null;

            if (activeTab === 'cells') {
                let history = this.tableData.table.merge.history;
                if (!this.isEmpty(history)) {
                    this.merge.history = history;
                }
            }
            this.manage = status;
            this.activeTab = activeTab;
            const items = {
                data: {
                    tdIds: this.tdIds,
                    setting: this.setting,
                    tableData: this.tableData.data,
                    table: this.table,
                    headers: this.tableData.headers
                },
                active: status,
                activeTab: activeTab
            }
            this.bus.emit('manage-cell', items);
        },
        styleChange(item, rowIndex, columnIndex, column, row) {
            this.selectedTdId = item.id;
            if (!this.manage) {
                if (item.id == this.itemId) {
                    this.itemId = -1;
                } else {
                    const data = {
                        edit: true,
                        item: item
                    }
                    this.$emit('editItem', data)
                    this.bus.emit('singleTdId', 'td_id_' + column.style.tdId);
                    this.itemId = item.id;
                }
            } else {
                this.dragRowColumn(item, rowIndex, columnIndex, column, row)
            }
        },
        copyItem(rowIndex, columnIndex, item) {
            const items = {
                id: this.id(),
                data: this.deepClone(item.data)
            }
            this.tableData.data[rowIndex].rows[columnIndex].columns.push(items)
        },
        deleteItem(rowIndex, columnIndex, id) {
            this.$emit('editItem', '');
            this.tableData.data[rowIndex].rows[columnIndex].columns.splice(id, 1)
        },
        innerBorder(index) {
            const innerBorder = this.setting.border.options.inner_border;
            const childs = innerBorder.childs;
            let style = ``;
            if (index !== 0 && innerBorder.value) {
                style = `${childs.inner_border_size.value}px solid ${childs.inner_border_color.value}`;
            }
            if (index === 0 && innerBorder.value && childs.header_inner_border.value) {
                style = `${childs.inner_border_size.value}px solid ${childs.inner_border_color.value}`;
            }
            return {
                border: style,
            }
        },
        tableHeadWidth(header) {
            const columnIndex = this.table.columnIndex;
            const row = this.table.row;
            let width = row && this.columnWidth(row, header);
            const colSpan = this.headerColSpan(header);
            let mergedCellWidth = (colSpan * 11) + (colSpan > 2 ? ((colSpan - 2) * 11) : 0);
            const headers = this.deepClone(this.tableData.headers);
            const mergedHeaders = headers.splice(columnIndex, colSpan);
            if (colSpan > 1) {
                mergedHeaders.forEach(columnName => {
                    mergedCellWidth += row && this.columnWidth(row, columnName)
                })
                return mergedCellWidth;
            } else {
                width = row && this.columnWidth(row, header)
                if (width === 150) {
                    width = this.setting.general.options.cell_min_auto_width.value;
                }
            }
            return (width + 1)
        },
        detectChangesInTable() {
            if (this.countChangeContent > 3 && this.initialTableInfo.tempTableData === JSON.stringify(this.tableData.data) && this.initialTableInfo.tempSetting === JSON.stringify(this.setting) && this.initialTableInfo.tempResponsive === JSON.stringify(this.responsive)) {
                this.countChangeContent = 3;
                this.bus.emit('saveData');
                return;
            }
            if (this.countChangeContent > 4) {
                return;
            }
            this.countChangeContent++;
            this.countChangeContent === 4 ? this.bus.emit('somethingChanged') : '';
        },
    },
    created() {
        this.bus.on('manageCell', () => {
            this.table.columnIndex = null
            this.table.rowIndex = null
            this.selectedTdId = null
            this.manage = false
            this.activeTab = 'background'
            this.clearMerge();
        })
    },
    computed: {
        Search() {
            return Search
        },
        firstRowSticky() {
            const isSticky = this.setting.sticky.options.first_row_sticky.value;
            return this.getBoolean(isSticky);
        },
        firstColumnSticky() {
            const isSticky = this.setting.sticky.options.first_column_sticky.value;
            return this.getBoolean(isSticky);
        },
        tdThActiveMargin() {
            if (this.manage && this.setting.border.options.table_border.value > 0 && this.tdIds.length === 1) {
                return {
                    '--td-th-active-margin': `-16px`,
                }
            }
        },
        tableWrapperActivePadding() {
            if (this.manage && this.setting.border.options.table_border.value > 0 && this.tdIds.length === 1) {
                return {
                    '--table-wrapper-padding': `16px`
                }
            }
        },
        showInnerBorder() {
            return this.setting.border.options.inner_border.value;
        },
        tableBorder() {
            const tableOption = this.setting.border.options;
            const columnIndex = this.table.columnIndex;
            const tableBorderSize = tableOption.table_border ? tableOption.table_border.value : 0;
            const tableInnerBorderSize = tableOption.inner_border.childs ? tableOption.inner_border.childs.inner_border_size.value : 0;
            const isSeparateRowColumn = this.getBoolean(this.setting.general.options.columns_rows_separate.value);
            const colSeparate = this.setting.general.options ? this.setting.general.options.columns_rows_separate.childs.space_between_column.value : 0;
            const rowSeparate = this.setting.general.options ? this.setting.general.options.columns_rows_separate.childs.space_between_row.value : 0;

            let calculateSeparate = 0;
            let columnArrow = 0;
            if (isSeparateRowColumn) {
                calculateSeparate = (colSeparate * 2) + (tableOption.inner_border.value ? (columnIndex * tableInnerBorderSize) : 0);
            }
            columnArrow = 16 + tableBorderSize + calculateSeparate - columnIndex + (tableOption.inner_border.value ? ((columnIndex + 1) * tableInnerBorderSize) : 0)
            const _marginTop = `${-5 - (isSeparateRowColumn ? rowSeparate : 0)}px`;

            return {
                '--border-color': tableOption.inner_border.childs.inner_border_color.value ? tableOption.inner_border.childs.inner_border_color.value : '#000000',
                '--border-size': `${tableInnerBorderSize}px`,
                '--column-arrow': `${columnArrow}px`,
                '--margin-top': _marginTop,
                '--margin-bottom': `-${isSeparateRowColumn ? (tableBorderSize === 0 ? (rowSeparate * 3) : rowSeparate) : 0}px`,
            }
        },
    },
    watch: {
        tableData: {
            handler(newVal, oldVal) {
                if (newVal) {
                    if (this.countChangeContent === 0) {
                        this.initialTableInfo.tempTableData = JSON.stringify(this.tableData.data);
                        this.initialTableInfo.tempSetting = JSON.stringify(this.setting);
                        this.initialTableInfo.tempResponsive = JSON.stringify(this.responsive);
                    }
                    this.detectChangesInTable();
                }
            },
            deep: true
        },
        setting: {
            handler(newVal, oldVal) {
                if (newVal) {
                    this.detectChangesInTable();
                }
            },
            deep: true
        },
        selectedDevice(newVal, oldVal) {
            this.responsiveInitial.showHeader = newVal === 'mobile' ? this.mobileDeviceShowHeader : (newVal === 'tablet' ? this.tabletDeviceShowHeader : false);
            this.responsiveInitial.itemsPerRow = newVal === 'mobile' ? this.mobileDeviceItemsPerRow : (newVal === 'tablet' ? this.tabletDeviceItemsPerRow : 1);
            this.responsiveInitial.cell_direction = newVal === 'mobile' ? this.mobileDeviceCellDirection : (newVal === 'tablet' ? this.tabletDeviceCellDirection : 'row');
            this.responsiveInitial.tableData = this.getResponsiveTableData(this.responsiveInitial.cell_direction, this.responsiveInitial.showHeader, this.responsiveInitial.itemsPerRow, true);
        },
        responsive: {
            handler(newVal, oldVal) {
                if (newVal) {
                    this.detectChangesInTable();
                }

                if (this.selectedDevice === 'mobile') {
                    this.responsiveInitial.showHeader = this.getBoolean(newVal.mode_options.options.devices.mobile.top_row_as_header.value);
                    this.responsiveInitial.itemsPerRow = newVal.mode_options.options.devices.mobile.items_per_row.value;
                    this.responsiveInitial.cell_direction = newVal.mode_options.options.devices.mobile.cell_direction.value;
                } else if (this.selectedDevice === 'tablet') {
                    this.responsiveInitial.showHeader = this.getBoolean(newVal.mode_options.options.devices.tablet.top_row_as_header.value);
                    this.responsiveInitial.itemsPerRow = newVal.mode_options.options.devices.tablet.items_per_row.value;
                    this.responsiveInitial.cell_direction = newVal.mode_options.options.devices.tablet.cell_direction.value;
                } else {
                }
                this.responsiveInitial.tableData = this.getResponsiveTableData(this.responsiveInitial.cell_direction, this.responsiveInitial.showHeader, this.responsiveInitial.itemsPerRow, false);
            },
            deep: true
        }
    }
}
</script>
<style lang="scss">
.ninja-tables-layout {
    margin-top: 20px;
    .table-customize-button {
        padding-bottom: 8px;
    }

    .sortable-ghost {
        border: 1px dashed grey;
        font-size: 0;
        overflow: hidden;
        width: 100%;
    }

    .button-group {
        display: flex !important;
        justify-content: center;
        margin: 5px auto;
        button{
            background: #335cff;
            border-color: #335cff;
            &:hover{
                background: #335cff;
                border-color: #335cff;
            }
        }
    }

    .manage-button {
        display: block;
        margin: 0px auto 10px auto;
        padding: 10px 20px;
    }

    .ntb_table_wrapper {
        overflow-wrap: break-word;
        position: relative;
        z-index: 1;
        //margin: auto;
        overflow: auto;
        // padding-top: 5px;
        padding-left: var(--table-wrapper-padding);

        .pixel-bar-slider {
            width: 65%;
            margin:0 auto 10px;
            // background: #3b503f;
            background: white;
            border: 1px solid #dbdbdb;
            padding: 0 0 0 20px;
            border-radius: 10px;
            .el-input__inner{
                padding: 0;
            }
        }

        .table {
            .table-header {
                top: 0;
                position: sticky;
                position: -webkit-sticky;
                z-index: 99;
                margin-left: var(--column-arrow);
                margin-bottom: var(--margin-bottom);
                display: table-caption;

                th {
                    //background-color: #ffffff;
                    //border: var(--border-size) solid var(--border-color);

                    .column-options {
                        span {
                            i {
                                border: 1px solid;
                                font-size: 12px;
                                opacity: 1;
                                color: #ffffff;
                            }
                        }
                    }

                    &.th-active {
                        display: block;
                        background-color: #335cff;
                        margin-left: var(--td-th-active-margin);
                        margin-top: var(--margin-top);

                        &:hover {
                            cursor: move;
                            background-color: #5f6368;

                            .column-options {
                                span {
                                    i {
                                        cursor: pointer;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            .tbody {
                .td-highlight {
                    background: inherit;
                    position: relative;
                    z-index: 9;
                    border: none !important;

                    &:before {
                        content: "";
                        position: absolute;
                        background: inherit;
                        width: calc(100% + 30px);
                        left: -15px;
                        top: 0;
                        height: 100%;
                        z-index: -1;
                        box-shadow: var(--offset-x) var(--offset-y) var(--blur-radius) var(--shadow-color);
                    }
                }

                tr {
                    &:first-child {
                        .td-highlight {
                            &:before {
                                top: var(--highlight-height-top);
                            }
                        }
                    }

                    &:first-child,
                    &:last-child {
                        .td-highlight {
                            &:before {
                                height: calc(100% + var(--highlight-height-bottom));
                            }
                        }
                    }
                }

                display: block;
                margin-left: var(--td-th-active-margin);

                tr.firstRowSticky {
                    position: sticky;
                    top: -5px;
                    z-index: 2;
                }

                tr {
                    position: relative;

                    td {
                        .sortIcon {
                            font-size: 30px;
                        }

                        position: relative;

                        .row-options {
                            top: calc(50% - 10px);

                            span {
                                i {
                                    color: #000000;
                                    border: 1px solid;
                                    font-size: 12px;
                                    opacity: 1
                                }

                                span {
                                    color: #000000;
                                }
                            }

                            &:hover {
                                i {
                                    opacity: 1;
                                    color: #ffffff;
                                    cursor: pointer;
                                }

                                span {
                                    color: #ffffff;
                                    cursor: move;
                                }
                            }
                        }

                        .row-move {
                            position: absolute;
                            height: 100%;
                            right: -37px;
                            top: 0;

                            .el-button {
                                width: 30px;

                                i {
                                    transform: rotate(270deg)
                                }

                                .top {
                                    position: absolute;
                                    margin-top: 15px;
                                }

                                .bottom {
                                    margin-bottom: 15px;
                                }
                            }
                        }
                    }

                    .td-active {
                        background-color: #335cff;
                        position: sticky;
                        position: -webkit-sticky;
                        z-index: 99;
                        cursor: move;
                        max-width: 20px;
                        border-bottom: var(--border-size) solid var(--border-color);
                        opacity: 1;

                        .row-options {
                            span {
                                i {
                                    color: #ffffff;
                                    opacity: 1;
                                }

                                span {
                                    color: #ffffff;
                                }
                            }
                        }

                        &:hover {
                            background-color: #5f6368;
                        }
                    }

                    .td-inactive {
                        opacity: 0;
                        visibility: hidden;
                        right: 10px;
                    }
                }
            }

            .single-item {
                position: relative;
                display: block;
                border: 0px solid transparent;

                .icon-style {
                    position: absolute;
                    top: -15px;
                    height: auto;
                    font-size: 14px;
                    // width: 100%;
                    // left: 0;
                    right: 0;
                    display: none;
                    color: #ffffff;
                    .el-icon{
                        font-size: 18px;
                    }
                    .el-icon-rank {
                        cursor: move;
                    }

                    i {
                        background: #335cff;
                        padding: 0px 2px;
                        font-weight: bold;
                    }
                }
            }

            .other-item {
                .hover-item {
                    border: 1px solid transparent;
                }

                &.item-active {
                    .hover-item {
                        border-color: #335cff;
                    }
                }

                &:hover {
                    cursor: pointer;

                    .hover-item {
                        border-color: #335cff;
                    }

                    .icon-style {
                        // opacity: 1;
                        display: unset;
                    }
                }
            }

            .only-ribbon {
                position: absolute;

                &:hover {
                    cursor: pointer;

                    .corner,
                    .bookmark,
                    .side {
                        border: 1px solid #335cff;
                    }

                    .icon-style {
                        // opacity: 1;
                        display: unset;
                        justify-content: flex-start;
                        z-index: 3;
                        top: 0;
                    }
                }
            }

            @keyframes selected-item-move {
                0% {
                    background-position: 0% 50%;
                }

                100% {
                    background-position: 100% 50%;
                }
            }

            #selected-item {
                background: repeating-linear-gradient(45deg, white, white 5px, #3299d1 5px, #3299d1 10px);
                background-size: 400% 400%;
                animation: selected-item-move 40s linear infinite reverse;
                opacity: .2;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
        }
    }
}

.responsive-mode {
    &:hover {
        cursor: pointer;
    }
}


</style>
