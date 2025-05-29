<template>
    <div>
        <div
            v-if="!tableId"
            :gutter="20"
            >
            <div class="mb-[30px]">
                <h3 class="nt-modal-title">{{ $t('Create a Drag & Drop Table') }}</h3>
                <p class="nt-modal-description">
                    {{ $t("Create tables fast using drag & drop table mode. Add columns and rows manually, import, or pick a template.") }}
                </p>
                <a class="nt-link" target="_blank" href="https://ninjatables.com/docs-category/simple-mode/">
                    {{ $t('Learn more about drag & drop table') }}
                </a>
            </div>
            <div class="form-group">
                <div class="font-semibold">{{ $t("Table Title") }}<span class="text-[#FB3748] ml-[2px]">*</span></div>
                <NinjaInput
                    style="width: 95%"
                    v-model="initialData.table_data.table_name"
                    type="text"
                    id="name"
                    class="form-control"
                    placeholder="Enter a title to identify your table"
                />
            </div>
            <el-row v-if="!newTable" class="new-table-wrapper">
                <el-col :span="12">
                    <div>
                        <div
                            class="new_table"
                            :style="`grid-template-rows:repeat(${initialData.table_data.table.tr},1fr);grid-template-columns:repeat(${initialData.table_data.table.tc},1fr)`"
                        >
                            <el-input-number
                                class="table-column-input"
                                :step="1"
                                v-model="initialData.table_data.table.tc"
                                :min="1"
                                :max="30"
                            ></el-input-number>
                            <el-input-number
                                class="table-row-input"
                                :step="1"
                                v-model="initialData.table_data.table.tr"
                                :min="1"
                                :max="30"
                            ></el-input-number>
                            <span
                                class="table-row-column"
                                v-for="r in tableRowColumn"
                                :key="r"
                            ></span>
                        </div>
                        <div class="create-button">
                            <NinjaButton
                                class="w-full"
                                @click="createTable('default')"
                                type="primary"
                                size="small"
                            >
                            <img :src="assetUrl('icons/add.svg')" alt="Create" />
                            {{$t('Create')}}
                            </NinjaButton>
                        </div>
                       
                    </div>
                </el-col>
                <el-col :span="12">
                    <div class="font-semibold">{{ $t('Import Table from CSV / JSON File') }}</div>
                    <div v-if="!file.name" class="mb-3 mt-1">
                        <NinjaInput :disabled="!hasPro" @mouseover.native="upgradeToPro"
                                  :placeholder="$t('Import CSV/JSON from URL')" v-model="url">
                        </NinjaInput>
                    </div>
                    <el-upload
                        v-if="!url"
                        accept=".csv, .json"
                        drag
                        action="#"
                        :limit="1"
                        :before-upload="beforeFileUpload"
                        :on-success="handleFileSuccess"
                        :on-remove="handleRemove"
                    >
                        <img class="mx-auto" :src="assetUrl('icons/upload-cloud-2-line.svg')"/>
                        <div class="el-upload__text">{{ $t('Drop file here or ') }}<span class="nt-link">{{ $t('click to upload') }}</span>
                        </div>
                    </el-upload>
                    <div class="import-button" v-if="(file.name || url)">
                        <NinjaButton
                            :loading="loading"
                            @click="importCJ"
                            type="primary"
                            size="small"
                        >Import
                        </NinjaButton>
                    </div>
                </el-col>
                <el-col :span="24">
                    <div class="ntb-choose-template">
                        <div>OR</div>
                        <a href="#ntb-templates"> {{ $t('Choose a Template') }}</a>
                    </div>
                </el-col>
            </el-row>

            <el-row v-if="!newTable" class="new-table-wrapper" id="ntb-templates">
                <el-col
                    :span="24"
                    v-for="(table, key) in initialData.ready_made_tables"
                    :key="key"
                >
                    <div class="table-type-heading mb-[20px]">
                        <h4 class="ready-made-name text-center text-lg font-semibold">{{ table.name }}</h4>
                    </div>
                    <el-row>
                        <el-col class="mb-[20px]" :span="8" v-for="(item, key) in table.tables" :key="key">
                            <div class="ready-made-table-image">
                                <img
                                    style="margin: 0 auto;width: 180px;display: flex;"
                                    :src="getAsset(item.key + '.jpg')"
                                    alt=""
                                />
                                <div class="ready-made-table-button">
                                    <NinjaButton
                                        size="small"
                                        @click="createTable(item.key, item)"
                                        type="primary"
                                    >
                                        <span>{{ (!hasPro && item.has_pro) ? 'Unlock in Pro' : 'Create' }}</span>
                                    </NinjaButton
                                    >
                                </div>
                            </div>

                            <h4 class="table-title-style">{{ item.name }}</h4>
                        </el-col>
                    </el-row>
                </el-col>
            </el-row>
        </div>
        <el-row v-else-if="tableId" :justify="tableAlignment === 'left' ? 'start' : 
           tableAlignment === 'right' ? 'end' : 'center'">
            <TableLayout
                @editItem="editItem"
                :initialData="initialData"
                :tableData="initialData.table_data"
                :setting="initialData.settings"
                :responsive="initialData.responsive"
                :selectedDevice="selectedDevice"
            ></TableLayout>
        </el-row>
    </div>
    
</template>
<script>
import draggable from "vuedraggable";
import TableLayout from "../Table/Layout.vue";
import {helpers} from "../Mixin/helpers";
import {UploadFilled} from "@element-plus/icons-vue";
import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";
import { assetUrl } from "../../../utils/ninjatablesadmin";

export default {
    name: "RightSideBar",
    props: ["initialData", "tableId", "selectedDevice"],
    mixins: [helpers],
    components: {
        UploadFilled,
        draggable,
        TableLayout,
        NinjaInput,
        NinjaButton
    },
    data() {
        return {
            newTable: false,
            value: "",
            file: {},
            loading: false,
            url: ''
        };
    },
    computed: {
        tableRowColumn() {
            return this.initialData.table_data.table.tr * this.initialData.table_data.table.tc;
        },
        hasPro() {
            return !!window.ninja_table_admin.hasPro;
        },
        tableAlignment() {
            return this.initialData?.settings?.general?.options?.table_alignment?.value;
        }
    },
    methods: {
        assetUrl,
        upgradeToPro() {
            if (!this.hasPro) {
                return this.upgradeMessage();
            }
        },
        importCJ() {
            if (this.url && !this.hasPro) {
                return this.upgradeMessage();
            }

            this.loading = true;
            let formData = new FormData();
            formData.append('file', this.file);
            formData.append('url', this.url);

            this.$post('import/table-builder', formData)
                .then(response => {
                    this.loading = false;
                    this.$message({
                        showClose: true,
                        message: this.$t("Imported successfully."),
                        type: "success",
                    });
                    this.$router.push({
                        name: "table_builder_edit_table",
                        params: {table_id: response.data.id},
                    });
                })
                .catch(error => {
                    this.failedMessage();
                });
        },
        handleRemove(file, fileList) {
            this.file = {};
        },
        handleFileSuccess(res, file) {
            this.file = file.raw;
        },
        beforeFileUpload(file) {
            const isCJ = file.type === 'text/csv' || file.type === 'application/json';
            const isLt1M = file.size / 1024 / 1024 < 1;

            if (!isCJ) {
                this.$message.error('File type must be CSV/JSON format!');
            }
            if (!isLt1M) {
                this.$message.error('File size can not exceed 1MB!');
            }
            return isCJ && isLt1M;
        },
        editItem(data) {
            this.$emit("editItem", data);
        },
        failedMessage() {
            return this.$message({
                showClose: true,
                message: this.$t("Something went wrong, please try again."),
                type: "warning",
            });
        },
        createTable(tableType, item = {}) {
            const type = tableType;
            if (!this.hasPro && item.has_pro) {
                return this.upgradeMessage();
            } else {
                if (this.initialData.table_data.table_name) {
                    if (type === "default") {
                        this.initialData.table_data.table_type = "";
                    } else {
                        this.initialData.table_data.table_type = type;
                    }
                    this.$post('table-builder', {
                        data: JSON.stringify(this.initialData),
                    }).then(response => {
                        this.$message({
                            showClose: true,
                            message: this.$t("Table created successfully."),
                            type: "success",
                        });
                        this.$router.push({
                            name: "table_builder_edit_table",
                            params: {table_id: response.data.id},
                        });
                    })
                        .catch((error) => {
                            this.failedMessage();
                        });
                } else {
                    this.failedMessage();
                }
            }
        },
        getAsset(path) {
            return window.ninja_table_admin.img_url + path;
        },
    },
};
</script>
<style lang="scss">
.new-table-wrapper {
    .table-type-heading {
        h4 {
            background-color: rgba(71, 108, 255, 0.1);
            text-align: center;
            padding: 10px;
            color: #335CFF;
            font-size: 16px;
            font-weight: 600;
        }
    }

    .table-title-style {
        display: table;
        margin: 5px auto 20px;
        font-size: 16px;
    }

    .ready-made-table-image {
        position: relative;

        img {
            opacity: 1;
            &:hover + .ready-made-table-button {
                opacity: 1; // Set opacity to 1 when hovering over the image
            }
        }
    }

    .ready-made-table-button {
        position: absolute;
        transition: 0.5s ease;
        opacity: 0;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        -ms-transform: translate(-50%, -50%);
        text-align: center;
        &:hover {
            opacity: 1;
        }
    }

    .new_table {
        border: 2px solid rgb(221, 221, 221);
        display: grid;
        position: relative;
        margin-top: 50px;
        width: 200px;
        height: 200px;
        margin-left: 40px;

        .table-column-input {
            position: absolute;
            top: -40px;
            width: 200px;
        }

        .table-row-input {
            left: -124px;
            position: absolute;
            transform: rotate(-90deg);
            top: 80px;
            width: 200px;
        }

        .table-row-column {
            border: 0.5px solid #ccc;
        }

    }

    .create-button {
        margin-top: 10px;
        width: 200px;
        left: 42px;
        position: relative;
    }

    .import-button {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ntb-choose-template {
        left: -42px;
        position: relative;
        text-align: center;
        margin: 10px 0;
    }
}

</style>
