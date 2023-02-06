<template>
    <div>

        <div class="ninja_header">
            <h2>{{$t('Export Data')}}</h2>
        </div>
        <div v-if="config.table.isExportable" class="ninja_content">
            <div class="ninja_suggest">
                <p>You can download the table data as CSV or JSON format, If you download as json then you can import the table to any Ninja Table Installation</p>
            </div>
            <div class="ninja_export_block">
                {{ $t('Format:') }}
                <select v-model="selected">
                    <option v-for="(option, key) in exportOptions" :value="key">
                        {{ option }}
                    </option>
                </select>
                <el-button type="primary" icon="el-icon-download" size="small"
                           @click.prevent="doExport()">
                    {{ $t('Export') }}
                </el-button>
            </div>
        </div>
        <div v-else class="ninja_content">
            <div class="ninja_suggest">
                <p>Sorry! You can not export the data as the table data is configured as external source ({{ config.table.dataSourceType }})</p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'ExportTable',
        props: ['config'],
        data() {
            return {
                tableId: this.$route.params.table_id,
                exportOptions: {
                    csv : 'CSV',
                    json: 'JSON'
                },
                selected: 'csv'
            }
        },
        methods: {
            downloadLink(format = 'csv') {
                const data = {
                    action: 'ninja_tables_ajax_actions',
                    target_action: 'export-data',
                    table_id: this.tableId,
                    format: format,
                    ninja_table_admin_nonce: window.ninja_table_admin.ninja_table_admin_nonce
                };

                return ajaxurl+'?'+jQuery.param(data)
            },
            doExport() {
                let url = this.downloadLink(this.selected);
                location.href = url;
            }
        }
    }
</script>
