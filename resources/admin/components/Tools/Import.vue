<template>
    <div>
        <div class="ninja_header">
            <h2>{{ $t('Import Table') }}</h2>
        </div>
        <div class="ninja_content">
            <div class="ninja_block">
                <p>
                    {{ $t('NinjaTables can import tables from existing data, like from a CSV or JSON file. You can also import existing tables from the other WordPress table plugins.') }}
                </p>
            </div>

            <hr/>
            <div class="ninja_block_section">
                <h3>Import Table from CSV / JSON File</h3>
                <p>
                    Browse and locate a CSV/JSON file you backed up before.
                </p>
                <p>
                    Select the intended format and click <strong>Import</strong> button, we will do
                    the rest for you.
                </p>

                <div class="form">
                    <!--Import data-->
                    <div class="form-item">
                        <template v-if="imports.source == 'file'">
                            <label>{{ $t('Select file:') }}</label>
                            <input type="file" id="fileUpload" @click="clear">
                        </template>
                        <template v-else-if="imports.source == 'url'">
                            File upload url
                        </template>
                        <template v-else>
                            <label>{{ $t('Import data:') }}</label>
                            <textarea rows="10"></textarea>
                        </template>
                    </div>

                    <!--Import format-->
                    <div class="form-item">
                        <label for="import_format">{{ $t('Import Format:') }}</label>
                        <select id="import_format" v-model="imports.format">
                            <option :value="format"
                                    v-for="(option, format) in imports.formatOptions"
                            >{{ $t(option) }}
                            </option>
                        </select>

                        <template v-if="imports.format == 'csv'">
                            <span  class="help">
                                Check tutorial for importing data from CSV file
                              <a
                                    href="https://ninjatables.com/docs/import-table-data-from-a-csv/"
                                    target="_blank">here</a>
                            </span>

                            <div class="form-item">
                                <el-checkbox
                                    :true-value="'yes'"
                                    :false-value="'no'"
                                    v-model="do_unicode"
                                >
                                    Convert to UTF-8 format (Check this if your CSV is non-unicode format)
                                </el-checkbox>
                            </div>
                        </template>


                        <span v-show="imports.format == 'json' || imports.format == 'ninjaJson'"
                              class="help">
                                Check tutorial for importing Table from JSON file<a
                                href="https://ninjatables.com/docs/import-ninja-table-json/"
                                target="_blank">here</a>
                            </span>
                    </div>

                    <div class="form-item">
                        <el-button type="primary" size="small" :loading="btnLoading" @click="importTable">
                            {{ $t('Import') }}
                        </el-button>
                    </div>
                </div>
            </div>

            <hr/>

            <div class="ninja_block_section">
                <h3>Import From Other WP Table Plugin</h3>
                <p>
                    To import from other WordPress plugins click the respective <strong>Import</strong>
                    button.
                </p>
                <table style="min-width: 400px;">
                    <tbody>
                    <tr v-for="(plugin, plugin_key) in otherPlugins">
                        <td>{{ plugin }}</td>
                        <td>
                            <button class="btn btn-default btn-sm"
                                    @click="importFromOtherPlugin(plugin_key)"
                            >
                                <template v-if="btnsLoading[plugin_key]">
                                    {{ $t('Processing...') }}
                                    <i class="fooicon fooicon-spin fooicon-circle-o-notch"></i>
                                </template>
                                <template v-else>
                                    {{ $t('Import') }}
                                </template>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <el-dialog
                title="Your current tables"
                v-model="showPluginModal"
                @close="closePluginModal()"
        >
            <template v-if="otherPluginTables.length">
                <el-table
                        :data="otherPluginTables"
                        style="width: 100%"
                >
                    <el-table-column label="Name">
                        <template #default="scope">
                            <span v-if="scope.row.is_already_imported">( Already Imported )</span> {{
                            scope.row.post_title }}
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="Action"
                            fixed="right"
                    >
                        <template #default="scope">
                            <el-button type="primary" size="small"
                                       @click="importThisTable(scope.row, scope.$index)"
                            >{{ $t('Import') }}
                            </el-button>
                            <router-link
                                    :to="{ name: 'data_items', params: { table_id: scope.row.ninja_table_id } }"
                                    class="el-button el-button--danger el-button--mini ninja_btn"
                                    v-if="scope.row.ninja_table_id"
                            >{{ $t('View Imported Table') }}
                            </router-link>
                        </template>
                    </el-table-column>
                </el-table>

                <template v-if="importing">
                    <br><br>
                    <div class="updated notice notice-success"
                         style="padding: 10px;"
                    >
                        {{ $t('Importing the table, please wait a bit ...') }}
                    </div>
                </template>
            </template>

            <div class="updated notice notice-success"
                 style="padding: 10px;"
                 v-else
            >
                You don't have any tables in your {{ selectedPlugin }} plugin.
            </div>
        </el-dialog>
    </div>
</template>

<script>
    export default {
        name: 'Tools',
        data() {
            return {
                has_pro: window.ninja_table_admin.hasPro,
                active_menu: 'import',
                activeNames: [
                    '1',
                    '2'
                ],
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
                btnLoading: false,
                otherPlugins: {
                    'TablePress': 'Table Press',
                    'supsystic': 'Data Tables Generator by Supsystic',
                },
                btnsLoading: {
                    'TablePress': false,
                },
                showPluginModal: false,
                selectedPlugin: null,
                otherPluginTables: [],
                importing: false
            }
        },
        methods: {
            clear() {
               document.getElementById('fileUpload').value = '';
            },
            importTable() {
                this.btnLoading = true;

                // For now only execute when the import source is `file`
                if (!this.imports.source == 'file') {
                    this.btnLoading = true;
                    return;
                }

                let file = document.getElementById('fileUpload').files[0];

                if (!file) {
                    this.btnLoading = false;
                    return;
                }

                var formData = new FormData();

                formData.append('format', this.imports.format);
                formData.append('file', file);
                formData.append('do_unicode', this.do_unicode);

              this.$post('import/default', formData)
                    .then(response => {
                      alert(response.message ? response.message : this.$t("Successfully added a table."));
                      if (this.imports.format === 'dragAndDrop') {
                        this.$router.push({
                          name: "table_builder_edit_table",
                          params: {table_id: response.data.id},
                        });
                      } else {
                        this.$router.push({
                          name: 'data_items',
                          params: {table_id: response.tableId}
                        });
                      }
                    })
                    .catch(error => {
                        this.btnLoading = false;
                        alert(error.responseJSON.message);
                    })
            },
            importFromOtherPlugin(plugin) {
                this.selectedPlugin = plugin;

                this.btnsLoading[plugin] = true;

                let data = {
                    plugin,
                };

                this.$post('import/get-tables-from-other-plugin', data)
                    .then(response => {
                        if(!response.tables) {
                            this.$message.error('No Table Found');
                        } else {
                            this.btnsLoading[plugin] = false;
                        }
                        this.showPluginModal = true;
                        this.otherPluginTables = response.tables;
                    })
                    .catch(error => {
                        this.btnsLoading[plugin] = false;
                        if(error.responseJSON) {
                            this.$message.error(error.responseJSON.message);
                        } else {
                            this.$message.error('No Table Found');
                        }
                    });
            },
            closePluginModal() {
                this.otherPluginTables = [];
                this.btnsLoading[this.selectedPlugin] = false;
                this.showPluginModal = false;
                this.selectedPlugin = null;

            },
            importThisTable(table, index) {
                this.importing = true;

                let data = {
                    plugin: this.selectedPlugin,
                    tableId: table.ID,
                };

                this.$post('import/import-table-from-other-plugin', data)
                    .then(response => {
                        this.$message.success(response.data.message);
                        this.importing = false;
                        this.otherPluginTables[index].ninja_table_id = response.data.tableId;
                    })
                    .catch(error => {
                        this.$message.error(error.data.message);
                        this.importing = false;
                    })
            }
        },
        mounted() {
            if (this.$route.query.active_menu) {
                this.active_menu = this.$route.query.active_menu;
            }
            jQuery('.ninja_table_tools_menu').on('click', () => {
                this.active_menu = 'import';
            });

            jQuery('.ninja_table_import_menu').on('click', () => {
                this.active_menu = 'import';
            });

            jQuery('.ninja_table_license_menu').on('click', () => {
                this.active_menu = 'license';
            });

        }
    }
</script>

<style scoped>
    .form-item {
        margin: 10px 0;
    }

    .form-item label {
        width: 100px;
    }
</style>
