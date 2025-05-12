<template>
    <div class="nt-custom-css-js-editor-wrapper">
        <div class="flex justify-between rounded-[8px] bg-[#F5F7FA] w-[250px] px-2 py-2 gap-3">
            <div @click="current_tab ='additional_css'"
                 :class="`${current_tab ==='additional_css' && 'bg-white rounded-[8px] shadow shadow-md shadow-gray-300'} px-5 py-1 cursor-pointer`">
                {{ $t('Custom CSS') }}
            </div>
            <div @click="current_tab ='additional_js'"
                 :class="`${current_tab ==='additional_js' && 'bg-white rounded-[8px] shadow shadow-md shadow-gray-300'} px-5 py-1 cursor-pointer`">
                {{ $t('Custom JS') }}
            </div>
        </div>

        <div v-if="current_tab === 'additional_css'">
            <p class="my-[16px]">{{ $t('You may add') }}
                <code
                    class='bg-[#D3DCFF] py-[2px] px-[6px] rounded cursor-pointer copy'
                    :data-clipboard-text="'#footable_parent_'+ config.table.ID"
                >
                    #footable_parent_{{ config.table.ID }}
                </code>
                {{ $t('as your css selector prefix to target this specific table. Alternatively, you can use') }}
                <code
                    class='bg-[#D3DCFF] py-[2px] px-[6px] rounded cursor-pointer copy'
                    :data-clipboard-text="'#footable_parent_NT_ID'"
                >#footable_parent_NT_ID</code>
                {{ $t('where') }} <strong>NT_ID</strong> {{ $t(' will be replaced with your table ID dynamically.') }}
            </p>
            <ace_code_editor editor_id="ninja_custom_css" mode="css" v-model="custom_css"></ace_code_editor>
            <p v-html="styleTagInfo" class="ndt-editor-info mt-2"></p>
        </div>
        <div v-else-if="current_tab === 'additional_js'">
            <p class="my-4">
                {{
                    $t(`Your additional JS code will run after the table initialized. Please provide valid javascript code. Invalid JS code may break the table UI.`)
                }}
            </p>
            <p class="mb-4">
                {{ $t('The Following Javascript variables are available that you can use:') }} <br>
                <b>$table:</b> {{ $t('The Javascript DOM object of the table') }} <br>
                <b>tableConfig:</b> {{ $t('The configuration object of the table.') }}
            </p>
            <ace_code_editor editor_id="ninja_custom_js" mode="javascript" v-model="custom_js"></ace_code_editor>
            <p v-html="scriptTagInfo" class="ndt-editor-info mt-2"></p>
        </div>

        <div class="flex justify-end mt-0">
            <NinjaButton
                type="primary"
                :btnText="$t('Save Settings')"
                @click="saveScripts"
            />
        </div>
    </div>
</template>

<script type="text/babel">
import ace_code_editor from '../../../common/_ace_editor';
import NinjaButton from "../../@ui-utils/NinjaButton.vue";

export default {
    name: 'ninja_css_editor',
    props: ['config'],
    components: {
        NinjaButton,
        ace_code_editor
    },
    data() {
        return {
            current_tab: 'additional_css',
            custom_css: '',
            custom_js: '',
            hasPro: !!window.ninja_table_admin.hasPro,
            fetching: false,
            styleTagInfo: "Please don't include <code class='bg-[#D3DCFF] py-[2px] px-[6px] rounded'>&lt;style&gt;&lt;/style&gt;</code> tag",
            scriptTagInfo: "Please don't include <code class='bg-[#D3DCFF] py-[2px] px-[6px] rounded'>&lt;script&gt;&lt;/script&gt;</code> tag"
        }
    },
    methods: {
        saveScripts() {
            if (!this.hasPro) {
                this.custom_js = '';
            }
            let tableId = this.config.table.ID;
            let data = {
                table_id: this.config.table.ID,
                custom_css: this.custom_css,
                custom_js: this.custom_js,
            }
            this.$post('settings/' + tableId + '/custom-styles', data)
                .then(response => {
                    this.$message({
                        showClose: true,
                        message: response.data.message,
                        type: 'success'
                    });
                    this.config.table.custom_css = this.custom_css;
                    this.config.table.custom_js = this.custom_js;
                })
                .catch(error => {
                    console.log(error);
                })
        },
        getScripts() {
            this.fetching = true;
            let tableId = this.config.table.ID;

            this.$get('settings/' + tableId + '/custom-styles')
                .then(response => {
                    this.custom_css = response.data.custom_css;
                    this.custom_js = response.data.custom_js;
                })
                .catch(error => {

                })
                .finally(() => {
                    this.fetching = false;
                });
        }
    },
    mounted() {
        this.getScripts();
        this.clipboard(); // Initialize clipboard functionality
    }
}
</script>

<style>
.js_instruction {
    padding: 10px 20px;
    background: rgb(255, 255, 255);
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 22px;
}
</style>
