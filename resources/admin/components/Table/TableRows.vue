<template>
    <div class="table-rows">
        <template v-if="!isEditable">
            <div v-if="dataSourceType == 'fluent-form'" class="tablenav top">
                <fluent-form-nav
                    :config="config"
                    :model="new_column"
                    :hasPro="has_pro"
                    :is-editable-message="isEditableMessage"
                    :tableCreated="reloadSettingsAndData"
                />
            </div>
            <div v-else-if="dataSourceType.indexOf('csv') != -1" class="tablenav top">
                <external-source-nav :is-editable-message="isEditableMessage"
                                     :loading="syncing"
                                     :config="config"
                                     :hasPro="has_pro"
                                     v-model="externalDataSourceUrl"
                                     :tableCreated="reloadSettingsAndData"
                />
            </div>

            <div v-else-if="dataSourceType == 'wp-posts' && new_column" class="tablenav top">
                <WPPostsNav
                    :config="config"
                    :model="new_column"
                    :hasPro="has_pro"
                    :is-editable-message="isEditableMessage"
                    :tableCreated="reloadSettingsAndData"
                    @add="addNewColumn()"
                />
            </div>

            <div v-else-if="dataSourceType == 'raw_sql'" class="tablenav top">
                <raw-sql-nav :is-editable-message="isEditableMessage"
                             :loading="syncing"
                             :config="config"
                             :column_count="columns.length"
                             :hasPro="has_pro"
                             :tableCreated="reloadSettingsAndData"
                />
            </div>

            <div v-else-if="dataSourceType == 'wp_woo'" class="tablenav top">
                <div class="woo_data_source_config">
                    <woo-nav-edit
                        :model="new_column"
                        :config="config"
                        @add="addNewColumn()"
                        @reloadData="getData()"
                    />
                </div>
            </div>

            <div v-if="!columns.length && !loading">
                <h3 style="text-align: center">No Data found based on your data source configaration</h3>
            </div>
        </template>

        <template v-if="columns.length && isEditable">
            <add_data_modal v-if="addDataModal"
                            :title="addDataModalTitle"
                            :show="addDataModal"
                            @modal_close="closeDataModal"
                            @updateItem="updateItemOnTable"
                            @createItem="addItemOnTable"
                            :table_id="tableId"
                            :columns="columns"
                            :item="updateItem"
                            :manual-sort="config.settings.sorting_type === 'manual_sort'"
                            :insert-after-position="insertAfterPosition"
                            :insertAfterId="insertAfterId"
                            :type="dataModalType"
            ></add_data_modal>
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text">
                        {{ $t('Select bulk action') }}
                    </label>
                    <select name="action" v-model="bulkAction">
                        <option value="-1">{{ $t('Bulk Actions') }}</option>
                        <option value="delete">{{ $t('Delete Entries')}}</option>
                    </select>
                    <button class="button action" v-on:click.prevent="handleBulkAction">{{ $t('Apply') }}</button>
                    <label for="compact_view" class="form_group">
                        <input id="compact_view" type="checkbox" v-model="isCompact"/> Compact View
                    </label>
                    <label class="show-meta-data">
                        | <i title="show meta data" @click="show_meta = !show_meta" class="el-icon-news"></i>
                    </label>
                    <label class="form_group search_action" for="search">
                        <input v-on:keyup.enter="getData" id="search" class="form-control inline" v-model="searchString"
                               placeholder="Search" type="text"/>
                        <i @click="getData" class="el-icon-search"></i>
                    </label>

                    <label class="form_group">
                        <input type="checkbox" name="checkbox" v-model="sorting">
                        Sort Manually
                        <template v-if="!has_pro">(Pro Feature)</template>
                    </label>
                </div>
                <div class="pull-right">
                    <el-button size="small" @click="addColumn()"> {{ $t('Add Column') }}</el-button>
                    <el-button size="small" type="primary" @click="add()"> {{ $t('Add Data') }}</el-button>
                </div>
            </div>
        </template>

        <div v-if="!loading && !columns.length && isEditable" type="warning"
             style="margin-top: 15px; text-align: center" class="instruction_block">
            <h3>{{ $t('To get started please add table columns') }}</h3>
            <el-button @click="addColumn()" type="primary" size="small">
                Add Column
            </el-button>
        </div>

        <template v-if="columns.length">
            <el-table
                @sort-change="onSortChange"
                class="js-sortable-table"
                v-loading="loading"
                :data="items"
                row-key="id"
                border
                max-height="600"
                :class="{ compact: isCompact, sorting: sorting}"
                :style="'width: '+tableWidth"
                @selection-change="handleSelectionChange"
            >
                <el-table-column
                    v-if="isEditable"
                    type="selection"
                    width="60">
                </el-table-column>
                <el-table-column
                    :sort-orders="['ascending', 'descending']"
                    :prop="column.key"
                    :sort-method="compare"
                    sortable
                    v-for="(column, index) in columns"
                    :width="(columnLength == index + 1 ) ? '' : 150"
                    :key="index">
                    <template #default="scope">
                        <show-editable-cell
                        :row="scope.row"
                        :column="column"
                        :columns="columns"
                        :formula_support="config.settings.formula_support"
                        :is_editable="isEditable"
                        />
                    </template>

                    <template #header>
                        <span>
                            {{ column.name || column.key }}<i class="el-icon-setting nt-column-config" @click="showColumnConfigModal(column)"/>
                        </span>
                    </template>
                </el-table-column>
                <template v-if="isEditable">
                    <template v-if="show_meta">
                        <el-table-column
                            label="Row ID"
                            width="100px"
                            prop="id"
                        />
                        <el-table-column
                            label="Created By"
                            width="165px"
                            prop="created_by"
                        />
                        <el-table-column
                            label="Reference Date"
                            width="165px"
                            prop="created_at"
                        />
                    </template>
                    <el-table-column
                        fixed="right"
                        label="Actions"
                        class-name="actions"
                        width="120">
                        <template #default="scope">
                            <a v-if="has_pro" @click="addAfter(scope)">
                                <el-tooltip placement="top-end" effect="light" content="Add Data after this row"
                                            :open-delay="500">
                                    <span class="dashicons dashicons-plus"></span>
                                </el-tooltip>
                            </a>

                            <a @click="showUpdateModal(scope)">
                                <el-tooltip placement="top-end" effect="light" content="Edit data" :open-delay="500">
                                    <span class="dashicons dashicons-edit"></span>
                                </el-tooltip>
                            </a>

                            <a @click="duplicateData(scope)">
                                <el-tooltip placement="top-end" effect="light" content="Duplicate data"
                                            :open-delay="500">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </el-tooltip>
                            </a>
                            <delete-pop-over @deleted="deleteItem(scope.row.id)"></delete-pop-over>
                        </template>
                    </el-table-column>
                </template>
            </el-table>
            <div class="tablenav bottom">
                <div v-if="isEditable" class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text">
                        {{ $t('Select bulk action') }}
                    </label>
                    <select name="action" v-model="bulkAction">
                        <option value="-1">{{ $t('Bulk Actions') }}</option>
                        <option value="delete">{{ $t('Delete Entries')}}</option>
                    </select>
                    <button class="button action" v-on:click.prevent="handleBulkAction">{{ $t('Apply') }}</button>
                </div>
                <div class="pull-right">
                    <el-pagination
                        @size-change="handleSizeChange"
                        @current-change="goToPage"
                        :current-page.sync="paginate.current_page"
                        :page-sizes="[10, 20, 50, 100, 500, 2000]"
                        :page-size="paginate.per_page"
                        layout="total, sizes, prev, pager, next, jumper"
                        :total="paginate.total">
                    </el-pagination>
                </div>
            </div>
        </template>

        <sortable-upgrade-notice :show="sortableUpgradeNotice" @close="sortableUpgradeNotice = false"/>

        <el-dialog
            :close-on-click-modal="false"
            class="no_padding_body"
            :append-to-body="true"
            top="50px"
            :title="'Edit Table Column : ' +currentEditingColumn.name"
            width="70%"
            :visible.sync="showColumnEditor"
        >
            <columns-editor
                :dataSourceType="config.table.dataSourceType"
                :model="currentEditingColumn"
                :hasPro="has_pro"
                :updating="true"
                :columns="columns"
                :settings="config.settings"
                v-if="showColumnEditor && currentEditingColumn"
                @store="storeSettings()"
                @delete="deleteColumn()"
                @cancel="showColumnEditor = false"
            />
        </el-dialog>

        <el-dialog
            v-model="columnModal"
            :close-on-click-modal="false"
            top="50px"
            :append-to-body="true"
            title="Add Table Column"
            width="70%">
            <columns-editor
                :model="new_column"
                :hasPro="has_pro"
                :columns="config.columns"
                :settings="config.settings"
                @add="addNewColumn()"
                @cancel="columnModal = false"
            />
        </el-dialog>
    </div>
</template>
<script type="text/babel">
    import Sortable from 'sortablejs';
    import findIndex from 'lodash/findIndex';
    import snakeCase from 'lodash/snakeCase'

    import addDataModal from './_AddDataModal';
    import NinjaPagination from '../../../common/NinjaPagination.vue';
    import Alert from '../includes/alert.vue';
    import DeletePopOver from '../includes/DeletePopOver.vue';
    import SortableUpgradeNotice from '../includes/SortableUpgradeNotice.vue';
    import columnsEditor from './ColumnEditor/ColumnsEditor';
    import FluentFormNav from '../TableNav/Fluentform';
    import ExternalSourceNav from '../TableNav/External';
    import RawSqlNav from '../TableNav/RawSqlNav';
    import WPPostsNav from '../TableNav/WPPostsNav';
    import WooNavEdit from '../TableNav/WooNavEdit';

    import ShowEditableCell from './_ShowEditableCell'
    import { useEventBus } from '../../eventBus';

    export default {
        name: 'TableDataItems',
        components: {
            add_data_modal: addDataModal,
            ninja_pagination: NinjaPagination,
            Alert,
            DeletePopOver,
            SortableUpgradeNotice,
            columnsEditor,
            FluentFormNav,
            ExternalSourceNav,
            WPPostsNav,
            RawSqlNav,
            WooNavEdit,
            ShowEditableCell
        },
        props: ['config', 'getColumnSettings', 'hasPro'],
        data() {
            return {
                bus : useEventBus(),
                columnModal: false,
                show_meta: false,
                new_column: {},
                has_pro: !!window.ninja_table_admin.hasPro,
                hasSortable: !!window.ninja_table_admin.hasSortable,
                isCompact: true,
                tableWidth: '100%',
                tableData: [],
                searchString: '',
                doingAjax: false,
                addDataModal: false,
                tableId: this.$route.params.table_id,
                loading: false,
                syncing: false,
                bulkAction: -1,
                selectAll: 0,
                checkedItems: [],
                pageLoading: false,
                items: [],
                paginate: {
                    total: 0,
                    current_page: 1,
                    last_page: 1,
                    per_page: 20
                },
                multipleSelection: [],
                updateItem: null,
                editIndex: null,

                // is table row soring enabled flag.
                sorting: false,
                sortableInstance: null,
                sortableUpgradeNotice: false,
                // insert after
                insertAfterPosition: null,

                showColumnEditor: false,
                currentEditingColumn: false,
                addDataModalTitle: 'Add Row',
                dataModalType: 'add',
                dataSource: 'default',
                // Used for external data sources
                isUpdatingTableSettings: false,
                externalDataSourceUrl: this.config.table.remoteURL,
                insertAfterId: false,
                insertAfterHash: false
            }
        },
        watch: {
            searchString() {
                if (this.searchString == '') {
                    this.getData();
                }
            },
            sorting(newVal) {
                if (newVal) {
                    if (!this.has_pro) {
                        this.sorting = false;
                        this.bus.emit('show_pro_popup');

                        return;
                    }

                    if (!this.hasSortable) {
                        this.sorting = false;
                        this.sortableUpgradeNotice = true;

                        return;
                    }
                }

                this.config.settings.sorting_type = newVal === true ? 'manual_sort' : '';
                this.storeSortingSetting(this.config.settings);
                this.toggleSorting(newVal);
            },
            'new_column.name': function () {
                this.new_column.key = snakeCase(this.new_column.name)
            },
        },
        computed: {
            columns() {
                return this.config && this.config.columns ? this.config.columns : [];
            },
            columnLength() {
                return this.columns.length
            },
            dataSourceType() {
                const c = this.config;
                return (c && 'dataSourceType' in c.table) ? c.table.dataSourceType : 'default';
            },
            isEditable() {
                const c = this.config;
                return (c && 'isEditable' in c.table) ? c.table.isEditable : true;
            },
            isEditableMessage() {
                const c = this.config;
                return (c && 'isEditableMessage' in c.table) ? c.table.isEditableMessage : null;
            }
        },
        methods: {
            storeSortingSetting (settings) {
                let data = {
                    table_settings: settings
                };

                this.$post(`settings/${this.tableId}`, data)
                    .then((res) => {})
                    .catch((error) => {})
                    .finally(() => {
                        this.savingSettings = false;
                    });
            },

            getData() {
                let data = {
                    table_id: this.tableId,
                    page: this.paginate.current_page,
                    per_page: this.paginate.per_page,
                    search: this.searchString,
                    default_sorting: this.config.settings.default_sorting
                };
                this.loading = true;
                return this.$get('tables/'+this.tableId+'/item', data)
                    .then((res) => {
                        this.items = res.data;
                        this.dataSource = res.data_source;
                        this.paginate.total = parseInt(res.total);
                        this.paginate.last_page = parseInt(res.last_page);
                        this.loading = false;
                    })
                    .catch((err) => {
                      console.log(err);
                      this.loading = false;
                    })
            },
            addTableData() {

            },
            getItemNumber(index) {
                return this.paginate.per_page * (this.paginate.current_page - 1) + (index + 1);
            },
            goToPage(value) {
                this.paginate.current_page = value;
                this.getData();
            },
            handleSizeChange(val) {
                this.paginate.per_page = val;
                this.getData();
            },
            confirmDeleteTable(tableId) {
                if (confirm(this.$t('Are you sure, You want to delete this table'))) {
                    this.deleteTable(tableId);
                }
            },
            deleteTable(tableId) {
                this.$del("tables/"+tableId)
                    .then((response) => {
                        this.fetchTables();
                        alert(response.message);
                    })
                    .catch((error) => {
                        alert(error.responseJSON.data.message);
                    });
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            handleBulkAction() {
                if (this.multipleSelection.length) {
                    if (this.bulkAction == 'delete') {
                        this.handleBulkDelete();
                    }
                }
            },
            handleBulkDelete() {
                this.$confirm(this.$t('This will permanently delete the selected rows. Continue?'), 'Warning', {
                    confirmButtonText: this.$t('Yes, Delete'),
                    cancelButtonText: this.$t('Cancel'),
                    type: 'warning'
                }).then(() => {
                    let ids = this.multipleSelection.map(item => item.id);
                    this.deleteItem(ids);
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: this.$t('Delete canceled')
                    });
                });

            },
            deleteItem(id) {
                let data = {

                    table_id: this.tableId,
                    id: id
                };

                let that = this;

                this.$del('tables/'+this.tableId+'/item', data)
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.message,
                            type: 'success'
                        });
                        that.getData();
                    })
                    .catch(error => {
                        this.$message({
                            showClose: true,
                            message: this.$t('Something is wrong! Please try again'),
                            type: 'error'
                        });
                    });
            },
            closeDataModal(success) {
                this.addDataModal = false;
                // this.updateItem = null;
                this.editIndex = null;
                this.insertAfterPosition = null;
                this.dataModalType = 'add';
                this.insertAfterId = false;
                this.insertAfterHash = false;
                if (success) {
                    this.getData();
                }
            },
            updateItemOnTable(item) {
                this.items[this.editIndex].values = item.values;
                this.items[this.editIndex].settings = item.settings;
                if (item.created_at) {
                    this.items[this.editIndex].created_at = item.created_at;
                }
            },
            addItemOnTable(item, position) {
                if (!position) {
                    position = item.position;
                }

                if (position == 'last-first') {
                    if (this.config.settings.default_sorting == 'new_first') {
                        position = 'first';
                    } else {
                        position = 'last';
                    }
                }

                if (position) {
                    if (position == 'last') {
                        this.items.push(item);
                    } else if (position == 'first') {
                        this.items.unshift(item);
                    } else if (this.insertAfterHash !== false) {
                        this.insertAfterHash++;
                        this.items.splice(this.insertAfterHash, -1, item);
                    } else {
                        this.items.push(item);
                    }
                } else {
                    this.items.unshift(item);
                }
                if (this.insertAfterPosition) {
                    this.insertAfterPosition = item.position;
                }
                if (this.insertAfterId) {
                    this.insertAfterId = item.id;
                }
                this.paginate.total++;
            },
            showUpdateModal(item) {
                this.updateItem = item.row;
                this.editIndex = item.$index;
                this.addDataModal = true;
                this.dataModalType = 'update';
                this.addDataModalTitle = 'Update Row';
            },

            addColumn() {
                this.columnModal = true;
                // Force Vue to update the component state
                this.$nextTick(() => {
                    // Ensure the modal is visible
                    if (!this.columnModal) {
                        this.columnModal = true;
                    }
                });
            },

            validateColumn(column) {
                if (!column.name) {
                    this.$message({
                        showClose: true,
                        message: this.$t('Name is required'),
                        type: 'error'
                    });
                    return false;
                }
                if (!column.key) {
                    this.$message({
                        showClose: true,
                        message: this.$t('Column Key is required'),
                        type: 'error'
                    });
                    return false;
                }
                // check uniqueness
                let uniqueStatus = findIndex(this.columns, (co) => {
                    return co.key == column.key
                });
                if (uniqueStatus === -1) {
                    return true;
                }
                this.$message({
                    showClose: true,
                    message: this.$t('Column Key needs to be unique. Please add a suffix / prefix to make it unique'),
                    type: 'error'
                });
                return false;
            },

            addNewColumn() {
                if (this.validateColumn(this.new_column)) {
                    this.config.columns.push(this.new_column);
                    this.setNewColumn();
                    this.columnModal = false;
                    this.storeSettings();
                }
            },

            setNewColumn() {
                let newColumn = {
                    name: '',
                    key: '',
                    breakpoints: '',
                    data_type: 'text',
                    dateFormat: '',
                    timeFormat: '',
                    header_html_content: "",
                    enable_html_content: false,
                };
                if (this.dataSourceType === 'wp-posts') {
                    newColumn.source_type = 'custom';
                }
                this.new_column = newColumn;
            },

            /**
             * Sortable JS initiate for table
             */
            initSortable() {
                const table = document.querySelector('.js-sortable-table tbody');
                const self = this;
                this.sortableInstance = Sortable.create(table, {
                    onEnd({newIndex, oldIndex}) {
                        let oldItem = self.items[oldIndex];
                        self.sortTable(oldItem.id, self.items[newIndex].position);

                        const targetRow = self.items.splice(oldIndex, 1)[0];

                        self.items.splice(newIndex, 0, targetRow);
                    }
                });
            },
            toggleSorting(shouldSort) {
                if (shouldSort) {
                    this.loading = true;

                    let promise = new Promise((resolve, reject) => {
                        this.bus.emit('initManualSorting', {
                            table_id: this.tableId,
                            page: this.paginate.current_page,
                            per_page: this.paginate.per_page,
                            search: this.searchString,
                            default_sorting: this.config.settings.default_sorting
                        }, resolve, reject);
                    });

                    promise.then(res => {
                        this.items = res.data;
                        this.paginate.total = parseInt(res.total);
                        this.paginate.last_page = parseInt(res.last_page);

                        // Manually set the sorting type so that we
                        // don't need to load the settings again.
                        this.config.settings['sorting_type'] = 'manual_sort';

                        this.initSortable();
                    }).catch(e => {
                        console.log(e);
                    }).then(() => {
                        this.loading = false;
                    });
                } else {
                    if (this.sortableInstance) {
                        this.sortableInstance.destroy();
                    }
                }
            },
            sortTable(id, newPosition) {
                this.loading = true;

                let data = {
                    // action: "ninja_tables_sort_table",
                    table_id: this.tableId,
                    id,
                    newPosition,
                    page: this.paginate.current_page,
                    per_page: this.paginate.per_page,
                    search: this.searchString,
                    default_sorting: this.config.settings.default_sorting
                };

                this.$post('pro/sortable', data)
                    .then(res => {
                        this.items = res.data;
                        this.paginate.total = parseInt(res.total);
                        this.paginate.last_page = parseInt(res.last_page);
                    })
                    .catch(e => {
                        console.log(e);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
            add() {
                this.updateItem = null;
                this.insertAfterPosition = null;
                this.addDataModal = true;
                this.dataModalType = 'add';
                this.addDataModalTitle = 'Add Data';
            },
            addAfter(scope) {
                if (!this.hasSortable) {
                    this.sortableUpgradeNotice = true;
                    return
                }

                this.updateItem = null;
                this.addDataModalTitle = 'Add Data';
                this.dataModalType = 'add-after';
                this.insertAfterPosition = parseInt(scope.row.position);
                this.insertAfterHash = scope.$index;
                this.insertAfterId = scope.row.id;
                this.addDataModal = true;
            },
            showColumnConfigModal(selectedColumn) {
                this.currentEditingColumn = this.columns.find(column => column.key === selectedColumn.key);
                this.showColumnEditor = true;
            },
            storeSettings() {
                this.bus.emit('updateTableColumns', () => {
                    this.showColumnEditor = false;
                    this.currentEditingColumn = false;
                    if (this.dataSource && this.dataSource != 'default') {
                        this.getData();
                    }
                });
            },
            duplicateData(item) {
                this.updateItem = JSON.parse(JSON.stringify(item.row));
                this.updateItem.id = null;
                if (this.hasSortable) {
                    this.insertAfterPosition = item.$index + 1;
                }

                this.insertAfterPosition = parseInt(item.row.position);
                this.insertAfterHash = item.$index;
                this.insertAfterId = item.row.id;

                this.addDataModal = true;
                this.dataModalType = 'duplicate';
                this.addDataModalTitle = 'Duplicate Data';
            },
            reloadSettingsAndData() {
                this.getColumnSettings();
                this.getData();
            },
            deleteColumn() {
                let targetIndex = findIndex(this.config.columns, this.currentEditingColumn);
                this.showColumnEditor = false;
                this.currentEditingColumn = false;
                this.config.columns.splice(targetIndex, 1);
                this.$nextTick(() => this.storeSettings());
            },
            compare(key, order) {
                let that = this;
                return function (x, y) {
                  const column = that.columns.find(column => column.key === key);
                  let a = x.values[key];
                  let b = y.values[key];

                  if (column.data_type === 'number') {
                    a = parseFloat(a);
                    b = parseFloat(b);
                  } else if (column.data_type === 'date') {
                    a = new Date(a).getTime();
                    b = new Date(b).getTime();
                  }

                  if (a === b) {
                    return 0;
                  } else if (a < b) {
                    return order === 'ascending' ? -1 : 1;
                  } else {
                    return order === 'ascending' ? 1 : -1;
                  }
                }
            },
            onSortChange({prop, order }) {
               this.items.sort(this.compare(prop, order))
            }
        },
        mounted() {
            this.getData();
            this.tableWidth = jQuery('.wrap').width() + 'px';
            this.setNewColumn();
            if (this.config.settings.sorting_type === 'manual_sort') {
                this.sorting = true;
            }
        }
    }
</script>
<style lang="scss">
    .el-table {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .alert-warning {
        color: #8a6d3b;
        background-color: #fcf8e3;
        border-color: #faebcc;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .sorting tr {
        cursor: move;
    }

    .el-table__header {
        tr th:hover {
            .nt-column-config {
                opacity: 1;
            }
        }
    }

    .nt-column-config {
        padding-left: 5px;
        cursor: pointer;
        opacity: 0;
        display: inline-block;
        &:hover {
            color: #58B7FF;
        }
    }

    .instruction_block {
        padding: 30px 20px;
        background: white;
    }

    .js-sortable-table {
      table {
        tr {
          td,
          th {
            span {
              word-break: break-word;
              i {
                padding: 0;
              }
            }
          }
        }
      }
    }
</style>
