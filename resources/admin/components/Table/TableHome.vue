<template>
    <div>
        <span v-if="doingAjax" v-loading="doingAjax" class="doingAJaxLoading"></span>

        <div class="ninja_inner_nav flex-wrap">
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
                <img @click="editTableModalShow = !editTableModalShow" class="cursor-pointer"
                    :src="assetUrl('icons/edit-2.svg')" alt="Edit Table" />
            </div>

            <div class="ninja_inner_nav_right flex-wrap">
                <div class="copy_shortcode">
                    <el-tooltip effect="dark" content="Click to copy shortcode" title="Click to copy shortcode"
                        placement="top">
                        <code class="copy flex p-[8px] rounded-[8px] border border-[#E1E4EA]"
                            :data-clipboard-text='`[ninja_tables id="${tableId}"]`'>
                            <img :src="assetUrl('icons/copy-02.svg')" class="mr-2" alt="copy" />
                            [ninja_tables id="{{ tableId }}"]
                        </code>
                    </el-tooltip>
                </div>

                <router-link :to="{ name: 'help' }">
                    <NinjaButton type="secondary" :icon="assetUrl('icons/computer.svg')"
                        :btnText="$t('Documentation')" />
                </router-link>
                <a :href="preview_url" target="_blank">
                    <NinjaButton type="secondary" :icon="assetUrl('icons/view.svg')" :btnText="$t('Preview')" />
                </a>
                <a v-if="!has_pro"
                    href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade"
                    target="_blank">
                    <NinjaButton
                        type="danger"
                        :btnText="$t('Get Pro')"
                    />
                </a>
                <NinjaButton v-if="$route.name === 'design_studio'" :btnText="$t('Save')" :disabled="is_form_saving"
                    :loading="is_form_saving" @click="saveDesign" />
            </div>

        </div>

        <div :class="[is_form_saving ? 'disabled' : '']" :disabled="is_form_saving">
            <div class="bg-white !-mx-5 py-4 px-4 border-b border-[#E1E4EA] overflow-x-auto whitespace-nowrap">
                <router-link v-for="tableTab in table_tabs" :key="tableTab.route"
                :to="{ name: tableTab.route, params: { table_id: tableId } }"
                exact-active-class="focus:shadow-none outline:none border-b-2 py-4 border-[#335cff] text-[#0e121b]"
                class="px-2 mx-4 py-3 focus:shadow-none font-[500] text-[#525866]">
                <span class="py-3"></span>
                {{ tableTab.title }}
                </router-link>
            </div>
            <router-view v-if="config" :config="config" :getColumnSettings="getSettings"></router-view>
        </div>

        <el-dialog
            class="ninja_create-table-modal"
            :title="$t('Update Table Info')"
            v-model="editTableModalShow"
            top="50px"
            :append-to-body="true"
        >
            <edit_table
                v-if="editTableModalShow"
                :table="table"
                @modal_close="editTableModalShow = !editTableModalShow"
            />
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
import NinjaButton from "../../@ui-utils/NinjaButton.vue";
import tableConfigStore from '../../store/tableConfigStore';
import GetPro from "../Tools/GetPro.vue";

export default {
    name: 'table_home',
    components: {
        GetPro,
        NinjaButton,
        'edit_table': EditTable
    },
    data() {
        return {
            bus: useEventBus(),
            table_tabs: [],
            tableSettings: tableConfigStore.state.config ? tableConfigStore.state.config.settings : {},
            is_data_saving: false,
            is_form_saving: false,
            tableId: this.$route.params.table_id,
            table: {},
            doingAjax: false,
            doingAjaxTest: false,
            user_tab: this.$route.query.user_tab,
            editTableModalShow: false,
            preview_url: '#',
            has_pro: window.ninja_table_admin.hasPro,
            store: tableConfigStore
        }
    },
    computed: {
        config() {
            return this.store.state.config;
        }
    },
    methods: {
        assetUrl,
        updateTableColumns(callback) {
            let tableId = this.tableId;

            let data = {
                table_id: this.tableId,
                columns: this.store.state.config.columns // Get from store
            }

            this.$post('settings/' + tableId, data)
                .then((res) => {
                    // Update the store with any returned data if needed
                    this.store.setConfig(res.data || res);

                    this.$message({
                        showClose: true,
                        message: res.message,
                        type: 'success'
                    });

                    if (callback) callback(res)
                })
        },
        getSettings() {
            let tableId = this.tableId;
            this.store.setTableId(tableId); // Set tableId in the store

            this.$get('settings/' + tableId)
                .then(response => {
                    if (Object.prototype.toString.call(response.columns) == '[object Object]') {
                        response.columns = toArray(response.columns);
                    }

                    // Set the data in the store
                    this.store.setConfig(response);

                    // You can still set local references if needed
                    this.table = response.table;
                    this.preview_url = response.preview_url;
                })
                .catch((error) => {
                    this.$message.error(error.responseJSON.data.message);
                    if (error.responseJSON.data.route) {
                        this.$router.push({ name: error.responseJSON.data.route });
                    }
                })
        },
        goToTab(key) {
            this.user_tab = key;
            this.$router.push({
                name: 'custom_tab',
                params: { table_id: this.tableId },
                query: { user_tab: key }
            });
        },
        saveDesign() {
            if (this.is_form_saving) return; // Prevent double-clicks

            this.is_form_saving = true;

            // Create a timeout promise for error handling
            const timeoutPromise = new Promise((_, reject) => {
                setTimeout(() => reject(new Error('Save operation timed out')), 10000);
            });

            // Emit event and wait for response
            this.bus.emit('saveTableDesign');

            // Reset the form state after 2 seconds if no response 
            // (This is a fallback in case the event doesn't complete the operation)
            setTimeout(() => {
                this.is_form_saving = false;
            }, 2000);
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

        this.bus.on('tableDoingAjax', (value) => {
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
