<template>
    <div class="import-table-wrapper ml-2">
        <div class="text-[18px] font-[600] text-[#0E121B] my-5">{{ $t('Import Table') }}</div>
        <div v-if="config.table.isImportable">
            <div class="text-[14px] font-[400] text-[#0E121B] my-5 w-1/2">
                {{$t("Import CSV data into the existing table. Please note that, your CSV data structure need to follow the sample CSV. Download the sample CSV to ensure correct data formatting.") }}
            </div>
            <div class="w-1/2 my-3">

                <input type="file" id="fileUpload" @click="clear">

                <el-checkbox v-model="replace">{{ $t('Replace Existing Data') }}</el-checkbox>
                <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="do_unicode">Convert to UTF-8 format ( Check this if your csv is non-unicode format )</el-checkbox>
                <div class="py-[16px]">
                        <NinjaButton size="small" type="primary" :btnText="$t('Import Table')" @click.prevent="upload"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import each from 'lodash/each'
    import {Download, Upload} from "@element-plus/icons-vue";
    import NinjaButton from "../../../@ui-utils/NinjaButton.vue";

    export default {
        name: "Import",
        components: {NinjaButton, Download, Upload},
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

                let file = document.getElementById('fileUpload').files[0];

                if (!file) {
                    that.btnLoading = false;
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
