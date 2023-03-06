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
                const payloadData = {
                    table_id: this.tableId,
                    format: format
                };

                return window.ninja_table_admin.rest.url + '/export/default?' + jQuery.param(payloadData);
            },
            doExport() {
               location.href = this.downloadLink(this.selected);
            }
        }
    }
</script>
