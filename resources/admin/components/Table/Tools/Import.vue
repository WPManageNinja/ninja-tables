<template>
    <div>
        <div class="ninja_header">
            <h2>{{ $t('Import Table Data') }}</h2>
        </div>
        <div v-if="config.table.isImportable" class="ninja_content">
            <div v-if="columns.length">
                <form action="" id="fileUploadForm" class="">
                    <div class="form-group">
                        <input type="file" id="fileUpload" @click="clear">

                        <el-checkbox v-model="replace">{{ $t('Replace Existing Data') }}</el-checkbox>
                    </div>

                    <div class="form-group">
                        <el-checkbox true-label="yes" false-label="no" v-model="do_unicode">Convert to UTF-8 format ( Check this if your csv is non-unicode format )</el-checkbox>
                    </div>

                    <div class="form-group">
                        <el-button type="primary" icon="el-icon-upload2" size="small"
                                   @click.prevent="upload" :loading="btnLoading">
                            {{ $t('Import from CSV') }}
                        </el-button>
                    </div>
                </form>
                <div class="ninja_suggest">
                    <p>
                        Please note that, your CSV data structure need to follow the sample CSV.
                        You may want to check the <a :href="tutorial">video tutorial here.</a>
                    </p>
                    <br>
                    <p>
                        Also make sure the content is in UTF-8 format, for the best result.
                    </p>
                </div>

                <div class="justify-items">
                    <h3>
                        {{ $t('CSV Header Structure') }}
                    </h3>

                    <el-button type="primary" icon="el-icon-download" size="small"
                               style="float: right" @click="download">
                        {{ $t('Download Sample CSV') }}
                    </el-button>
                </div>

                <el-table border :data="sampleData" style="width: 100%" stripe>
                    <el-table-column v-for="column in columns"
                                     :prop="column.key"
                                     :label="column.key"
                                     :key="column.key"
                    ></el-table-column>
                </el-table>

                <h3>or as bellow</h3>
                <el-table border :data="sampleData" style="width: 100%" stripe>
                    <el-table-column v-for="column in columns"
                                     :prop="column.key"
                                     :label="column.name"
                                     :key="column.key"
                    ></el-table-column>
                </el-table>
            </div>
            <div v-else="" class="error">
                <p>{{ $t('Please set table configuration first.') }}</p>
            </div>
        </div>

        <div v-else class="ninja_content">
            <div class="ninja_suggest">
                <p>Sorry! You can not import any data as the table data is configured as external source ({{ config.table.dataSourceType }})</p>
            </div>
        </div>
    </div>
</template>

<script>
    import each from 'lodash/each'

    export default {
        name: "Import",
        props: ['config', 'tableId'],
        data() {
            return {
                btnLoading: false,
                replace: false,
                tutorial: "https://wpmanageninja.com/docs/ninja-tables/import-table-data-from-csv/?utm_source=ninja-tables",
                do_unicode: 'no'
            }
        },
        computed: {
            columns() {
                return this.config && this.config.columns ? this.config.columns : [];
            },
            sampleData() {
                let row = {};
                each(this.columns, item => {
                    row[item.key] = 'column value';
                });
                return Array(3).fill(row);
            }
        },
        methods: {
            clear() {
                jQuery('#fileUpload').val('');
            },
            upload() {
                var that = this;

                that.btnLoading = true;

                var file = jQuery('#fileUpload')[0].files[0];

                if (!file) {
                    that.btnLoading = false;
                    return;
                }

                var formData = new FormData();

                formData.append('file', file);
                formData.append('action', 'ninja_tables_ajax_actions');
                formData.append('target_action', 'upload-data');
                formData.append('table_id', this.tableId);
                formData.append('replace', this.replace);
                formData.append('do_unicode', this.do_unicode);
                formData.append('ninja_table_admin_nonce', window.ninja_table_admin.ninja_table_admin_nonce);

                jQuery.ajax({
                    url: ajaxurl,
                    data: formData,
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        that.$emit('csvUploaded');

                        that.clear();

                        that.$message.success(response.message)
                    },
                    error: function (error) {
                        that.$message.error(error.responseJSON.message)
                    }
                })
                    .always(function () {
                        that.btnLoading = false;
                    });
            },
            download: function () {
                var rows = [1, 2];

                var samples = [];

                var headers = this.config.columns.map(item => {
                    return item.key;
                });

                samples.push(headers);

                rows.forEach(function (row, rowIndex) {
                    var item = [];

                    headers.forEach(function (header, headerIndex) {
                        item.push('content_' + rowIndex + '_' + headerIndex);
                    })

                    samples.push(item);
                })

                var csv = "data:text/csv;charset=utf-8,";

                samples.forEach(function (item, index) {
                    var dataString = item.join(",");
                    csv += index < samples.length ? dataString + "\n" : dataString;
                });

                var encodedUri = encodeURI(csv);

                var link = document.createElement("a");

                link.setAttribute("href", encodedUri);

                link.setAttribute("download", "sample.csv");

                document.body.appendChild(link);

                link.click();
            }
        }
    }
</script>

<style scoped lang="scss">
    #fileUpload {
        max-width: 200px;
    }

    .justify-items {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ninja_content .ninja_suggest {
        background: #f1f1f1;
    }

    .ninja_content {
        margin: 1em 0;
    }
</style>
