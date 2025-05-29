<template>
    <el-container class="ninja-add-table">
        <el-aside v-if="!table.ID" class="ninja-tables-aside">
            <el-menu
                 :default-active="activeTabName"
                 background-color="#FFFFFF"
                 text-color="#565865"
                 active-text-color="#335CFF"
                 min-width="200px"
            >
                <el-menu-item @click="activeTabName = 'default'" index='default'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('Default')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'drag_and_drop'" index='drag_and_drop'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('Drag & Drop Table')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'fluent_form'" index='fluent_form'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('Connect Fluent Forms')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'wp_posts'" index='wp_posts'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('WP Posts')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

                <el-menu-item v-if="has_woo" @click="activeTabName = 'woo_table'" index='woo_table'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('WooCommerce Table')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'google_spread_sheet'" index='google_spread_sheet'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('Connect Google Sheets')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'csv'" index='csv'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('Connect External CSV')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'raw_sql'" index='raw_sql'>
                    <div class="tab-label">
                        <div class="active-bar"></div>
                        <div class="tab-title">
                            <span>{{$t('Custom SQL Query')}}</span>
                            <img :src="assetUrl('icons/active-arrow.svg')"/>
                        </div>
                    </div>
                </el-menu-item>

            </el-menu>
        </el-aside>

        <el-main class="ninja-tables-main">
            <div v-if="activeTabName === 'default'" class="w-full">
                <div class="ninja_modal-body">
                    <template v-if="!table.ID">
                        <h3 class="nt-modal-title">Manually Create a Table</h3>
                        <p class="nt-modal-description">
                            Manually create your table columns and rows to get complete
                            control over your data with tons of customizations.
                        </p>
                    </template>

                    <div class="my-[30px]">
                        <div class="nt-form-group">
                            <label class="nt-form-label">{{ $t('Table Title') }}<span class="nt-required ml-[4px]">*</span></label>
                            <NinjaInput
                                v-model="table.post_title"
                                :placeholder="$t('Enter a title to identify your table')"
                            />
                        </div>

                        <div class="nt-form-group">
                            <label class="nt-form-label">{{ $t('Table Description') }}</label>
                            <WPEditor v-model="table.post_content" />
                        </div>
                    </div>
                </div>

                <div class="nt-modal-footer">
                    <NinjaButton type="secondary" @click="closeModal" :btnText="$t('Cancel')" />
                    <NinjaButton v-if="table.ID" :loading="btnLoading" @click="addTable" :btnText="$t('Update')"/>
                    <NinjaButton v-else @click="addTable" :loading="btnLoading" :btnText="$t('Add')" />
                </div>
            </div>

            <div v-else-if="activeTabName === 'drag_and_drop'">
              <RightSideBar :initialData="initialData" />
            </div>

            <div v-else-if="activeTabName === 'google_spread_sheet'">
                <ExternalDataSource
                    type="google-csv"
                    :tableCreated="fireTableCreated"
                    :has-pro="hasPro"
                    :activated_features="activated_features"
                    @modalClose="closeModal"
                />
            </div>

            <div v-else-if="activeTabName === 'csv'" class="w-full">
                <ExternalDataSource
                    type="csv"
                    :tableCreated="fireTableCreated"
                    :has-pro="hasPro"
                    :activated_features="activated_features"
                    @modalClose="closeModal"
                />
            </div>

            <template v-else-if="activeTabName === 'fluent_form'">
                <FluentForm
                    :tableCreated="fireTableCreated"
                    @modalClose="closeModal"
                />
            </template>

            <template v-else-if="activeTabName === 'wp_posts'">
                <WPPosts
                    :tableCreated="fireTableCreated"
                    :activated_features="activated_features"
                    @modalClose="closeModal"
                />
            </template>

            <template v-else-if="activeTabName === 'woo_table'">
                <WooProducts
                    v-if="activated_features.woocommerce_table"
                    :tableCreated="fireTableCreated"
                    @modalClose="closeModal"
                />
                <div v-else-if="has_woo && hasPro">
                    <p>Please update to latest version of <b>Ninja Tables Pro</b> to use WooCommerce integration</p>
                </div>
                <div v-else-if="has_woo && !hasPro" class="ninja_no_woo">
<!--                    <h3>Upgrade to pro for using WooCommerce Integration</h3>-->
                    <PremiumNotice title="WooCommerce Table">
                        <template #default>
                            <p class="text-[14px] font-[400] text-[#525866]">This is a Premium feature. Create customizable, smart, and organized
                                <a href="https://ninjatables.com/docs/woocommerce-integration/" class="nt-link" target="_blank" >
                                    WooCommerce product tables
                                </a>
                                with Ninja Tables Pro and get more sales.</p>
                        </template>
                    </PremiumNotice>
                </div>
            </template>

            <template v-else-if="activeTabName === 'raw_sql'">
                <RawSqlForm
                    :has_sql_permission="has_sql_permission"
                    :tableCreated="fireTableCreated"
                    :activated_features="activated_features"
                    @modalClose="closeModal"
                />
            </template>
        </el-main>
    </el-container>

</template>

<script type="text/babel">
    import WPEditor from '../../common/_wp_editor';
    import WPPosts from './DataProviders/WPPosts';
    import WooProducts from './DataProviders/WooProducts';
    import FluentForm from './DataProviders/FluentForm.vue';
    import ExternalDataSource from './DataProviders/ExternalDataSource.vue';
    import ImportTable from './includes/ImportTable';
    import RawSqlForm from './DataProviders/RawSqlForm'
    import PremiumNotice from './includes/PremiumNotice';
    import RightSideBar from "./TableBuilder/Sidebar/RightSideBar.vue";
    import {assetUrl} from "../utils/ninjatablesadmin";
    import NinjaInput from "../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../@ui-utils/NinjaButton.vue";

    export default {
        name: 'add_table',
        components: {
            NinjaInput,
            NinjaButton,
            RightSideBar,
            WPEditor,
            WPPosts,
            WooProducts,
            FluentForm,
            ExternalDataSource,
            ImportTable,
            RawSqlForm,
            PremiumNotice
        },
        props: {
            table: {
                type: Object,
                default() {
                    return {
                        ID: null,
                        post_title: '',
                        post_content: '',
                    }
                }
            },
            hasPro: {
                required: true
            }
        },
        data() {
            return {
                activeTabName: 'default',
                btnLoading: false,
                activated_features: window.ninja_table_admin.activated_features,
                has_sql_permission: window.ninja_table_admin.sql_permission == 'yes',
                editorOption: {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike', 'link'],         // toggled buttons
                            ['blockquote', 'code-block'],
                            [{'header': 1}, {'header': 2}],               // custom button values
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            [{'script': 'sub'}, {'script': 'super'}],      // superscript/subscript
                            [{'align': []}],
                            [{'direction': 'rtl'}]
                        ]
                    }
                },
                has_woo: !!window.ninja_table_admin.has_woocommerce,
                initialData : {},
            }
        },
        methods: {
            assetUrl,
            createDragAndDropTable() {
              this.$get("table-builder")
                  .then(response => {
                    this.initialData = response
                  })
                  .catch(error => {
                    this.$message({
                      showClose: true,
                      message: this.$t('Something went wrong, please try again.'),
                      type: 'warning'
                    });
                  });
            },
            handleTabClick(tab, event) {
                setTimeout(() => {
                    jQuery(tab.$el).find('input:first').focus();
                }, 0);
            },
            addTable: function () {
              this.btnLoading = true;

              this.$post("tables", {
                post_title: this.table.post_title,
                post_content: this.table.post_content,
                tableId: this.table.ID
              })
                  .then(response => {
                      this.$message({
                        showClose: true,
                        message: response.message,
                        type: 'success'
                      });

                      this.$emit('addedTable');

                      if (this.table.ID) {
                        this.closeModal();
                      } else {
                        // Check if response has table_id directly or in data property
                        const tableId = response.table_id || (response.data && response.data.table_id);
                        if (tableId) {
                          this.fireTableCreated(tableId);
                        } else {
                          console.error('No table_id found in response:', response);
                        }
                      }
                      this.btnLoading = false;
                  })
                  .catch(error => {
                      if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
                        this.$message({
                          showClose: true,
                          message: error.responseJSON.data.message,
                          type: 'error'
                        });
                      } else if (error.responseText) {
                        this.$message({
                          showClose: true,
                          message: error.responseText,
                          type: 'error'
                        });
                      } else {
                        this.$message({
                          showClose: true,
                          message: 'An error occurred while creating the table',
                          type: 'error'
                        });
                      }
                      this.btnLoading = false;
                  });
            },
            closeModal() {
                this.$emit('modal_close');
            },
            onEditorChange({editor, html, text}) {
                this.table.post_content = html
            },
            fireTableCreated(table_id) {
                this.$emit('table_inserted', table_id);
            },
        },
        mounted() {
            this.createDragAndDropTable();
        }
    }
</script>

