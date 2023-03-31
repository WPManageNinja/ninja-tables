<template>
    <div class="ninja_custom_filter_wrapper">
        <div class="ninja_header">
            <h2 class="ninja_block">Custom Search Filters</h2>
            <p>
                Custom Search Filters is useful if you want to add select box / Radio Button to show a group of rows of
                your table.
                <br/>
                To learn more about this <a target="_blank"
                                            href="https://ninjatables.com/docs/custom-filters/">click here</a>
            </p>
        </div>
        <div style="margin: 25px 0" v-loading="loading" class="ninja_style_wrapper">
            <div v-if="hasAdvancedFilters" class="section_block">
                <el-button @click="showAddFilter()" size="small" type="primary">Add New Filter</el-button>
                <template v-if="table_filters.length">
                    <table style="margin: 20px 0" class="wp-list-table table-bordered widefat fixed striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Target Columns</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <draggable
                                :options="{handle:'.handle'}"
                                :list="table_filters"
                                :element="'tbody'"
                                @change="saveFilters()"
                        >
                            <tr v-for="(table_filter, filter_index) in table_filters">
                                <td><span class="dashicons dashicons-editor-justify handle"></span> {{ table_filter.title }}</td>
                                <td>{{ table_filter.type }}</td>
                                <td>
                                    <code v-for="columnKey in table_filter.columns" v-show="columnKeyPairs[columnKey]">
                                        {{ columnKeyPairs[columnKey] }}
                                    </code>
                                </td>
                                <td>
                                    <el-button @click="edit(table_filter)" size="mini" type="primary"
                                               icon="el-icon-edit"></el-button>
                                    <el-button size="mini" @click="deleteFilter(filter_index)" type="danger"
                                               icon="el-icon-delete"></el-button>
                                </td>
                            </tr>
                        </draggable>
                    </table>
                    <h3>Filter Appearance</h3>
                    <el-radio-group v-model="filter_styling.filter_display_type">
                        <el-radio label="inline">Show filter inputs as inline</el-radio>
                        <el-radio label="columns">Show filter inputs as Columns</el-radio>
                    </el-radio-group>
                    <template v-if="filter_styling.filter_display_type == 'columns'">
                        <h3>Filter Columns</h3>
                        <el-radio-group size="mini" v-model="filter_styling.filter_columns">
                            <el-radio-button label="columns_2">Two Columns</el-radio-button>
                            <el-radio-button label="columns_3">Three Columns</el-radio-button>
                            <el-radio-button label="columns_4">Four Columns</el-radio-button>
                        </el-radio-group>
                    </template>

                    <h3>Progressive Filter</h3>
                    <el-checkbox true-label="yes" false-label="no" v-model="filter_styling.progressive">Enable Progressive filter for dynamic filter options</el-checkbox>

                    <div style="margin-top: 20px" class="form_group">
                        <el-button :loading="saving" size="small" type="primary" @click="saveFilters">Update Settings</el-button>
                    </div>
                </template>
            </div>
            <div v-else-if="hasPro" class="section_block">
                <h3>Custom Filters is introduced in version 2.4.0. Please update <b>Ninja tables pro</b> plugin to use
                    this feature</h3>
            </div>
            <div v-else class="section_block text-center">
                <h3>Custom Filters is pro only features. Please purchase <b>"Ninja Tables Pro"</b> to use this feature
                </h3>
                <get-pro/>
            </div>
        </div>

        <el-dialog
                title="Edit Custom Filter"
                :visible.sync="editorModal"
                width="70%"
                top="50px"
                :append-to-body="true">
            <ninja-filter-editor v-if="activeEditor" :columns="columns" :columnKeyPairs="columnKeyPairs" :activeEditor="activeEditor"></ninja-filter-editor>
            <span slot="footer" class="dialog-footer">
                <el-button @click="editorModal = false">Cancel</el-button>
                <el-button type="primary" @click="updateFilter(activeEditor)">Update</el-button>
            </span>
        </el-dialog>

        <el-dialog
                title="Add New Custom Filter"
                :visible.sync="addFilterModal"
                width="70%"
                top="50px"
                :append-to-body="true">
            <ninja-filter-editor v-if="activeEditor" :columns="columns" :columnKeyPairs="columnKeyPairs" :activeEditor="activeEditor"></ninja-filter-editor>
            <span slot="footer">
                <el-button size="small" @click="addFilterModal = false">Cancel</el-button>
                <el-button size="small" type="primary" @click="addFilter(activeEditor)">Add</el-button>
            </span>
        </el-dialog>

    </div>
</template>

<script type="text/babel">

    import each from 'lodash/each';
    import NinjaFilterEditor from './_filterEditor'
    import draggable from 'vuedraggable'
    import GetPro from "../../Tools/GetPro";

    export default {
        name: 'custom_filter',
        props: ['table_id', 'columns'],
        components: {
          GetPro,
            NinjaFilterEditor,
            draggable
        },
        data() {
            return {
                loading: false,
                saving: false,
                hasPro: !!window.ninja_table_admin.hasPro,
                hasAdvancedFilters: !!window.ninja_table_admin.hasAdvancedFilters,
                table_filters: [],
                activeEditor: false,
                editorModal: false,
                addFilterModal: false,
                filter_styling: {
                    filter_display_type: '',
                    filter_columns: 'columns_2',
                    filter_column_label: 'new_line'
                }
            }
        },
        computed: {
            columnKeyPairs() {
                let formattedColumns = {};
                each(this.columns, (column) => {
                    formattedColumns[column.key] = column.name;
                });
                return formattedColumns;
            }
        },
        methods: {
            each,
            fetchFilters() {
                this.loading = true;
                this.$get('pro/custom-filter', {
                  table_id: this.table_id
                })
                    .then((response) => {
                        this.table_filters = response.data.table_filters;
                        this.filter_styling = response.data.filter_styling;
                    })
                    .catch(error => {

                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
            updateFilter(filter) {
                if (this.validateFilter(filter)) {
                    this.saveFilters();
                }
            },
            validateFilter(filter) {
                if (!filter.title) {
                    this.$message.error('Please Provide Filter Title');
                    return false;
                }
                if (!filter.options.length) {
                    this.$message.error('Please Provide Filter Options');
                    return false;
                }

                if ( filter.type != 'reset_filter' && filter.type != 'select'  && !filter.columns.length) {
                    this.$message.error('Please Select columns that you need to add filter');
                    return false;
                }

                if(filter.type == 'select' && filter.select_value_type == 'dynamic_data' && !filter.dynamic_select_column) {
                    this.$message.error('Please Select Target Column');
                    return false;
                }

                return true;
            },
            saveFilters() {
                this.saving = true;
                let data = {
                  table_id: this.table_id,
                  ninja_filters: this.table_filters,
                  filter_styling: this.filter_styling
                };

                this.$post('pro/custom-filter', data)
                    .then((response) => {
                        this.$message.success(response.data.message);
                    })
                    .catch(error => {

                    })
                    .finally(() => {
                        this.saving = false;
                        this.activeEditor = false;
                        this.editorModal = false;
                        this.addFilterModal = false;
                    });
            },
            showAddFilter() {
                this.activeEditor = {
                    placeholder: "All",
                    options: [{
                        value: '',
                        label: ''
                    }],
                    type: "select",
                    columns: [],
                    strict: 'no',
                    title: ""
                };
                this.addFilterModal = true;
            },
            addFilter(filter) {
                if (this.validateFilter(filter)) {
                    this.table_filters.push(filter);
                    this.$nextTick(() => {
                        this.saveFilters();
                    });
                }
            },
            edit(row) {
                this.activeEditor = row;
                this.editorModal = true;
            },
            deleteFilter(index) {
                this.table_filters.splice(index, 1);
                this.saveFilters();
            }
        },
        mounted() {
            if (this.hasAdvancedFilters) {
                this.fetchFilters();
            }
        }
    }
</script>
