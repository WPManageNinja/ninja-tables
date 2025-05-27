<template>
    <div>
        <div class="table-column-settings mx-4">
            <el-container class="ninja-table-aside">
                <el-aside class="!w-[70px] lg:!w-[300px] !p-[5px]">
                    <el-menu background-color="white"
                             :default-active="active_menu"
                             text-color="#525866"
                             active-text-color="#ffd04b"
                    >
                        <el-menu-item  @click="active_menu = 'columns'" index="columns">
                            <el-tooltip content="Columns" placement="right">
                                <img class="lg:hidden" :src="assetUrl('/icons/credit-card.svg')"/>
                            </el-tooltip>
                            <img class="hidden lg:block" :src="assetUrl('/icons/credit-card.svg')"/>
                            <span class="hidden lg:block">Columns</span>
                        </el-menu-item>

                        <el-menu-item  @click="active_menu = 'rendering_settings'" index="rendering_settings">
                            <el-tooltip content="Rendering Settings" placement="right">
                                <img class="lg:hidden" :src="assetUrl('/icons/credit-card.svg')"/>
                            </el-tooltip>
                            <img class="hidden lg:block" :src="assetUrl('/icons/credit-card.svg')"/>
                            <span class="hidden lg:block">Rendering Settings</span>
                        </el-menu-item>

                        <el-menu-item  @click="active_menu = 'custom_filters'" index="custom_filters">
                            <el-tooltip content="Custom Filters" placement="right">
                                <img class="lg:hidden" :src="assetUrl('/icons/customize.svg')"/>
                            </el-tooltip>
                            <img class="hidden lg:block" :src="assetUrl('/icons/customize.svg')"/>
                            <span class="hidden lg:block">Custom Filters</span>
                            <img v-if="!hasPro" class="h-4 w-4 ml-1 !grayscale-0" :src="assetUrl('icons/get-pro.svg')" alt="">
                        </el-menu-item>

                        <el-menu-item  @click="active_menu = 'button_settings'" index="button_settings">
                            <el-tooltip content="Buttons (CSV/Print)" placement="right">
                                <img class="lg:hidden" :src="assetUrl('/icons/search-area.svg')"/>
                            </el-tooltip>
                            <img class="hidden lg:block" :src="assetUrl('/icons/search-area.svg')"/>
                            <span class="hidden lg:block">Buttons (CSV/Print)</span>
                            <img v-if="!hasPro" class="h-4 w-4 ml-1 !grayscale-0" :src="assetUrl('icons/get-pro.svg')" alt="">
                        </el-menu-item>

                        <el-menu-item  @click="active_menu = 'language_settings'" index="language_settings">
                            <el-tooltip content="Language Settings" placement="right">
                                <img class="lg:hidden" :src="assetUrl('/icons/language-square.svg')"/>
                            </el-tooltip>
                            <img class="hidden lg:block" :src="assetUrl('/icons/language-square.svg')"/>
                            <span class="hidden lg:block">Language Settings</span>
                        </el-menu-item>
                    </el-menu>
                </el-aside>
                <el-main class="ml-5 lg:ml-10">
                    <template v-if="active_menu == 'columns'">
                        <h2 class="text-[18px] font-[600] text-[#0E121B]">Table Column Settings</h2>
                  
                        <div class="ninja_content">
                            <div class="section_widget">
                                <div class="heading">
                                    <h3 v-if="addColumnStatus || !columns.length" class="title">{{ $t('Add Table Column') }}</h3>
                                    <h3 v-else class="title">{{ $t('Available Columns') }}</h3>
                                    <div v-show="!addColumnStatus" class="inline_action" v-if="addable">
                                        <NinjaButton 
                                        v-show="columns.length" 
                                        @click="addColumnStatus = !addColumnStatus" 
                                        :btnText="$t('Add Column')"
                                        type="secondary"
                                        :icon="assetUrl('/icons/add-01.svg')"
                                        />
                                    </div>
                                </div>
                                <div v-if="addColumnStatus || !columns.length" class="column border border-[#ebeef5] rounded-[8px] mb-4">
                                        <div class="add_column_wrapper">
                                            <ColumnsEditor
                                                :columns="columns"
                                                :dataSourceType="config.table.dataSourceType"
                                                :model="new_column"
                                                :settings="config.settings"
                                                :has-pro="has_pro"
                                                @add="addNewColumn()"
                                                @cancel="addColumnStatus = !addColumnStatus"
                                                :hideCancel="false"
                                            />
                                        </div>
                                    </div>
                                <div class="widget_body border border-[#ebeef5]">
                                    <draggable 
                                        @end="storeSettings" 
                                        v-model="columns" 
                                        handle=".handle" 
                                        animation="150"
                                        item-key="key"
                                    >
                                        <template #item="{element: column, index}">
                                            <div class="column drawer" :key="column.key">
                                                <div class="header flex justify-between items-center" :class="{'border-b border-[#ebeef5]':currentIndex.includes(index)}">
                                                    <div class="flex items-center gap-2">
                                                        <!-- <span class="dashicons dashicons-editor-justify handle" /> -->
                                                         <img class="cursor-move handle" :src="assetUrl('/icons/drag-drop.svg')"/>
                                                        <span @click="openDrawer(index)" class="text-[14px]">{{ column.name || column.key }}</span>
                                                    </div>
                                                    <span @click="openDrawer(index)" class="cursor-pointer">
                                                        <!-- <img v-if="currentIndex.includes(index)" :src="assetUrl('/icons/chevron-up.svg')"/>
                                                        <img v-else :src="assetUrl('/icons/chevron-down.svg')"/> -->
                                                        <img :src="assetUrl('/icons/edit-2.svg')"/>
                                                    </span>
                                                </div>
                                                <div class="drawer_body" :class="'drawer_body_'+index">
                                                    <columns-editor
                                                        :columns="columns"
                                                        :dataSourceType="config.table.dataSourceType"
                                                        :model="column"
                                                        :has-pro="has_pro"
                                                        :settings="config.settings"
                                                        :updating="true"
                                                        @delete="deleteColumn(index)"
                                                        @store="storeSettings()"
                                                    />
                                                </div>
                                            </div>
                                        </template>
                                    </draggable>
                                </div>
                            </div>
                            <div class="proms my-4">
                                <div class="nt-instruction">
                                    <p class="text-[14px]">Need help to configure the columns and responsive breakdowns, Please check tutorial with
                                        video <a class="nt-link"
                                                href="https://ninjatables.com/docs/column-responsive-breakpoints/"
                                                target="_blank">video here</a></p>
                                </div>
                                <div v-if="!is_fluent_installed" class="nt-instruction">
                                    <p class="text-[14px]">Have you checked out FluentForm yet? We have developed a powerful Drag & Drop WordPress Form
                                        Builder plugin with some amazing Premium features <a :href="fluent_url">Download from
                                            WordPress.org</a></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="active_menu == 'rendering_settings'">
                        <NinjaRenderingSettings
                                @storeSettings="storeSettings"
                                :tableSettings="tableSettings"
                                :config="config"
                        />
                    </template>

                    <template v-else-if="active_menu == 'language_settings'">
                        <NinjaLanguageSettings @storeSettings="storeSettings" :tableSettings="tableSettings"></NinjaLanguageSettings>
                    </template>

                    <template v-else-if="active_menu == 'custom_filters'">
                        <NinjaCustomFilters :columns="columns" :table_id="tableId"></NinjaCustomFilters>
                    </template>

                    <template v-else-if="active_menu = 'button_settings'">
                        <NinjaButtonSettings :table_id="tableId" />
                    </template>
                </el-main>
            </el-container>
        </div>
    </div>
</template>
<script type="text/babel">
    import draggable from 'vuedraggable'
    import findIndex from 'lodash/findIndex';
    import get from 'lodash/get'
    import size from 'lodash/size'
    import snakeCase from 'lodash/snakeCase'
    import ColumnsEditor from './ColumnsEditor.vue';
    import NinjaCustomFilters from '../TableFilters/CustomFilter.vue';
    import NinjaLanguageSettings from '../Configarations/_LanguageSettings.vue'
    import NinjaRenderingSettings from '../Configarations/_RenderingSettings.vue'
    import NinjaButtonSettings from '../Configarations/_buttons.vue'
    import { useEventBus } from '../../../eventBus';

    import { tableLibs } from '../../../data/data'
    import { assetUrl } from '../../../utils/ninjatablesadmin';
    import { NinjaButton } from '../../../@ui-utils';

    export default {
        name: 'TableConfiguration',
        components: {
            draggable,
            ColumnsEditor,
            NinjaCustomFilters,
            NinjaLanguageSettings,
            NinjaRenderingSettings,
            NinjaButtonSettings,
            NinjaButton
        },
        props: ['config'],
        data() {
            return {
                bus : useEventBus(),
                currentIndex: [],
                hasPro: !!window.ninja_table_admin.hasPro,
                active_menu: 'columns',
                table_color_primary: '#000',
                table_color_secondary: '#fff',
                tableId: this.$route.params.table_id,
                tableLibs: tableLibs(),
                doingAjax: false,
                addColumnStatus: false,
                new_column: false,
                breakPointsOptions: {
                    'xs': this.$t('Initial Hidden Mobile'),
                    'xs sm': this.$t('Initial Hidden Mobile and Tab'),
                    'xs sm md lg': this.$t('Initial Hidden Mobile, Tab and Regular Computers'),
                    '': this.$t('Always show in all devices'),
                    'hidden': this.$t('Totally hidden on all devices'),
                },
                dataTypesOptions: {
                    'text': this.$t('Single Line Text Field'),
                    'textarea': this.$t('Text Area'),
                    'html': this.$t('HTML Field'),
                    'number': this.$t('Numeric Value'),
                    'date': this.$t('Date Field'),
                    'selection': this.$t('Select Field')
                },
                attributeModel: {
                    name: null,
                    key: null,
                    breakpoints: ''
                },
                columns: this.config.columns,
                tableSettings: this.config.settings,
                is_fluent_installed: window.ninja_table_admin.isInstalled,
                fluent_url: window.ninja_table_admin.fluentform_url,
                has_pro: !!window.ninja_table_admin.hasPro,
                hasSortable: !!window.ninja_table_admin.hasSortable,
                addVisible: false,
                sortableUpgradeNotice: false
            }
        },
        watch: {
            'new_column.name': function () {
                this.new_column.key = snakeCase(this.new_column.name)
            },
        },
        methods: {
            assetUrl,
            storeSettings() {
                this.bus.emit('tableDoingAjax', true);

                let data = {
                    table_id: this.tableId,
                    columns: this.columns,
                    table_settings: this.tableSettings
                };
                this.$post('settings/'+this.tableId, data)
                    .then((res) => {
                        this.$message({
                            showClose: true,
                            message: res.message,
                            type: 'success'
                        });
                        this.config.columns = this.columns;
                      this.bus.emit('tableDoingAjax', false);
                    })
                    .catch((error) => {
                      this.bus.emit('tableDoingAjax', false);
                    })

            },
            openDrawer(index) {
                jQuery('.drawer_body_' + index).slideToggle();
                if(this.currentIndex.includes(index)){
                    this.currentIndex = this.currentIndex.filter(i => i !== index);
                } else {
                    this.currentIndex.push(index);
                }
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
                    this.columns.push(this.new_column);
                    this.setNewColumn();
                    this.addColumnStatus = false;
                    this.storeSettings();
                }
            },
            deleteColumn(index) {
                this.config.columns.splice(index, 1);
                this.storeSettings();
            },
            showProAd(title) {
                this.addVisible = true;
                this.bus.emit('show_pro_popup', 1);
            },
            size,
            get,
            initManualSorting() {
                let promise = new Promise((resolve, reject) => {
                    this.bus.emit('initManualSorting', {
                        table_id: this.tableId,
                        noData: true
                    }, resolve, reject);
                })
            },
            headerColorsClick() {
                if (!this.has_pro) {
                    this.showProAd();
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
                if(this.dataSourceType() === 'wp-posts') {
                    newColumn.source_type = 'custom';
                }
                this.new_column = newColumn;
            },
            dataSourceType() {
                let dataSource = this.config.table.dataSourceType || 'Default';
                dataSource = dataSource.indexOf('google') > -1 ? 'Google SpreadSheet' : dataSource;
                return dataSource;
            }
        },
        computed: {
            addable() {
                return ['default', 'wp-posts'].indexOf(this.config.table.dataSourceType) != -1;
            }
        },
        mounted() {
            this.setNewColumn();
        }
    }
</script>

<style lang="scss">
    .table-column-settings {
        margin-top: 24px;

        .el-menu {
            border-right: initial;
        }
    }
</style>
