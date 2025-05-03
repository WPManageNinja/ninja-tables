<template>
    <div class="nt-woo-nav">

        <el-tabs type="border-card">
            <el-tab-pane label="Appearance" class="nt-appearance">
                <el-checkbox :true-label="'yes'" :false-label="'no'" v-model="appearance.show_cart_before_table">Show Cart Details Before Table</el-checkbox>
                <br />
                <el-checkbox :true-label="'yes'" :false-label="'no'" v-model="appearance.show_cart_after_table">Show Cart Details After Table</el-checkbox>
                <br />
                <el-checkbox :true-label="'yes'" :false-label="'no'" v-model="appearance.show_cart_button">Show Go to Cart Button</el-checkbox>
                <br />
                <el-checkbox :true-label="'yes'" :false-label="'no'" v-model="appearance.show_checkout_button">Show Checkout Button</el-checkbox>
                <br />
                <div class="nt-form-group">
                    <label for="cartBtnText">
                        Cart Text
                    </label>

                    <input type="text" id="cartBtnText" placeholder="Enter cart button text" v-model="appearance.cartBtnText">
                </div>

                <div class="nt-form-group">
                    <label for="checkoutBtnText">
                        Checkout Text
                    </label>

                    <input type="text" id="checkoutBtnText" placeholder="Enter checkout button text" v-model="appearance.checkoutBtnText">
                </div>

                <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
            </el-tab-pane>
            <el-tab-pane label="Query Settings">
                <woo-nav
                    :query_selections="config.table.query_selections"
                    :query_conditions="config.table.query_conditions"
                />
                <br />
                <el-button @click="saveSettings()" type="primary" size="small">Save Settings</el-button>
            </el-tab-pane>
            <el-tab-pane label="Add New Column">
                <columns-editor
                    :model="model"
                    :columns="config.columns"
                    :hasPro="true"
                    :settings="config.settings"
                    :hideCancel="true"
                    dataSourceType="wp_woo"
                    @add="addNewColumn()"
                />
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script type="text/babel">
    import WooNav from './WooNav';
    import ColumnsEditor from '../Table/ColumnEditor/ColumnsEditor';

    export default {
        name: 'woo_nav_edit',
        props: ['config', 'model'],
        components: {
            WooNav,
            ColumnsEditor
        },
        data() {
            return {
                query_terms: [],
                appearance: this.config.table.appearance_settings || {},
                loading: false
            }
        },
        methods: {
            saveSettings() {
                this.saving = true;
                this.$post({
                    action: 'ninja_table_save_query_settings_woo_table',
                    table_id: this.config.table.ID,
                    query_selections: this.config.table.query_selections,
                    query_conditions: this.config.table.query_conditions,
                    appearance_settings: this.config.table.appearance_settings
                })
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: 'success'
                        });
                        this.$emit('reloadData', 1);
                    })
                    .fail(error => {

                    })
                    .always(() => {
                        this.saving = false;
                    });
            },
            addNewColumn() {
                this.$emit('add');
            }
        },
        mounted() {

        }
    }
</script>

<style lang="scss">
    .nt-woo-nav {
        .nt-appearance {
            label.el-checkbox {
                display: block;
            }
        }

        .nt-form-group {
            margin-bottom: 15px;

            label {
                margin-right: 15px;
            }
        }
    }
</style>
