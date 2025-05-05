<template>
    <div>
        <span v-if="doingAjax" v-loading="doingAjax" class="doingAJaxLoading"></span>

        <div class="settings_header">
            <div style="display: inline-block; margin-top: 8px;">
                <el-button class="ninja_mini" size="small" @click="editTableModalShow = !editTableModalShow"><i title="Edit" class="el-icon-edit action">{{ $t('Edit') }}</i></el-button> <span
                    class="section_title">{{ table.post_title }}</span>
                <el-tooltip effect="dark"
                            content="Click to copy shortcode"
                            title="Click to copy shortcode"
                            placement="top">
                    <code class="copy"
                          :data-clipboard-text='`[ninja_tables id="${tableId}"]`'>
                        <i class="el-icon-document"></i> [ninja_tables id="{{ tableId }}"]
                    </code>
                </el-tooltip>
            </div>

            <span style="margin-right: 20px" class="pull-right">
                <router-link class="doc_link" :to="{ name: 'help' }">{{ $t('Documentation') }}</router-link>
                <a :href="preview_url" target="_blank">
                    <el-button size="small">{{ $t('Preview') }}</el-button>
                </a>
                <a v-if="!has_pro"
                   href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade"
                   target="_blank">
                    <el-button type="danger" size="small">{{ $t('Get Pro') }}</el-button>
                </a>
            </span>
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
<style lang="scss">
    .settings_header {
        font-size: 20px;
        padding-bottom: 20px;
        background: white;
        margin-top: -20px;
        padding-top: 20px;
        margin-right: -20px;
        margin-left: -20px;
        padding-left: 24px;
        .action {
            font-size: 16px;
            cursor: pointer;
            &:hover {
                color: #0085ba;
            }
        }
    }
</style>
