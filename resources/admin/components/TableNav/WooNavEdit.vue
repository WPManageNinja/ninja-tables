<template>
    <div class="nt-woo-nav">

        <el-collapse class="nt_accordion_content_white">
            <el-collapse-item name="1">
                <template #title>
                    <p> {{ $t('You may update the query settings here.') }}</p>
                </template>

                <div class="flex rounded-[8px] bg-[#F5F7FA] w-fit p-2 my-3 gap-3">
                    <div @click="activeTab = 'appearance'" :class="`px-5 py-1 cursor-pointer ${activeTab==='appearance' ? 'bg-white rounded-[8px] shadow shadow-md shadow-gray-300 text-[#0E121B]' : 'text-[#99A0AE]'}`">{{$t('Appearance')}}</div>
                    <div @click="activeTab = 'query_settings'" :class="`px-5 py-1 cursor-pointer ${activeTab==='query_settings' ? 'bg-white rounded-[8px] shadow shadow-md shadow-gray-300 text-[#0E121B]': 'text-[#99A0AE]'}`">{{$t('Query Settings')}}</div>
                    <div @click="activeTab ='add_new_column'" :class="`px-5 py-1 cursor-pointer ${activeTab==='add_new_column' ? 'bg-white rounded-[8px] shadow shadow-md shadow-gray-300 text-[#0E121B]':'text-[#99A0AE]'}`">{{$t('Add New Column')}}</div>
                </div>

                <div v-if="activeTab === 'appearance'">
                    <div class="flex items-center gap-4  mt-6">
                        <div>
                            <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="appearance.show_cart_before_table">{{$t('Show Cart Details Before Table')}}</el-checkbox>
                            <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="appearance.show_cart_after_table">{{$t('Show Cart Details After Table')}}</el-checkbox>
                        </div>

                        <div>
                            <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="appearance.show_cart_button">{{$t('Show Go to Cart Button')}}</el-checkbox>
                            <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="appearance.show_checkout_button">{{$t('Show Checkout Button')}}</el-checkbox>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <div class="nt-form-group flex flex-col">
                            <label for="cartBtnText" class="nt-form-label">
                                Cart Text
                            </label>

                            <NinjaInput id="cartBtnText" :placeholder="$t('Enter cart button text')" v-model="appearance.cartBtnText" />
                        </div>

                        <div class="nt-form-group flex flex-col">
                            <label for="checkoutBtnText" class="nt-form-label">
                                Checkout Text
                            </label>

                            <NinjaInput id="checkoutBtnText" :placeholder="$t('Enter checkout button text')" v-model="appearance.checkoutBtnText" />
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <NinjaButton
                            @click="saveSettings"
                            :btn-text="$t('Save Settings')"
                        />
                    </div>
                </div>

                <div v-else-if="activeTab === 'query_settings'">
                    <woo-nav
                        :query_selections="config.table.query_selections"
                        :query_conditions="config.table.query_conditions"
                    />

                    <div class="flex justify-end mt-6">
                        <NinjaButton @click="saveSettings()" :btn-text="$t('Save Settings')"/>
                    </div>
                </div>

                <div v-else>
                    <columns-editor
                        :model="model"
                        :columns="config.columns"
                        :hasPro="true"
                        :settings="config.settings"
                        :hideCancel="true"
                        dataSourceType="wp_woo"
                        @add="addNewColumn()"
                    />
                </div>

            </el-collapse-item>
        </el-collapse>
    </div>
</template>

<script type="text/babel">
    import WooNav from './WooNav';
    import ColumnsEditor from '../Table/ColumnEditor/ColumnsEditor';
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";

    export default {
        name: 'woo_nav_edit',
        props: ['config', 'model'],
        components: {
            NinjaInput,
            NinjaButton,
            WooNav,
            ColumnsEditor
        },
        data() {
            return {
                query_terms: [],
                appearance: this.config.table.appearance_settings || {},
                loading: false,
                activeTab: 'appearance'
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
