<template>
    <el-container class="ninja-add-table">
        <el-aside v-if="!table.ID" style="background-color: rgb(35, 40, 45);">
            <el-menu :collapse="isCollapse"
                     :default-active="activeTabName"
                     background-color="#23282d"
                     text-color="#eee"
                     active-text-color="#fff"
            >
                <el-menu-item @click="activeTabName = 'default'" index='default'>
                    <i class="el-icon-setting"></i>
                    <span>Default</span>
                </el-menu-item>
                <el-menu-item @click="activeTabName = 'drag_and_drop'" index='drag_and_drop'>
                  <i class="el-icon-s-unfold"></i>
                  <span>Drag & Drop Table</span>
                </el-menu-item>
                <el-menu-item @click="activeTabName = 'import_table'" index="import_table">
                    <i class="el-icon-upload2"></i>
                    <span>Import Table</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'fluent_form'" index='fluent_form'>
                    <img :src="fluentFormIcon" alt="fluent form icon" class="el-icon-fluent-form">
                    <span>Connect Fluent Forms</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'wp_posts'" index='wp_posts'>
                    <i class="el-icon-news"></i> <span>WP Posts</span>
                </el-menu-item>

                <el-menu-item v-if="has_woo" @click="activeTabName = 'woo_table'" index='woo_table'>
                    <img :src="wooIcon" alt="woocomerce icon" class="el-icon-fluent-form">
                    <span>WooCommerce Table</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'google_spread_sheet'" index='google_spread_sheet'>
                    <span class="dashicons dashicons-media-spreadsheet"></span>
                    <span>Connect Google Sheets</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'csv'" index='csv'>
                    <i class="el-icon-document"></i>
                    <span>Connect External CSV</span>
                </el-menu-item>

                <el-menu-item @click="activeTabName = 'raw_sql'" index='raw_sql'>
                    <i class="dashicons dashicons-editor-code"></i>
                    <span>Custom SQL Query</span>
                </el-menu-item>

            </el-menu>
        </el-aside>

        <el-main>
            <template v-if="activeTabName == 'default'">
                <div class="ninja_modal-body">
                    <template v-if="!table.ID">
                        <h3>Manually Create a Table</h3>
                        <p class="ninja_subtitle">
                            Manually create your table columns and rows to get complete
                            control over your data with tons of customizations.
                        </p>
                    </template>

                    <div class="form-group">
                        <label for="name">{{ $t('Table Title') }}</label>
                        <input v-model="table.post_title"
                               type="text" id="name" class="form-control"
                               placeholder="Enter a title to identify your table"
                        >
                    </div>
                    <div class="form-group">
                        <label>{{ $t('Table Description') }}</label>
                        <wp_editor v-model="table.post_content"></wp_editor>
                    </div>
                </div>
                <div class="modal-footer">
                    <el-button type="primary" size="small" @click="addTable">
                        <span v-if="table.ID">{{ $t('Update') }}</span>
                        <span v-else>{{ $t('Add') }}</span>
                        <i v-if="btnLoading" class="fooicon fooicon-spin fooicon-circle-o-notch"></i>
                    </el-button>
                </div>
            </template>
            <template v-else-if="activeTabName === 'drag_and_drop'">
              <right-side-bar :initialData="initialData"></right-side-bar>
            </template>

            <template v-else-if="activeTabName === 'import_table'">
                <import-table></import-table>
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
    import { useEventBus } from '../composables/useEventBus';

    export default {
        name: 'add_table',
        components: {
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
                fluentFormIcon: window.ninja_table_admin.fluent_form_icon,
                wooIcon: window.ninja_table_admin.img_url+'woo-logo.png',
                has_woo: !!window.ninja_table_admin.has_woocommerce,
                initialData : {},
            }
        },
        methods: {
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

                      const { emit } = useEventBus();
                      emit('addedTable');

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

<style lang="scss">
    .ninja-add-table {
        .el-main {
            padding: 0 1px 0 15px;
            min-height: initial;
        }

        .el-menu {
            border-right: initial;
        }

        .el-menu-item {
            .el-icon-fluent-form {
                height: 18px;
            }

            .dashicons {
                width: 24px;
                height: 18px;
                margin-right: 5px;
            }

            &.is-active {
                background-color: #0073aa !important;
            }
        }

        .el-table .cell {
            text-overflow: initial;
        }
    }
</style>
