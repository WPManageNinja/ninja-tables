<template>
    <div>
        <el-table
            class="ninja_tables compact"
            @selection-change="handleSelectionChange"
            v-loading.body="pageLoading"
            :data="items"
            aria-label="all-tables"
            @sort-change="handleTableSort"
            style="width: 100%">

            <!-- <el-table-column type="selection" fixed width="55" /> -->

            <el-table-column :label="$t('ID')" width="90" prop="ID" sortable="custom">
              <template #default="scope">
                <router-link v-if="scope.row.dataSourceType === 'drag_and_drop'" :to="{ name: 'table_builder_edit_table', params: { table_id: scope.row.ID } }">
                  {{ scope.row.ID }}
                </router-link>
                <router-link v-else :to="{ name: 'data_items', params: { table_id: scope.row.ID } }">
                  {{ scope.row.ID }}
                </router-link>
              </template>
            </el-table-column>

            <el-table-column :label="$t('Title')" prop="post_title" sortable="custom" width="200">
                <template #default="scope">
                    <template v-if="shouldBeVisible(scope.row) && scope.row.ID">
                        <router-link v-if="scope.row.dataSourceType === 'drag_and_drop'" :to="{ name: 'table_builder_edit_table', params: { table_id: scope.row.ID } }">
                            {{$t(scope.row.post_title)}}
                        </router-link>
                        <router-link v-else :to="{ name: 'data_items', params: { table_id: scope.row.ID } }">
                            {{$t(scope.row.post_title)}}
                        </router-link>
                    </template>
                    <template v-else-if="shouldBeVisible(scope.row) && !scope.row.ID">
                        {{$t(scope.row.post_title)}}
                    </template>

                        <template v-else>
                            {{ scope.row.post_title }}
                        </template>

                        <span v-show="scope.row.post_status !== 'publish'">
                            ({{ scope.row.post_status }})
                        </span>
                </template>
            </el-table-column>

            <el-table-column :label="$t('Description')" class-name="description" width="300">
                <template #default="scope">
                    <div class="nt_cell" v-html="scope.row.post_content"/>
                </template>
            </el-table-column>

            <el-table-column :label="$t('Data Source')" width="200">
                <template #default="scope">
                    <span v-if="scope.row.dataSourceType === 'drag_and_drop'">{{$t('Drag & Drop Table')}}</span>
                    <span v-else>{{ dataSourceType(scope.row) }}</span>
                </template>
            </el-table-column>

            <el-table-column :label="$t('ShortCode')" :min-width="200">
                <template #default="scope">
                    <div class="flex items-center" type="info" :style="{ cursor: 'pointer' }">
                        <div class="bg-[#F5F6F7] px-2 py-1 rounded-[8px] flex items-center copy"
                             :data-clipboard-text="scope.row.dataSourceType === 'drag_and_drop' ? 
                                `[ninja_table_builder id='${scope.row.ID}']` : 
                                `[ninja_tables id='${scope.row.ID}']`"
                             style="border: 1px solid #E1E4EA">
                            <img class="mr-2" :src="assetUrl('icons/copy-02.svg')"/> 
                            <span class="text-sm">
                                {{ scope.row.dataSourceType === 'drag_and_drop' ? 
                                   `[ninja_table_builder id='${scope.row.ID}']` : 
                                   `[ninja_tables id='${scope.row.ID}']` }}
                            </span>
                        </div>
                    </div>
                </template>
            </el-table-column>

            <el-table-column fixed="right" min-width="150">
                <template #default="scope">
                    <div class="flex justify-end items-center">
                       <span
                           v-if="shouldBeVisible(scope.row)"
                           @click="()=>onRedirectPreview(scope.row.preview_url)"
                           class="icons_bg"
                       >
                           <img :src="assetUrl('icons/view.svg')" alt="View"/>
                       </span>

                        <span
                            v-if="shouldBeVisible(scope.row)"
                            class="icons_bg ml-2"
                        >
                          <router-link v-if="scope.row.dataSourceType === 'drag_and_drop'" :to="{ name: 'table_builder_edit_table', params: { table_id: scope.row.ID } }">
                            <img :src="assetUrl('icons/setting-02.svg')" alt="Edit"/>
                          </router-link>
                          <router-link v-else :to="{ name: 'data_items', params: { table_id: scope.row.ID } }">
                            <img :src="assetUrl('icons/setting-02.svg')" alt="Edit"/>
                          </router-link>
                        </span>

                        <el-dropdown>
                            <span class="el-dropdown-link no-hover">
                                <img :src="assetUrl('/icons/more.svg')" alt="more"/>
                            </span>

                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item>
                                         <span>
                                             <a @click.prevent="confirmDeleteTable(scope.row.ID)" href="#">{{ $t('Delete') }}</a>
                                         </span>
                                    </el-dropdown-item>

                                    <el-dropdown-item>
                                        <span class="row-duplicate" v-if="shouldBeVisible(scope.row)">
                                            <a href="#" @click.prevent="duplicate(scope.row.ID, scope.row.dataSourceType)">{{ $t('Duplicate') }}</a>
                                        </span>
                                        <span class="row-duplicate" v-if="shouldBeVisible(scope.row) && scope.row.fluentfrom_url">
                                            <a :href="scope.row.fluentfrom_url" >{{ $t('Fluent Form Entries') }}</a> |
                                        </span>
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </div>
                </template>
            </el-table-column>
        </el-table>

        <div class="ninja-pagination-wrapper">

            <div class="pagination-page-change-option">
               <span class="flex-shrink-0">
                  Page {{ paginate.current_page }}
                  of {{ Math.ceil(paginate.total / Number(paginate.per_page)) }}
               </span>

                <el-select class="min-w-[100px]" v-model="paginate.per_page" @change="handleSizeChange">
                    <el-option value="10">{{ $t('10/page') }}</el-option>
                    <el-option value="15">{{ $t('15/page') }}</el-option>
                    <el-option value="20">{{ $t('20/page') }}</el-option>
                    <el-option value="50">{{ $t('50/page') }}</el-option>
                    <el-option value="100">{{ $t('100/page') }}</el-option>
                </el-select>
            </div>

            <el-pagination
                class="ninja-pagination"
                @size-change="handleSizeChange"
                @current-change="goToPage"
                :current-page.sync="paginate.current_page"
                :page-sizes="[10, 20, 50, 100]"
                :page-size="paginate.per_page"
                layout="prev, pager, next, jumper"
                :total="paginate.total"
            />

        </div>

        <div v-if="!loading && !is_installed && items.length > 2 && !hasPro">
            <a style="display: block;width: 800px;margin: 40px auto 0px;max-width: 100%;" target="_blank" href="https://wordpress.org/plugins/fluentform">
                <img style="max-width: 100%" :src="img_url_path+'fluent_banner.png'"/>
            </a>
        </div>
        <div style="margin-top: 100px;" class="text-center" v-else-if="items.length > 3 && !hasPro">
            <hr />
            <h3>Love Ninja Tables? Upgrade to Pro and get more exciting features and Performance</h3>
            <get-pro/>
        </div>
    </div>
</template>

<script type="text/babel">
    import pagination from '../../common/NinjaPagination.vue';
    import GetPro from "./Tools/GetPro";
    import NinjaInput from "../@ui-utils/NinjaInput.vue";
    import {assetUrl} from "../utils/ninjatablesadmin";
    export default {
        name: 'Home',
        components: {
            NinjaInput,
          GetPro,
          'ninja_pagination': pagination
        },
        props: ['searchAction', 'searchString'],
        watch: {
            searchAction() {
                this.paginate.current_page = 1;
                this.fetchTables();
            }
        },
        data() {
            return {
                loading: false,
                bulkAction: -1,
                selectAll: 0,
                checkedItems: [],
                pageLoading: false,
                items: [],
                paginate: {
                    total: 0,
                    current_page: 1,
                    last_page: 1,
                    per_page: parseInt(this.getFromStore('tables_per_page', 20))
                },
                hasPro: !!window.ninja_table_admin.hasPro,
                img_url_path: window.ninja_table_admin.img_url,
                is_installed: window.ninja_table_admin.isInstalled,
                orderBy: 'date',
                order: 'DESC'
            }
        },
        methods: {
            assetUrl,
            fetchTables() {
                this.pageLoading = true;

                this.$get('tables/', {
                      per_page: this.paginate.per_page,
                      page: this.paginate.current_page,
                      search: this.searchString,
                      orderBy: this.orderBy,
                      order: this.order
                })
                    .then(response => {
                      this.items = response.data;
                      this.paginate.total = response.total;
                      this.paginate.current_page = response.current_page;
                      this.paginate.last_page = response.last_page;
                      this.pageLoading = false;
                      if(response.total) {
                        this.$emit('total_table', response.total);
                      }
                    })
                    .catch(error => {
                      console.log('Something went wrong, please try again.');
                    });
            },
            goToPage(value) {
                this.paginate.current_page = value;
                this.fetchTables();
            },
            handleSizeChange(val) {
                this.paginate.per_page = val;
                this.setStoreData('tables_per_page', val);
                this.fetchTables();
            },
            confirmDeleteTable(tableId) {
                this.$confirm('Are you sure, You want to delete this table?', 'Warning', {
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.deleteTable(tableId);
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: 'Delete canceled'
                    });
                });
            },
            deleteTable(tableId) {
              this.$del("tables/"+tableId)
                  .then(response => {
                    this.fetchTables();
                    this.$message({
                      type: 'success',
                      message: response.message
                    });
                  })
                  .catch(error => {
                    alert(error.responseJSON.data.message);
                  });
            },
            handleSelectionChange(tables) {
                this.$emit('selection', tables.map(table => table.ID));
            },
            duplicate(tableId, source = '') {

              this.$post("tables/"+tableId+"/duplicate")
                  .then(response => {
                    this.fetchTables();
                    this.$message({
                      type: 'success',
                      message: response.data.message
                    });
                    if (source === 'drag_and_drop') {
                      this.$router.push({ name: 'table_builder_edit_table', params: { table_id: response.data.table_id } });
                    } else {
                      this.$router.push({ name: 'data_items', params: { table_id: response.data.table_id } });
                    }
                  })
                  .catch(error => {
                    alert(error.responseJSON.data.message);
                  });

            },
            shouldBeVisible(table) {
                if(table.dataSourceType == 'fluent-form') {
                    return window.ninja_table_admin.hasFluentForm;
                }

                return true;
            },
            onRedirectPreview(preview) {
                return window.open(preview, '_blank');
            },

            dataSourceType(table) {
                let dataSource = table.dataSourceType || 'Default';
                if(dataSource == 'raw_sql') {
                    return 'SQL';
                }

                dataSource = dataSource.indexOf('google') > -1 ? 'Google SpreadSheet' : dataSource;
                return dataSource;
            },
            handleTableSort(column) {
                this.orderBy = column.prop;
                this.order = (column.order === 'ascending') ? 'ASC' : 'DESC';
                this.fetchTables();
            }
        },
        mounted() {
            this.fetchTables();
            this.clipboard();
        }
    }
</script>

<style lang="scss">
    .ninja-tables.el-table {
        td, th {
            padding: 5px 0;
        }
        span.row-delete a {
            color: #a00;
        }
        a {
            text-decoration: none;
        }
        .description {
            .cell {
                max-height: 60px;
            }
        }
    }

    .el-dropdown-link {
        outline: none;
        &:hover {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
        }
    }


</style>

