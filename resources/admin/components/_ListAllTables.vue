<template>
    <div>
        <el-table
            class="ninja-tables compact"
            @selection-change="handleSelectionChange"
            v-loading.body="pageLoading"
            :data="items"
            border
            aria-label="all-tables"
            @sort-change="handleTableSort"
            style="100%">

            <!-- <el-table-column type="selection" fixed width="55" /> -->

            <el-table-column :label="$t('ID')" width="90" prop="ID" sortable="custom">
              <template slot-scope="scope">
                <router-link v-if="scope.row.dataSourceType === 'drag_and_drop'" :to="{ name: 'table_builder_edit_table', params: { table_id: scope.row.ID } }">
                  {{ scope.row.ID }}
                </router-link>
                <router-link v-else :to="{ name: 'data_items', params: { table_id: scope.row.ID } }">
                  {{ scope.row.ID }}
                </router-link>
              </template>
            </el-table-column>

            <el-table-column :label="$t('Title')" prop="post_title" sortable="custom">
                <template slot-scope="scope">
                    <strong>
                        <template v-if="shouldBeVisible(scope.row)">
                          <router-link v-if="scope.row.dataSourceType === 'drag_and_drop'" :to="{ name: 'table_builder_edit_table', params: { table_id: scope.row.ID } }">
                            {{$t(scope.row.post_title)}}
                          </router-link>
                          <router-link v-else :to="{ name: 'data_items', params: { table_id: scope.row.ID } }">
                            {{$t(scope.row.post_title)}}
                          </router-link>
                        </template>

                        <template v-else>
                            {{ scope.row.post_title }}
                        </template>

                        <span v-show="scope.row.post_status != 'publish'">
                            ({{ scope.row.post_status }})
                        </span>
                    </strong>

                    <div class="row-actions">
                        <span class="row-edit" v-if="shouldBeVisible(scope.row)">
                              <router-link v-if="scope.row.dataSourceType === 'drag_and_drop'" :to="{ name: 'table_builder_edit_table', params: { table_id: scope.row.ID } }">
                                {{$t('Edit')}}
                              </router-link>
                              <router-link v-else :to="{ name: 'data_items', params: { table_id: scope.row.ID } }">
                                {{$t('Edit')}}
                              </router-link> |
                        </span>

                        <span class="row-preview" v-if="shouldBeVisible(scope.row)">
                            <a rel="noopener" :href="scope.row.preview_url" target="_blank">{{ $t('Preview') }}</a> |
                        </span>

                        <span class="row-duplicate" v-if="shouldBeVisible(scope.row)">
                            <a href="#" @click.prevent="duplicate(scope.row.ID, scope.row.dataSourceType)">{{ $t('Duplicate') }}</a> |
                        </span>
                        <span class="row-duplicate" v-if="shouldBeVisible(scope.row) && scope.row.fluentfrom_url">
                            <a :href="scope.row.fluentfrom_url" >{{ $t('Fluent Form Entries') }}</a> |
                        </span>

                        <span class="row-delete">
                            <a @click.prevent="confirmDeleteTable(scope.row.ID)" href="#">{{ $t('Delete') }}</a>
                        </span>
                    </div>
                </template>
            </el-table-column>

            <el-table-column :label="$t('Description')" class-name="description">
                <template slot-scope="scope">
                    <div class="nt_cell" v-html="scope.row.post_content"/>
                </template>
            </el-table-column>

            <el-table-column width="190" :label="$t('Data Source')">
                <template slot-scope="scope">
                    <strong v-if="scope.row.dataSourceType === 'drag_and_drop'">{{$t('Drag & Drop Table')}}</strong>
                    <strong v-else>{{ dataSourceType(scope.row) }}</strong>
                    <template v-if="scope.row.remoteURL">
                        <el-tooltip class="item" effect="light" :content="scope.row.remoteURL" placement="top-start">
                          <div slot="content">
                            <h3>Source of data</h3>
                            <p>{{scope.row.remoteURL}}</p>
                          </div>
                          <i class="el-icon-info el-text-info"/>
                        </el-tooltip>
                    </template>
                </template>
            </el-table-column>

            <el-table-column width="250" :label="$t('ShortCode')">
                <template slot-scope="scope">
                    <el-tooltip effect="dark"
                                content="Click to copy shortcode"
                                title="Click to copy shortcode"
                                placement="top">
                        <code class="copy"
                              v-if="scope.row.dataSourceType === 'drag_and_drop'"
                                :data-clipboard-text='`[ninja_table_builder id="${scope.row.ID}"]`'>
                            <i class="el-icon-document"></i> [ninja_table_builder id="{{ scope.row.ID }}"]
                        </code>
                        <code class="copy"
                              v-else
                              :data-clipboard-text='`[ninja_tables id="${scope.row.ID}"]`'>
                          <i class="el-icon-document"></i> [ninja_tables id="{{ scope.row.ID }}"]
                        </code>
                    </el-tooltip>
                </template>
            </el-table-column>
        </el-table>

        <div class="pull-right">
            <el-pagination
                    @size-change="handleSizeChange"
                    @current-change="goToPage"
                    :current-page.sync="paginate.current_page"
                    :page-sizes="[10, 20, 50, 100]"
                    :page-size="paginate.per_page"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="paginate.total">
            </el-pagination>
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
    export default {
        name: 'Home',
        components: {
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
            fetchTables() {
                this.pageLoading = true;

                this.$get('all-tables', {
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
                      vueNotification.error('Something went wrong, please try again.');
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
                let data = {
                    action: 'ninja_tables_ajax_actions',
                    target_action: 'delete-a-table',
                    table_id: tableId
                };

                this.$post(data)
                    .then((response) => {
                        this.fetchTables();
                        this.$message({
                            type: 'success',
                            message: response.message
                        });
                    })
                    .fail((error) => {
                        alert(error.responseJSON.data.message);
                    });
            },
            handleSelectionChange(tables) {
                this.$emit('selection', tables.map(table => table.ID));
            },
            duplicate(tableId, source = '') {
                let data = {
                    action: 'ninja_tables_ajax_actions',
                    target_action: 'duplicate-table',
                    tableId: tableId
                };

                this.$post(data)
                    .then((response) => {
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
                    .fail((error) => {
                        alert(error.responseJSON.data.message);
                    });
            },
            shouldBeVisible(table) {
                if(table.dataSourceType == 'fluent-form') {
                    return window.ninja_table_admin.hasFluentForm;
                }

                return true;
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
</style>

