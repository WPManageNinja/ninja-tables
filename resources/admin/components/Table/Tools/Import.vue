<template>
    <div class="import-table-wrapper ml-2">
        <div v-if="config.table.isImportable && config.columns.length">
            <div class="text-[18px] font-[600] text-[#0E121B] my-5">{{ $t('Import Table') }}</div>
            <div class="text-[14px] font-[400] text-[#0E121B] my-5 w-1/2">
                {{
                    $t("Import CSV data into the existing table. Please note that, your CSV data structure need to follow the sample CSV. Download the sample CSV to ensure correct data formatting.")
                }}
            </div>
            <div class="w-1/2 my-3">
                <div class="mt-5 rounded-2xl border-solid border-gray-200 border">
                    <div class="p-3 !border-gray-200 flex justify-between items-center"
                         style="border-bottom: solid 1px">
                        <p>{{ $t('Import table from here') }}</p>
                        <a class="flex items-center cursor-pointer" @click="downloadSampleCSV">
                            <img class="mr-1" :src="assetUrl('icons/file-02.svg')"/>
                            <span class="text-[#335CFF]">{{$t('Download Sample CSV')}}</span>
                        </a>
                    </div>
                    <div class="p-[16px]">
                        <el-upload
                            drag
                            :on-change="handleChange"
                            :auto-upload="false"
                            name="file"
                            ref="uploadRef"
                            :limit="1"
                            action=""
                            accept=".csv"
                            :show-file-list="false"
                        >
                            <div class="mb-3">
                                <img class="mx-auto" :src="assetUrl('icons/upload-cloud-2-line.svg')"/>
                            </div>
                            <div class="el-upload__text">{{ $t("Choose a CSV file or drag & drop it here.") }}</div>
                        </el-upload>

                        <div v-if="uploadedFile?.name"
                             class="mt-3 flex justify-between items-center border border-solid border-[#e1e4ea] rounded-[10px] px-5 py-4">
                            <div class="flex items-center">
                                <img class="mr-2" :src="getFileIcon()"/>
                                <div>
                                    <div>{{ uploadedFile.name }}</div>
                                    <div class="flex items-center">
                                        <small class="mr-2">{{ (uploadedFile.size / 1024).toFixed(2) }} KB .</small>
                                        <img class="" :src="assetUrl('icons/check-box-fill.svg')"/>
                                        <small>{{ $t('Completed') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div @click="clear" class="cursor-pointer">
                                <img :src="assetUrl('icons/delete-02.svg')"/>
                            </div>
                        </div>
                        <div class="mt-4" v-if="uploadedFile?.status === 'ready'">
                            <el-checkbox v-model="replace" :label="$t('Replace Existing Data')"/>
                            <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="do_unicode"
                                         :label="$t('Convert to UTF-8 format ( Check this if your csv is non-unicode format)')"/>
                            <div class="py-[16px] flex justify-end">
                                <NinjaButton type="primary" :btnText="$t('Import Table')" @click.prevent="upload"/>
                            </div>
                        </div>
                        <div v-if="uploadedFile?.status === 'error'" class="text-red-500">
                            <small>{{ uploadedFile.message }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-[18px] font-[600] text-[#0E121B] mt-5">{{ $t('Sample Header Structure') }}</div>

            <div>
                <el-table :data="getSampleTableData()" border class="nt-inner-table">
                    <el-table-column
                        v-for="column in config.columns"
                        :key="column.key"
                        :prop="column.key"
                        :label="column.name"
                        align="center"
                    />
                </el-table>
                <div class="mt-3 text-sm text-gray-500">
                    {{ $t('Your CSV file should follow this column structure.') }}
                </div>
            </div>
        </div>
        <div v-else-if="config.table.isImportable && !config.columns.length" class="nt-instruction text-center my-4 w-1/2">
            <h3 class="nt-modal-subtitle my-3">{{ $t('Table Configuration Required') }}</h3>
            <p class="nt-modal-description">{{ $t('Please configure the table columns before importing data.' )}}</p>
        </div>
        <div v-else class="nt-instruction text-center">
            <h3 class="nt-modal-subtitle mb-3">{{ $t('Import Table') }}</h3>
            <p class="nt-modal-description">{{ $t(`Sorry! You can not import any data as the table data is configured as external source (${config.table.dataSourceType})`) }}</p>
        </div>
    </div>
</template>

<script>
import each from 'lodash/each'
import {Download, Upload} from "@element-plus/icons-vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";
import {assetUrl} from "../../../utils/ninjatablesadmin";
import GetPro from "../../Tools/GetPro.vue";

export default {
    name: "Import",
    components: {GetPro, NinjaButton, Download, Upload},
    props: ['config', 'tableId'],
    data() {
        return {
            btnLoading: false,
            replace: false,
            tutorial: "https://wpmanageninja.com/docs/ninja-tables/import-table-data-from-csv/?utm_source=ninja-tables",
            do_unicode: 'no',
            uploadedFile: null,
            uploadRef: null,
        }
    },
    computed: {
        columns() {
            return this.config && this.config.columns ? this.config.columns : [];
        }
    },
    methods: {
        assetUrl,
        getFileIcon() {
            return this.assetUrl('icons/csv-file-ico.svg');
        },
        clear() {
            this.uploadedFile = null;
            this.$refs.uploadRef.clearFiles();
        },
        handleChange(file) {
            this.clear();
            this.uploadedFile = file;
        },
        upload() {
            this.btnLoading = true;
            let that = this;
            let file = this.uploadedFile.raw;
            if (file && file.status === 'error') {
                that.btnLoading = false;
                that.$message.error(this.$t('File upload failed'));
                return;
            }

            let formData = new FormData();

            formData.append('file', file);
            formData.append('table_id', this.tableId);
            formData.append('replace', this.replace);
            formData.append('do_unicode', this.do_unicode);

            this.$post('import/upload-csv-in-existing-table', formData)
                .then(response => {
                    that.$emit('csvUploaded');

                    that.clear();
                    that.$message.success(response.data.message)
                })
                .catch(error => {
                    that.$message.error(error.data.message)
                })
            that.btnLoading = false;

        },
        getSampleTableData() {
            const sampleData = [];
            for (let i = 0; i < 3; i++) {
                const row = {};
                each(this.config.columns, column => {
                    row[column.key] = "column value";
                });
                sampleData.push(row);
            }
            return sampleData;
        },
        downloadSampleCSV() {
            const headers = this.config.columns.map(column => column.name).join(',');
            const rows = this.getSampleTableData()
                .map(row => this.config.columns.map(column => row[column.key]).join(','))
                .join('\n');

            // Download the CSV file
            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURI(headers + '\n' + rows);
            link.download = `sample-${this.tableId}.csv`;
            link.click();

            this.$message.success(this.$t('Sample CSV downloaded successfully'));
        }

    }
}
</script>
