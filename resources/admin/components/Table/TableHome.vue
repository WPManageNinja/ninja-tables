<template>
    <div>
        <span v-if="doingAjax" v-loading="doingAjax" class="doingAJaxLoading"></span>

        <div class="ninja_inner_nav">
            <div class="ninja_inner_nav_left">
                <router-link to="/" class="nav-all-tables">All Tables</router-link>
                <span class="mx-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path
                            d="M10.7958 9.9992L7.08334 6.2867L8.14384 5.2262L12.9168 9.9992L8.14384 14.7722L7.08334 13.7117L10.7958 9.9992Z"
                            fill="#CACFD8" />
                    </svg>
                </span>
                <span class="nav-table-name">{{ table.post_title }}</span>
                <img
                    @click="editTableModalShow = !editTableModalShow"
                    class="cursor-pointer"
                    :src="assetUrl('icons/edit-2.svg')"
                    alt="Edit Table"
                />
            </div>

            <div class="ninja_inner_nav_right">
                <div class="copy_shortcode">
                    <el-tooltip effect="dark"
                                content="Click to copy shortcode"
                                title="Click to copy shortcode"
                                placement="top">
                        <code class="copy flex"
                              :data-clipboard-text='`[ninja_tables id="${tableId}"]`'>
                            <img :src="assetUrl('icons/copy-02.svg')" class="mr-2" alt="copy" />
                            [ninja_tables id="{{ tableId }}"]
                        </code>
                    </el-tooltip>
                </div>

                <router-link :to="{ name: 'help' }">
                    <el-button>{{ $t('Documentation') }}</el-button>
                </router-link>
                <a :href="preview_url" target="_blank">
                    <el-button >{{ $t('Preview') }}</el-button>
                </a>
                <a v-if="!has_pro"
                   href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade"
                   target="_blank">
                    <el-button type="danger">{{ $t('Get Pro') }}</el-button>
                </a>
            </div>


<!--            <div style="display: inline-block; margin-top: 8px;">-->
<!--                <el-button class="ninja_mini" size="small" @click="editTableModalShow = !editTableModalShow"><i title="Edit" class="el-icon-edit action">{{ $t('Edit') }}</i></el-button> <span-->
<!--                    class="section_title">{{ table.post_title }}</span>-->
<!--                <el-tooltip effect="dark"-->
<!--                            content="Click to copy shortcode"-->
<!--                            title="Click to copy shortcode"-->
<!--                            placement="top">-->
<!--                    <code class="copy"-->
<!--                          :data-clipboard-text='`[ninja_tables id="${tableId}"]`'>-->
<!--                        <i class="el-icon-document"></i> [ninja_tables id="{{ tableId }}"]-->
<!--                    </code>-->
<!--                </el-tooltip>-->
<!--            </div>-->

        </div>

        <fieldset :class="[is_form_saving ? 'disabled' : '']" :disabled="is_form_saving">
            <h2 class="nav-tab-wrapper">
                <router-link v-for="tableTab in table_tabs" :key="tableTab.route" active-class="nav-tab-active" exact :class="[ 'nav-tab' ]"
                             :to="{ name: tableTab.route, params: { table_id: tableId } }">
                    {{ tableTab.title }}
                </router-link>
            </h2>

            <router-view v-if="config" :config="config" :getColumnSettings="getSettings"></router-view>

        </fieldset>
        <el-dialog
                title="Update Table Info"
                v-model="editTableModalShow"
                top="50px"
                :append-to-body="true"
        >
            <edit_table v-if="editTableModalShow" :table="table" @modal_close="editTableModalShow = !editTableModalShow"></edit_table>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
    import EditTable from './EditTableModal';
    import each from 'lodash/each';
    import size from 'lodash/size';
    import toArray from 'lodash/values';
    import { useEventBus } from '../../eventBus';
    import { assetUrl } from "../../utils/ninjatablesadmin";

    export default {
        name: 'table_home',
        components: {
            'edit_table': EditTable
        },
        data() {
            return {
                bus : useEventBus(),
                table_tabs: [],
                is_data_saving: false,
                is_form_saving: false,
                tableId: this.$route.params.table_id,
                config: null,
                table: {},
                doingAjax: false,
                doingAjaxTest: false,
                user_tab: this.$route.query.user_tab,
                editTableModalShow: false,
                preview_url: '#',
                has_pro: window.ninja_table_admin.hasPro
            }
        },
        methods: {
            assetUrl,
            updateTableColumns(callback) {
              let tableId = this.tableId;

              let data = {
                table_id: this.tableId,
                columns: this.config.columns
              }

              this.$post('settings/'+tableId, data)
                  .then((res) => {
                      this.$message({
                          showClose: true,
                          message: res.message,
                          type: 'success'
                      });
                      callback(res)
                  })
            },
            getSettings() {
              let tableId = this.tableId;

                this.$get('settings/'+tableId)
                    .then(response => {
                        if (Object.prototype.toString.call(response.columns) == '[object Object]') {
                            response.columns = toArray(response.columns);
                        }
                        this.config = response;
                        this.table = response.table;
                        this.preview_url = response.preview_url;
                    })
                    .catch((error) => {
                        this.$message.error(error.responseJSON.data.message);
                        if(error.responseJSON.data.route) {
                            this.$router.push({ name: error.responseJSON.data.route });
                        }
                    })
            },
            goToTab(key) {
                this.user_tab = key;
                this.$router.push({
                    name: 'custom_tab',
                    params: {table_id: this.tableId},
                    query: {user_tab: key}
                });
            },
            size,
            each,
           initTableTabs() {
                this.table_tabs = this.applyFilters('ninja_table_table_tabs', [
                    {
                        route: 'data_items',
                        title: 'Table Rows'
                    },
                    {
                        route: 'data_columns',
                        title: 'Table Configuration'
                    },
                    {
                        route: 'design_studio',
                        title: 'Table Design'
                    },
                    {
                        route: 'table_editing',
                        title: 'Frontend Editing'
                    },
                    {
                        route: 'additional_css',
                        title: 'Custom CSS/JS'
                    },
                    {
                        route: 'import-export',
                        title: 'Import - Export'
                    }
                ]);
            }
        },
        mounted() {
            this.initTableTabs();
            this.getSettings();
            this.clipboard();

            // Initialize the table's manual data sorting.
            this.bus.on('initManualSorting', (options, resolve, reject) => {
                let data = {
                    ...options
                };

                this.$post('pro/sortable/init', data)
                    .then(response => resolve(response))
                    .catch(e => reject(e));
            });

            this.bus.on('tableDoingAjax',  (value) => {
                this.doingAjax = value;
            });

            // removes previous events to prevent duplicate event handlers.
            this.bus.off('updateTableColumns');

            this.bus.on('updateTableColumns', (callback) => {
                this.updateTableColumns(callback);
            });

            this.bus.emit('addedTable');

            // window.ninjaTableBus.$emit('addedTable');
        }
    }
</script>
