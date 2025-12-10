<template>
    <div class="nt-woo-nav">

        <el-collapse class="nt_accordion_content_white">
            <el-collapse-item name="1">
                <template #title>
                    <p> {{ $t('You may update the query settings here.') }}</p>
                </template>

                <div class="flex rounded-[8px] bg-[#F5F7FA] w-fit p-2 my-3 gap-3 items-center h-[36px]">
                    <div @click="activeTab = 'appearance'" :class="`px-5 h-[26px] cursor-pointer ${activeTab==='appearance' ? 'bg-white rounded-[8px] shadow-md shadow-gray-300 text-[#0E121B]' : 'text-[#99A0AE]'}`">{{$t('Appearance')}}</div>
                    <div @click="activeTab = 'query_settings'" :class="`px-5 h-[26px] cursor-pointer ${activeTab==='query_settings' ? 'bg-white rounded-[8px] shadow-md shadow-gray-300 text-[#0E121B]': 'text-[#99A0AE]'}`">{{$t('Query Settings')}}</div>
                    <div @click="activeTab ='add_new_column'" :class="`px-5 h-[26px] cursor-pointer ${activeTab==='add_new_column' ? 'bg-white rounded-[8px] shadow-md shadow-gray-300 text-[#0E121B]':'text-[#99A0AE]'}`">{{$t('Add New Column')}}</div>
                </div>

                <div v-if="activeTab === 'appearance'" class="flex flex-col">
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <div class="flex flex-col">
                            <label class="nt-form-label">
                                {{ $t("Filled Star Color") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>{{$t('Filled Star Color')}}</h3>
                                        <p>{{$t('Set the color for filled/active rating stars')}}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                </el-tooltip>
                            </label>
                            <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                <ColorPicker v-model="appearance.filled_star_color" />
                            </div>
                        </div>

                        <div class="flex flex-col">
                            <label class="nt-form-label">
                                {{ $t("Empty Star Color") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>{{$t('Empty Star Color')}}</h3>
                                        <p>{{$t('Set the color for empty/inactive rating stars')}}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                </el-tooltip>
                            </label>
                            <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                <ColorPicker v-model="appearance.empty_star_color" />
                            </div>
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
                    <WooReviewNav
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
    import WooReviewNav from "./WooReviewNav.vue";
    import ColorPicker from "../../@ui-utils/ColorPicker.vue";

    export default {
        name: 'WooReviewsNavEdit',
        props: ['config', 'model'],
        components: {
            ColorPicker,
            WooReviewNav,
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
                    action: 'ninja_table_save_query_settings_woo_reviews_table',
                    table_id: this.config.table.ID,
                    query_selections: this.config.table.query_selections,
                    query_conditions: this.config.table.query_conditions,
                    appearance_settings: this.appearance
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
