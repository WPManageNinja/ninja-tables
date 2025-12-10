<template>
    <div class="nt-table-configuration-content">
        <div class="text-[18px] font-[600] text-[#0E121B]">{{
                $t('CSV Export / Print Button Settings for Frontend')
            }}
        </div>
        <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
            {{ $t("You can enable/disable print and csv export settings here") }}
        </div>

        <div v-if="!hasPro" class="nt-instruction w-full overflow-hidden text-center my-6">
            <h3 class="text-[16px] font-[400] text-[#525866] my-4">
                Export CSV and Table Print is pro features. Please purchase <b>"Ninja Tables Pro"</b> to use this feature
            </h3>
            <div class="flex justify-center mb-4">
                <a v-if="!hasPro"
                   href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade"
                   target="_blank">
                    <NinjaButton
                        type="pro"
                        :icon="assetUrl('icons/get-pro.svg')"
                        :btnText="$t('Get Pro')"
                    />
                </a>
            </div>
        </div>

        <div v-else v-loading="fetching">
            <div class="mb-3 text-[16px] font-[500]">{{ $t("Toolbar Button Settings") }}</div>
            <div>
                <el-collapse class="ninja-tables_rendering_accordion mb-5" accordion v-model="activeCollapse">
                    <el-collapse-item name="csv">
                        <template #title>
                            <div>
                                <el-switch
                                    size="small"
                                    v-model="table_buttons.csv.status"
                                    class="mr-2"
                                    active-value="yes"
                                    inactive-value="no"
                                    @click="(e)=>{
                                        activeCollapse = table_buttons.csv.status === 'yes' ? ['csv'] : []
                                        e.stopPropagation();
                                    }"
                                />
                                <span style="font-weight: 400; font-size: 14px;">{{
                                        $t('Enable CSV Export Button')
                                    }}</span>
                            </div>
                        </template>
                        <div class="grid grid-cols-2 gap-5 mt-4" style="margin-bottom: -12px">
                            <div>
                                <div class="mb-2 font-[500] text-[14px]">{{ $t("Button Text") }}</div>
                                <NinjaInput v-model="table_buttons.csv.label" placeholder="Enter button text"/>
                            </div>
                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <div class="mb-2 font-[500] text-[14px]">{{ $t("Button BG Color") }}</div>
                                    <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                        <ColorPicker :labelShow="false" v-model="table_buttons.csv.bg_color"/>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-2 font-[500] text-[14px]">{{ $t("Button Text Color") }}</div>
                                    <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                        <ColorPicker :labelShow="false" v-model="table_buttons.csv.text_color"/>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <el-checkbox size="large" :true-value="'yes'" :false-value="'no'"
                                             v-model="table_buttons.csv.plainText">
                                    Export as Plain Text
                                </el-checkbox>
                            </div>
                        </div>
                    </el-collapse-item>
                    <el-collapse-item name="print">
                        <template #title>
                            <div>
                                <el-switch
                                    size="small"
                                    v-model="table_buttons.print.status"
                                    class="mr-2"
                                    active-value="yes"
                                    inactive-value="no"
                                    @click="(e)=>{
                                        activeCollapse = table_buttons.print.status === 'yes' ? ['print'] : []
                                        e.stopPropagation();
                                    }"
                                />
                                <span style="font-weight: 400; font-size: 14px;">{{ $t('Enable Print Button') }}</span>
                            </div>
                        </template>
                        <div class="grid grid-cols-2 gap-5 my-4">
                            <div>
                                <div class="mb-2 font-[500] text-[14px]">{{ $t("Button Text") }}</div>
                                <NinjaInput v-model="table_buttons.print.label" placeholder="Enter button text"/>
                            </div>
                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <div class="mb-2 font-[500] text-[14px]">{{ $t("Button BG Color") }}</div>
                                    <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                        <ColorPicker :labelShow="false" v-model="table_buttons.print.bg_color"/>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-2 font-[500] text-[14px]">{{ $t("Button Text Color") }}</div>
                                    <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                        <ColorPicker :labelShow="false" v-model="table_buttons.print.text_color"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between rounded-[8px] bg-white w-fit px-2 py-2 gap-3">
                            <div @click="print_screen ='header'"
                                 :class="`${print_screen ==='header' && 'bg-[#f9f9f9] rounded-[8px] shadow-md shadow-gray-300'} px-5 py-1 cursor-pointer`">
                                {{ $t('Print Screen Header') }}
                            </div>
                            <div @click="print_screen ='footer'"
                                 :class="`${print_screen ==='footer' && 'bg-[#f9f9f9] rounded-[8px] shadow-md shadow-gray-300'} px-5 py-1 cursor-pointer`">
                                {{ $t('Print Screen Footer') }}
                            </div>
                        </div>
                        <div v-if="print_screen === 'header'">
                            <div class="font-[500] text-[14px] my-3">{{ $t("Header in each page") }}</div>
                            <div class="ninja_tables_radio_group my-3">
                                <el-radio-group v-model="table_buttons.print.header_each_page">
                                    <el-radio value="yes" size="large" border>{{ $t("Yes") }}</el-radio>
                                    <el-radio value="no" size="large" border>{{ $t("No") }}</el-radio>
                                </el-radio-group>
                            </div>
                            <div class="my-3">
                                <div class="font-[500] text-[14px] my-3">{{ $t("Description For Header") }}</div>
                                <WPEditor v-model="table_buttons.print.header_html"/>
                            </div>
                        </div>
                        <div v-else-if="print_screen === 'footer'">
                            <div class="font-[500] text-[14px] my-3">{{ $t("Footer in each page") }}</div>
                            <div class="ninja_tables_radio_group my-3">
                                <el-radio-group v-model="table_buttons.print.footer_each_page">
                                    <el-radio value="yes" size="large" border>{{ $t("Yes") }}</el-radio>
                                    <el-radio value="no" size="large" border>{{ $t("No") }}</el-radio>
                                </el-radio-group>
                            </div>
                            <div class="my-3">
                                <div class="font-[500] text-[14px] my-3">{{ $t("Description For Footer") }}</div>
                                <WPEditor v-model="table_buttons.print.footer_html"/>
                            </div>
                        </div>
                    </el-collapse-item>
                </el-collapse>
            </div>

            <div class="mb-3 text-[16px] font-[500]">{{ $t("Buttons Placement") }}</div>
            <div class="p-5 border-solid border border-[#E1E4EA] rounded-[12px]">
                <div class="mb-5">
                    <div class="font-[500] text-[14px] mb-3">{{ $t("Button Position In Frontend") }}</div>
                    <div class="ninja_tables_radio_group mb-3">
                        <el-radio-group v-model="table_buttons.button_position">
                            <el-radio v-for="(title, key) in button_positions" :key="key" :value="key" size="large" border>
                                {{ $t(title) }}
                            </el-radio>
                        </el-radio-group>
                    </div>
                </div>
                <div class="font-[500] text-[14px] mb-3">{{ $t("Button Alignment") }}</div>
                <div class="ninja_tables_radio_group mb-3">
                    <el-radio-group v-model="table_buttons.button_alignment">
                        <el-radio v-for="(title, key) in buttonAlignments" :key="key" :value="key" size="large" border>
                            {{ $t(title) }}
                        </el-radio>
                    </el-radio-group>
                </div>
            </div>

            <div class="flex justify-end mt-5">
                <NinjaButton @click="saveSettings" type="primary" :btnText="$t('Save Settings')"/>
            </div>
        </div>

    </div>

</template>
<script type="text/babel">

import WPEditor from '../../../../common/_wp_editor.vue';
import GetPro from "../../Tools/GetPro";
import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
import ColorPicker from "../../../@ui-utils/ColorPicker.vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";
import {assetUrl} from "../../../utils/ninjatablesadmin";

export default {
    name: 'button_settings',
    components: {
        NinjaButton,
        ColorPicker,
        NinjaInput,
        GetPro,
        WPEditor
    },
    props: ['table_id'],
    data() {
        return {
            print_screen: 'header',
            activeCollapse: [],
            table_buttons: {
                csv: {
                    status: 'no',
                    label: 'CSV',
                    all_rows: 'no'
                },
                print: {
                    status: 'no',
                    label: 'Print',
                    all_rows: 'no',
                    header_each_page: 'no',
                    footer_each_page: 'no',
                }
            },
            fetching: false,
            saving: false,
            button_positions: {
                after_search_box: 'After Search Box',
                before_table: 'Top of the Table',
                after_table: 'Bottom of the Table',
            },
            buttonAlignments: {
                'ninja_buttons_left': 'Left',
                'ninja_buttons_center': 'Center',
                'ninja_buttons_right': 'Right'
            },
            hasPro: !!window.ninja_table_admin.hasPro
        }
    },
    methods: {
        assetUrl,
        getSettings() {
            this.fetching = true;
            this.$get('settings/' + this.table_id + '/button')
                .then(response => {
                    this.table_buttons = response.data.button_settings;
                    this.fetching = false;
                })
                .catch(error => {
                    this.fetching = false;
                })
        },
        saveSettings() {
            this.saving = true;
            let data = {
                table_id: this.table_id,
                button_settings: this.table_buttons
            };
            this.$post('settings/' + this.table_id + '/button', data)
                .then(response => {
                    this.$message({
                        showClose: true,
                        message: response.data.message,
                        type: 'success'
                    });
                    this.saving = false;
                })
                .catch(error => {
                    this.saving = false;
                })
        }
    },
    mounted() {
        this.getSettings();
    }
}
</script>
