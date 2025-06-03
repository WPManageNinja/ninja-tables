<template>
    <div class="">
        <div class="text-[18px] font-[600] text-[#0E121B]">{{ $t('Import Table') }}</div>
        <div class="ninja_content">
            <div class="text-[14px] font-[400] text-[#0E121B] my-5">
                {{ $t("Ninja Tables can import tables from existing data, like from a CSV or JSON file. You can also import existing tables from the other WordPress table plugins.") }}
            </div>
            <div class="my-2">
                <p class="mb-2 text-[#0E121B] text-[16px] font-[500]">{{ $t('Import Table from CSV / JSON File') }}</p>
                <p class="mb-1 text-[#0E121B] text-[14px]">{{$t('Browse and locate a CSV / JSON file you backed up before')}}</p>
                <p class="text-[#0E121B] text-[14px]">
                    Select the intended format and click <strong>Import</strong> button, we will do
                    the rest for you.
                </p>
            </div>
            <div class="ninja_block_section">
                <el-upload
                    drag
                    :on-change="handleChange"
                    :auto-upload="false"
                    name="file"
                    ref="uploadRef"
                    :limit="1"
                    action=""
                    :class="{ upload: true }+' mt-5'"
                    :accept="'.json,.csv'"
                    :show-file-list="false"
                >
                    <div class="mb-5">
                        <img class="mx-auto" :src="assetUrl('icons/upload-cloud-2-line.svg')"/>
                    </div>
                    <div class="el-upload__text">{{$t("Choose a file or drag & drop it here.")}}</div>
                    <div class="font-[300] text-[12px]">{{$t("CSV or JSON")}}</div>
                </el-upload>

                <div v-if="uploadedFile?.name" class="mt-3 flex justify-between items-center border border-solid border-[#e1e4ea] rounded-[10px] px-5 py-4">
                    <div class="flex items-center">
                        <img class="mr-2" :src="getFileIcon(uploadedFile)"/>
                        <div>
                            <div>{{ uploadedFile.name }}</div>
                            <div class="flex items-center">
                                <small class="mr-2">{{(uploadedFile.size / 1024).toFixed(2)}} KB .</small>
                                <img class="" :src="assetUrl('icons/check-box-fill.svg')"/>
                                <small>{{$t('Completed')}}</small>
                            </div>
                        </div>
                    </div>
                    <div @click="clear" class="cursor-pointer">
                        <img :src="assetUrl('icons/delete-02.svg')"/>
                    </div>
                </div>
                <div class="form">
                    <!--Import data-->
                    <!-- <div class="form-item">
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
                    </div> -->

                    <!--Import format-->
                    <div class="form-item">
                        <label class="font-[400] mt-4 mb-2">{{ $t('Import Format') }}</label>
                        <el-select class="ninja-select" id="import_format" v-model="imports.format">
                            <el-option :value="format" :key="option"
                                       v-for="(option, format) in imports.formatOptions" :label="$t(option)"
                            >
                            </el-option>
                        </el-select>

                        <template v-if="imports.format == 'csv'" class="mt-4 mb-2">
                            <div class="text-[#0E121B] mt-4">
                                {{ $t('Check tutorial for importing data from CSV file') }}
                                <a
                                    class="nt-link"
                                    href="https://ninjatables.com/docs/import-table-data-from-a-csv/"
                                    target="_blank">here</a>
                            </div>

                            <div class="form-item">
                                <el-checkbox
                                    class="text-[#0E121B]"
                                    :true-value="'yes'"
                                    :false-value="'no'"
                                    v-model="do_unicode"
                                > {{ $t('Convert to UTF-8 format (Check this if your CSV is non-unicode format)') }}
                                </el-checkbox>
                            </div>
                        </template>


                        <span v-show="imports.format == 'json' || imports.format == 'ninjaJson'"
                              class="help">
                                Check tutorial for importing Table from JSON file <a
                            class="nt-link"
                            href="https://ninjatables.com/docs/import-ninja-table-json/"
                            target="_blank">here</a>
                        </span>
                    </div>

                    <div class="flex justify-end mt-2">
                        <NinjaButton :disabled="!uploadedFile" :loading="btnLoading" @click="importTable">
                            {{ $t('Import') }}
                        </NinjaButton>
                    </div>
                </div>
            </div>

            <hr v-if="migrationTables.length>0"/>
            <div class="ninja_block_section" v-if="migrationTables.length>0">
                <h3 class="text-[#0E121B] text-[16px] font-[500] mb-2">Import From Other WP Table Plugin</h3>
                <p class="text-[14px] font-[400] text-[#0E121B] my-2">
                    To import from other WordPress plugins click the respective <strong>Import</strong>
                    button.
                </p>
                <div class="w-full border border-solid border-[#e1e4ea] rounded-[10px] flex justify-between items-center py-2 px-3 mb-2 bg-gray-50/95"
                     v-for="(plugin) in migrationTables" :key="plugin.key">
                    <div class="font-[500]">
                        {{ plugin.title }}
                    </div>
                    <div>
                        <NinjaButton size="small" class="btn btn-default btn-sm"
                                     @click="importFromOtherPlugin(plugin.key)">
                            <template v-if="btnsLoading[plugin.key]">
                                {{ $t('Processing...') }}
                                <i class="fooicon fooicon-spin fooicon-circle-o-notch"></i>
                            </template>
                            <template v-else>
                                {{ $t('Import') }}
                            </template>
                        </NinjaButton>
                    </div>
                </div>
            </div>

        </div>

        <el-dialog
            title="Your current tables"
            v-model="showPluginModal"
            @close="closePluginModal()"
            class="ninja_create-table-modal"
            align-center
        >
            <div class="p-5" style="max-height: 70vh; overflow-y: auto;">
                <template v-if="otherPluginTables.length">
                    <el-table
                        :data="otherPluginTables"
                        class="nt-inner-table mt-4"
                        border
                    >
                        <el-table-column label="Name">
                            <template #default="scope">
                                <span v-if="scope.row.is_already_imported">( Already Imported )</span> {{
                                    scope.row.post_title }}
                            </template>
                        </el-table-column>
                        <el-table-column
                            width="250"
                            label="Action"
                        >
                            <template #default="scope">
                                <div class="flex items-center gap-2">
                                    <NinjaButton type="primary" size="small"
                                                 @click="importThisTable(scope.row, scope.$index)"
                                    >{{ $t('Import') }}
                                    </NinjaButton>
                                    <router-link
                                        :to="{ name: 'data_items', params: { table_id: scope.row.ninja_table_id } }"
                                        v-if="scope.row.ninja_table_id"
                                    >
                                        <NinjaButton type="secondary" size="small">
                                            {{ $t('View Table') }}
                                        </NinjaButton>
                                    </router-link>
                                </div>
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

                <div class="bg-red-50 text-red-500 border border-solid border-red-100 rounded-[10px] p-3 pb-5"
                     v-else
                >
                    You don't have any tables in your {{ selectedPlugin }} plugin.
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { assetUrl } from '../../utils/ninjatablesadmin';
import NinjaButton from '../../@ui-utils/NinjaButton.vue';
export default {
    name: 'Tools',
    components: {
        NinjaButton
    },
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
                'TablePress': 'TablePress',
                'supsystic': 'Data Tables Generator by Supsystic',
            },
            btnsLoading: {
                'TablePress': false,
            },
            showPluginModal: false,
            selectedPlugin: null,
            otherPluginTables: [],
            importing: false,
            uploadedFile: null,
            migrationTables: []
        }
    },
    methods: {
        assetUrl,
        clear() {
            this.uploadedFile = null;
            this.$refs.uploadRef.clearFiles();
        },
        getFileIcon(file) {
            const fileType = file.name.split('.').pop().toLowerCase();
            if (fileType === 'csv') {
                return this.assetUrl('icons/csv-file-ico.svg');
            } else if (fileType === 'json') {
                return this.assetUrl('icons/json-ico.svg');
            } else {
                return this.assetUrl('icons/default-file-icon.svg');
            }
        },

        handleChange(file) {
            this.clear();
            this.uploadedFile = file;
        },

        importTable() {
            this.btnLoading = true;
            // For now only execute when the import source is `file`
            if (!this.imports.source == 'file') {
                this.btnLoading = true;
                return;
            }

            let file = this.uploadedFile.raw;

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
                    if (response.message) {
                        this.$message.success(response.message);
                    }
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
                    this.$message.error(error.message);
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
                    console.log(response.tables);
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

                    this.$router.push({
                        name: 'data_items',
                        params: {table_id: response.data.tableId}
                    }).then(() => {
                        this.$nextTick(() => {
                            document.documentElement.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        });
                    });
                })
                .catch(error => {
                    this.$message.error(error.data.message);
                    this.importing = false;
                })
        },
        getMigrationTables() {
            if(window.ninja_table_admin.hasTablePress) {
                this.migrationTables.push({key:'TablePress', title: 'TablePress'});
            }
            if(window.ninja_table_admin.hasSupsystic) {
                this.migrationTables.push({key:'supsystic', title: 'Data Tables Generator by Supsystic'});
            }
        }
    },
    mounted() {
        if (this.$route.query.active_menu) {
            this.active_menu = this.$route.query.active_menu;
        }

        jQuery('.ninja_table_import_menu').on('click', () => {
            this.active_menu = 'import';
        });

        jQuery('.ninja_table_license_menu').on('click', () => {
            this.active_menu = 'license';
        });

        this.getMigrationTables();

    }
}
</script>git
