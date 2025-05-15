<template>
    <div class="export-table-wrapper ml-2">
        <div class="text-[18px] font-[600] text-[#0E121B] my-5">{{ $t('Export Table Here') }}</div>
        <div v-if="config.table.isExportable">
            <div class="text-[14px] font-[400] text-[#0E121B] my-5 w-1/2">{{
                    $t("You can download the table as CSV or JSON format, If you download as JSON then you can can import the table to Fluent table installation")
                }}
            </div>


            <el-form-item size="large" label-position="top" :label="$t('Format')" class="!mb-[10px] w-1/2">
                <el-select class="ninja-data-tables_select" v-model="selected">
                    <el-option value="csv" label="CSV"/>
                    <el-option value="json" label="JSON"/>
                </el-select>
            </el-form-item>

            <NinjaButton class="my-3" type="primary" @click="doExport"
                         :btnText="$t('Export Table')"/>


        </div>
        <div v-else>
            <div class="text-[14px] font-[400] text-[#0E121B] my-5 w-1/2">
                {{ $t('Sorry! You can not export the data as the table data is configured as external source') }}
                ({{ config.table.dataSourceType }})
            </div>
        </div>
    </div>
</template>

<script>
import {Download} from "@element-plus/icons-vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";

export default {
    name: 'ExportTable',
    props: ['config'],
    components: {
        NinjaButton,
        Download
    },

    data() {
        return {
            tableId: this.$route.params.table_id,
            selected: 'csv'
        }
    },
    methods: {
        downloadLink(format = 'csv') {
            return `${window.ajaxurl}?action=ninja-tables-default-export&table_id=${this.$route.params.table_id}&format=${format}`;
        },
        doExport() {
            location.href = this.downloadLink(this.selected);
        }
    }
}
</script>
