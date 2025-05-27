<template>
    <div>
        <div class="ninja_modal-body">
            <h3>{{ $t('Import Table') }}</h3>

            <p class="ninja_subtitle">
                {{ $t('Import table from existing CSV or JSON file.') }}
            </p>

            <div class="form">
                <!--Import data-->
                <div class="form-group">
                    <template v-if="imports.source === 'file'">
                        <label for="fileUpload">{{ $t('Select file:') }}</label>
                        <br>
                        <input type="file" id="fileUpload" @click="clear">
                    </template>

                    <template v-else-if="imports.source === 'url'">
                        File upload url
                    </template>

                    <template v-else>
                        <label>{{ $t('Import data:') }}</label>
                        <textarea rows="10"></textarea>
                    </template>
                </div>

                <!--Import format-->
                <div class="form-group">
                    <label for="import_format">{{ $t('Import Format:') }}</label>

                    <select id="import_format" v-model="imports.format" class="form-control">
                        <option :value="format"
                                v-for="(option, format) in imports.formatOptions"
                        >{{ $t(option) }}
                        </option>
                    </select>

                    <template v-if="imports.format === 'csv'">
                        <p class="hint">
                            Check tutorial for importing data from CSV file
                            <a class="nt-link" href="https://ninjatables.com/docs/import-table-data-from-a-csv/"
                               target="_blank"
                            >here</a>
                        </p>
                        <el-checkbox
                            :true-value="'yes'"
                            :false-value="'no'"
                            v-model="do_unicode">
                            Convert to UTF-8 format ( Check this if your csv is non-unicode format )
                        </el-checkbox>
                    </template>



                    <p v-show="imports.format === 'json' || imports.format === 'ninjaJson'" class="hint">
                        Check tutorial for importing Table from JSON file

                        <a class="nt-link" href="https://ninjatables.com/docs/import-ninja-table-json/"
                           target="_blank"
                        >here</a>
                    </p>
                </div>

                <div v-if="errors.length" class="form_group ninja_errors">
                    <ul>
                        <li v-for="(error,error_index) in errors" :key="error_index">
                            {{ error.info }}
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <el-button
                size="small"
                type="primary"
                :loading="btnLoading"
                @click="importTable"
            >
                {{ $t('Import') }}
            </el-button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ImportTable",
        data() {
            return {
                imports: {
                    source: 'file',
                    sourceOptions: ['file'],
                    formatOptions: {
                        'csv': this.$t('CSV - Comma-separated values'),
                        'json': this.$t('JSON - Javascript Object Notation'),
                        'ninjaJson': this.$t('JSON - Exported From Ninja Tables'),
                        'dragAndDrop': this.$t('JSON/CSV - Exported From Drag & Drop Tables'),
                    },
                    format: 'csv'
                },
                do_unicode: 'no',
                errors: [],
                btnLoading: false
            }
        },
        methods: {
            clear() {
              document.getElementById('fileUpload').value = '';
            },
            importTable() {
                this.btnLoading = true;
                this.errors = [];
                // For now only execute when the import source is `file`
                if (this.imports.source !== 'file') {
                    this.btnLoading = true;
                    return;
                }

                let file = document.getElementById('fileUpload').files[0];

                if (!file) {
                    this.btnLoading = false;
                    return;
                }

                let formData = new FormData();

                formData.append('format', this.imports.format);
                formData.append('file', file);
                formData.append('do_unicode', this.do_unicode);

                this.$post('import/default', formData)
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.message ? response.message : this.$t("Successfully added a table aaa."),
                            type: 'success'
                        });
                        if (this.imports.format === 'dragAndDrop') {
                          this.$router.push({
                            name: "table_builder_edit_table",
                            params: {table_id: response.data.id},
                          });
                        } else {
                          if(response.tableId) {
                            this.$router.push({
                              name: 'data_items',
                              params: {table_id: response.tableId}
                            });
                          }
                        }
                    })
                    .catch(error => {
                        this.errors = error.responseJSON.data.errors;
                        this.$message({
                            showClose: true,
                            message: error.responseJSON.data.message,
                            type: 'error'
                        });
                        this.btnLoading = false;
                    })
            }
        }
    }
</script>

<style scoped lang="scss">
    .hint {
        width: 100%;
        background-color: #f4f4f5;
        color: #909399;
        padding: 8px 16px;
    }

    .form_group.ninja_errors {
        background: rgb(255, 215, 215);
        padding: 10px;
    }
</style>
