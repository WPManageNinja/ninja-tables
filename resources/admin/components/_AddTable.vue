<template>
    <el-container class="ninja-add-table">
        <el-aside v-if="!table.ID" class="ninja-tables-aside">
            <el-menu
                 :collapse="isCollapse"
                 :default-active="activeTabName"
                 background-color="#FFFFFF"
                 text-color="#565865"
                 active-text-color="#335CFF"
            >
                <el-menu-item @click="activeTabName = 'default'" index='default'>
                    <span>Default</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'drag_and_drop'" index='drag_and_drop'>
                  <span>Drag & Drop Table</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'fluent_form'" index='fluent_form'>
                    <span>Connect Fluent Forms</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'wp_posts'" index='wp_posts'>
                    <span>WP Posts</span>
                </el-menu-item>

                <el-menu-item v-if="has_woo" @click="activeTabName = 'woo_table'" index='woo_table'>
                    <span>WooCommerce Table</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'google_spread_sheet'" index='google_spread_sheet'>
                    <span>Connect Google Sheets</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'csv'" index='csv'>
                    <span>Connect External CSV</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'raw_sql'" index='raw_sql'>
                    <span>Custom SQL Query</span>
                </el-menu-item>

            </el-menu>
        </el-aside>

        <el-main class="ninja-tables-main">
            <template v-if="activeTabName == 'default'">
                <div class="ninja_modal-body">
                    <template v-if="!table.ID">
                        <h3 class="nt-modal-title">Manually Create a Table</h3>
                        <p class="nt-modal-subtitle">
                            Manually create your table columns and rows to get complete
                            control over your data with tons of customizations.
                        </p>
                    </template>

                    <div class="my-[30px]">
                        <div class="nt-form-group">
                            <label class="nt-form-label">{{ $t('Table Title') }}</label>
                            <NinjaInput
                                v-model="table.post_title"
                                :placeholder="$t('Enter a title to identify your table')"
                            />
                        </div>

                        <div class="nt-form-group">
                            <label class="nt-form-label">{{ $t('Table Description') }}</label>
                            <wp_editor v-model="table.post_content"></wp_editor>
                        </div>
                    </div>
                </div>

                <div class="nt-modal-footer">
                    <NinjaButton type="secondary" @click="closeModal" :btnText="$t('Cancel')" />
                    <NinjaButton v-if="table.ID" @click="addTable" :btnText="$t('Update')"/>
                    <NinjaButton v-else @click="addTable" :btnText="$t('Add')" />
                </div>
            </template>
            <template v-else-if="activeTabName === 'drag_and_drop'">
              <right-side-bar :initialData="initialData"></right-side-bar>
            </template>

            <template v-else-if="activeTabName == 'google_spread_sheet'">
                <external-data-source
                        type="google-csv"
                        :tableCreated="fireTableCreated"
                        :has-pro="hasPro"
                        :activated_features="activated_features"
                />
            </template>

            <template v-else-if="activeTabName == 'csv'">
                <external-data-source
                        type="csv"
                        :tableCreated="fireTableCreated"
                        :has-pro="hasPro"
                        :activated_features="activated_features"
                />
            </template>

            <template v-else-if="activeTabName == 'fluent_form'">
                <fluent-form-data-source
                        :tableCreated="fireTableCreated"
                        @modalClose="closeModal"
                />
            </template>

            <template v-else-if="activeTabName == 'wp_posts'">
                <wp-posts-data-source
                        :tableCreated="fireTableCreated"
                        :activated_features="activated_features"
                />
            </template>

            <template v-else-if="activeTabName == 'woo_table'">
                <woo-data-source
                    v-if="activated_features.woocommerce_table"
                    :tableCreated="fireTableCreated"
                />
                <div v-else-if="has_woo && hasPro">
                    <p>Please update to latest version of <b>Ninja Tables Pro</b> to use WooCommerce integration</p>
                </div>
                <div v-else-if="has_woo && !hasPro" class="ninja_no_woo">
                    <h3>Upgrade to pro for using WooCommerce Integration</h3>
                    <premium-notice highlight="WooCommerce Integration module where you can create and build table with Woocomerce produsts and increase your conversion rate"/>
                </div>
            </template>

            <template v-else-if="activeTabName == 'raw_sql'">
                <raw-sql-form
                    :has_sql_permission="has_sql_permission"
                    :tableCreated="fireTableCreated"
                    :activated_features="activated_features"
                />
            </template>
        </el-main>
    </el-container>

</template>

<script type="text/babel">
    import wp_editor from '../../common/_wp_editor';
    import WPPosts from './DataProviders/WPPosts';
    import WooProducts from './DataProviders/WooProducts';
    import FluentForm from './DataProviders/FluentForm';
    import ExternalDataSource from './DataProviders/ExternalDataSource';
    import ImportTable from './includes/ImportTable';
    import RawSqlForm from './DataProviders/RawSqlForm'
    import PremiumNotice from './includes/PremiumNotice';
    import RightSideBar from "./TableBuilder/Sidebar/RightSideBar";
    import {assetUrl} from "../utils/ninjatablesadmin";
    import NinjaInput from "../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../@ui-utils/NinjaButton.vue";

    export default {
        name: 'add_table',
        components: {
            NinjaInput,
            NinjaButton,
            RightSideBar,
            wp_editor: wp_editor,
            'wp-posts-data-source': WPPosts,
            'woo-data-source': WooProducts,
            'fluent-form-data-source': FluentForm,
            'external-data-source': ExternalDataSource,
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
                isCollapse: false,
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
            checkScreenSize() {
                if (window.innerWidth < 1000) {
                    this.isCollapse = true;
                } else {
                    this.isCollapse = false;
                }
            }
        },
        mounted() {
            this.checkScreenSize();
            this.createDragAndDropTable();
            jQuery(window).resize(() => {
                this.checkScreenSize();
            });
        }
    }
</script>

