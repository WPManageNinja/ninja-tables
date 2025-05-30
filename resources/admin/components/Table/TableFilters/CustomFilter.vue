<template>
    <div class="ninja_table_filters_wrapper">
        <div class="ninja_table_filters">
            <h2 class="text-[18px] font-[600] text-[#0E121B]">{{ $t('Custom Search Filters') }}</h2>
            <p class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
                {{ $t('Custom Search Filters is useful if you want to add select box / Radio Button to show a group of rows of your table.') }}
                <br/>
                {{ $t('To learn more about this') }}
                <a target="_blank" href="https://ninjatables.com/docs/custom-filters/" class="nt-link">{{ $t('click here') }}</a>
            </p>
        </div>

        <div style="margin: 25px 0" v-loading="loading" class="ninja_style_wrapper">
            <div v-if="hasAdvancedFilters" class="section_block">
                <div class="flex justify-end mb-4">
                    <NinjaButton
                        @click="showAddFilter()"
                        :btn-text="$t('Add New Filter')"
                    />
                </div>

                <div v-if="table_filters.length">
                    <el-table
                        class="nt-inner-table mt-4"
                        :data="table_filters"
                        border
                        row-class-name="handle-custom-filter"
                        :row-key="row => row.title + '-' + row.type"
                    >
                        <el-table-column width="50">
                            <template #default>
                                <img class="cursor-move handle-custom-filter" :src="assetUrl('icons/drag-drop.svg')"/>
                            </template>
                        </el-table-column>
                        <el-table-column label="Name">
                            <template #default="{ row }">
                                {{ row.title }}
                            </template>
                        </el-table-column>
                        <el-table-column label="Type" prop="type" />
                        <el-table-column label="Target Columns">
                            <template #default="{ row }">
                                <code v-for="columnKey in row.columns" v-show="columnKeyPairs[columnKey]">
                                    {{ columnKeyPairs[columnKey] }}
                                </code>
                            </template>
                        </el-table-column>
                        <el-table-column label="Action">
                            <template #default="{ row, $index }">
                                <div class="flex items-center">
                                    <div class="mr-3 mt-[2px] cursor-pointer" @click="edit(row)">
                                        <img :src="assetUrl('icons/edit-2.svg')"/>
                                    </div>
                                    <div class="cursor-pointer" @click="deleteFilter($index)">
                                        <img :src="assetUrl('icons/delete-02.svg')"/>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>

                    <div class="bg-white border border-solid border-[#E1E4EA] p-5 rounded-[12px] mt-6">
                        <h3 class="text-[16px] font-[500] text-[#0E121B]">Filter Appearance</h3>
                        <el-radio-group class="ninja_tables_radio_group" v-model="filter_styling.filter_display_type">
                            <el-radio :label="$t('Show filter inputs as inline')" value="inline" />
                            <el-radio :label="$t('Show filter inputs as Columns')" value="columns" />
                        </el-radio-group>

                        <template v-if="filter_styling.filter_display_type === 'columns'">
                            <h3 class="text-[16px] font-[500] text-[#0E121B] mt-5 mb-2">Filter Columns</h3>
                            <el-radio-group class="ninja_tables_radio_group" size="large" v-model="filter_styling.filter_columns">
                                <el-radio border :label="$t('Two Columns')" value="columns_2" />
                                <el-radio border :label="$t('Three Columns')" value="columns_3" />
                                <el-radio border :label="$t('Four Columns')" value="columns_4" />
                            </el-radio-group>
                        </template>

                        <h3 class="text-[16px] font-[500] text-[#0E121B] mt-5">Progressive Filter</h3>
                        <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="filter_styling.progressive">
                            {{ $t('Enable Progressive filter for dynamic filter options') }}
                        </el-checkbox>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <NinjaButton :loading="saving" @click="saveFilters" :btn-text="$t('Update Settings')" />
                    </div>
                </div>
            </div>

            <div v-else-if="hasPro" class="section_block">
                <h3>Custom Filters is introduced in version 2.4.0. Please update <b>Ninja tables pro</b> plugin to use
                    this feature</h3>
            </div>
            <div v-else class="nt-instruction w-full overflow-hidden text-center">
                <h3 class="text-[16px] font-[400] text-[#525866] my-4">
                   {{$t('Set up custom filters to find the data you want! It’s a Pro feature.')}}
                </h3>
              <div class="flex justify-center mb-4">
                  <a v-if="!hasPro"
                     href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade"
                     target="_blank">
                      <NinjaButton
                          size="small"
                          type="pro"
                          :icon="assetUrl('icons/get-pro.svg')"
                          :btnText="$t('Get Pro')"
                      />
                  </a>
              </div>
            </div>
        </div>

        <el-dialog
            class="ninja_create-table-modal"
            title="Edit Custom Filter"
            v-model="editorModal"
            width="70%"
            top="50px"
            :append-to-body="true">
            <ninja-filter-editor v-if="activeEditor" :columns="columns" :columnKeyPairs="columnKeyPairs" :activeEditor="activeEditor"></ninja-filter-editor>
            <div class="flex justify-end items-center gap-2 p-5">
                <NinjaButton type="secondary" @click="editorModal = false" :btn-text="$t('Cancel')" />
                <NinjaButton @click="updateFilter(activeEditor)" :btn-text="$t('Update')" />
            </div>
        </el-dialog>

        <el-dialog
            title="Add New Custom Filter"
            v-model="addFilterModal"
            width="70%"
            top="50px"
            :append-to-body="true"
            class="ninja_create-table-modal"
        >
            <NinjaFilterEditor v-if="activeEditor" :columns="columns" :columnKeyPairs="columnKeyPairs" :activeEditor="activeEditor" />

            <div class="flex items-center justify-end px-5 pb-4 gap-2">
                <NinjaButton
                    type="secondary"
                    @click="addFilterModal = false"
                    :btn-text="$t('Cancel')"
                />
                <NinjaButton
                    @click="addFilter(activeEditor)"
                    :btn-text="$t('Add')"
                />
            </div>
        </el-dialog>

    </div>
</template>

<script>
import each from 'lodash/each';
import NinjaFilterEditor from './_filterEditor.vue';
import GetPro from "../../Tools/GetPro";
import { Delete, EditPen } from "@element-plus/icons-vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";
import { assetUrl } from "../../../utils/ninjatablesadmin";
import Sortable from 'sortablejs';

export default {
    name: 'custom_filter',
    props: ['table_id', 'columns'],
    components: {
        NinjaButton,
        Delete,
        EditPen,
        GetPro,
        NinjaFilterEditor
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
            sortableInstance: null,
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
        assetUrl,
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
                    console.error('Fetch filters error:', error);
                })
                .finally(() => {
                    this.loading = false;
                    this.$nextTick(() => {
                        this.initCustomFilterSortable();
                    });
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
            if (filter.type != 'reset_filter' && filter.type != 'select' && !filter.columns.length) {
                this.$message.error('Please Select columns that you need to add filter');
                return false;
            }
            if (filter.type == 'select' && filter.select_value_type == 'dynamic_data' && !filter.dynamic_select_column) {
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
                    console.error('Save filters error:', error);
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
                    this.initCustomFilterSortable();
                });
            }
        },
        edit(row) {
            this.activeEditor = row;
            this.editorModal = true;
        },
        deleteFilter(index) {
            this.table_filters.splice(index, 1);
            this.$nextTick(() => {
                this.saveFilters();
                this.initCustomFilterSortable();
            });
        },
        initCustomFilterSortable() {
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }

            const tableBody = this.$el.querySelector('.nt-inner-table tbody');

            if (!tableBody) {
                console.warn('Table body not found for sortable initialization');
                return;
            }

            this.sortableInstance = Sortable.create(tableBody, {
                handle: '.handle-custom-filter',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: ({ newIndex, oldIndex }) => {
                    if (newIndex === oldIndex) return;
                    const movedItem = this.table_filters.splice(oldIndex, 1)[0];
                    this.table_filters.splice(newIndex, 0, movedItem);
                    this.$nextTick(() => {
                        this.saveFilters();
                    });
                }
            });
        }
    },
    mounted() {
        if (this.hasAdvancedFilters) {
            this.fetchFilters();
        }
    },
    beforeUnmount() {
        if (this.sortableInstance) {
            this.sortableInstance.destroy();
            this.sortableInstance = null;
        }
    }
}
</script>
