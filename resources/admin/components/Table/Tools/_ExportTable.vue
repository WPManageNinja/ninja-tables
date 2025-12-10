<template>
    <div class="export-table-wrapper ml-2">
        <div class="text-[18px] font-[600] text-[#0E121B] my-5">{{ $t('Export Table') }}</div>
        <div v-if="config.table.isExportable">
            <div class="text-[14px] font-[400] text-[#0E121B] my-5 w-1/2">{{
                    $t("You can download the table in CSV or JSON format. If you download it as JSON, you can import the table into a Ninja Tables installation.")
                }}
            </div>


            <div class="text-[14px] font-[400] text-[#0E121B] my-2 w-1/2">
                {{ $t('Format') }}
                <el-tooltip placement="right" effect="light"
                            popper-class="nt-custom-tooltip"
                            :content="$t('Select the format in which you want to export your table data. CSV format is suitable for spreadsheet applications, while JSON format preserves all table settings for importing into another Ninja Tables installation.')">
                    <el-icon class="tooltip-icon-color">
                        <InfoFilled/>
                    </el-icon>
                </el-tooltip>
            </div>
            <div class="flex w-1/2 gap-2">
                <el-select class="ninja-select" v-model="selected">
                    <el-option value="csv" label="CSV"/>
                    <el-option value="json" label="JSON"/>
                </el-select>

                <NinjaButton class="min-w-[130px]" type="primary" @click="doExport" :btnText="$t('Export Table')"/>
            </div>


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
import {Download, InfoFilled} from "@element-plus/icons-vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";

export default {
    name: 'ExportTable',
    props: ['config'],
    components: {
        NinjaButton,
        Download,
        InfoFilled
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
