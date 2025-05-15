<template>
    <el-dialog
        class="ninja_create-table-modal"
        :title="title"
        v-model="showModal"
        top="50px"
        :close-on-click-modal="false"
        :append-to-body="true"
        @close="closeModal"
    >
        <div class="ninja_modal-body p-[20px]">
            <div v-if="showModal">
                <div v-for="column in columns" :key="column.key" class="form-group">
                    <label :for="slugify(column.key)" class="nt-form-label">{{ column.name || column.key }}</label>
                    <div v-if="column.data_type === 'textarea'">
                        <el-input
                            type="textarea"
                            v-model="newColumn[column.key]"
                            :placeholder="column.name"
                            :id="slugify(column.key)"
                            :autosize="{ minRows: 4, maxRows: 8}"
                        />
                    </div>
                    <div v-else-if="column.data_type === 'html'">
                        <wp_editor :editor_id="slugify(column.key)" v-model="newColumn[column.key]"></wp_editor>
                    </div>
                    <div v-else-if="column.data_type === 'date'">
                        <ninja-date-picker :column="column" :new_column="newColumn"></ninja-date-picker>
                    </div>
                    <div v-else-if="column.data_type === 'selection'">
                        <may-be-select :column="column" :newColumn="newColumn"></may-be-select>
                    </div>

                    <div v-else-if="column.data_type === 'image' && has_pro">
                        <image-selector :adding_counter="adding_counter" :column="column" :newColumn="newColumn"></image-selector>
                    </div>
                    <div v-else-if="column.data_type === 'button' && has_pro">
<!--                        <input-->
<!--                            placeholder="Valid button URL"-->
<!--                            type="url" :id="slugify(column.key)"-->
<!--                            class="form-control"-->
<!--                            v-model="newColumn[column.key]"-->
<!--                        >-->
                        <NinjaInput
                            placeholder="Valid button URL"
                            type="url"
                            :id="slugify(column.key)"
                            class="form-control"
                            v-model="newColumn[column.key]"
                        />
                        <small>{{ $t('Please provide valid URL') }}</small>
                    </div>

                    <div v-else>
                        <NinjaInput
                            :placeholder="column.name"
                            :id="slugify(column.key)"
                            v-model="newColumn[column.key]"
                        />
                    </div>
                </div>
                <div v-if="!editId && manualSort && !insertAfterPosition" class="flex items-center gap-2">
                    <label class="nt-form-label">{{ $t('Add at') }}</label>
                    <el-radio-group
                        v-model="position"
                        class="ninja_tables_radio_group"
                    >
                        <el-radio value="last" border>Last</el-radio>
                        <el-radio value="first" border>First</el-radio>
                    </el-radio-group>
                </div>
            </div>

        <div class="row_config_container" v-if="row_config">
            <template v-if="has_pro">
                <h3>Row Settings</h3>
                <div class="form_row_full">
                    <div class="form-group form_row_half">
                        <label>Row Background Color</label>
                        <el-color-picker v-model="item_settings.row_bg" show-alpha></el-color-picker>
                    </div>
                    <div class="form-group form_row_half">
                        <label>Row Text Color</label>
                        <el-color-picker v-model="item_settings.text_color" show-alpha></el-color-picker>
                    </div>
                </div>
                <h3>Cell Color Customization</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <th>Column</th>
                        <th>Background Color</th>
                        <th>Text Color</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="column in columns" :key="column.key">
                        <td>{{ column.name }}</td>
                        <td>
                            <el-color-picker v-model="item_settings.cell[column.key]['background-color']"
                                             show-alpha></el-color-picker>
                        </td>
                        <td>
                            <el-color-picker v-model="item_settings.cell[column.key]['color']"
                                             show-alpha></el-color-picker>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div v-if="!insertAfterPosition" style="margin-top: 20px" class="form-group">
                    <label>
                        Data Create Date
                        <el-tooltip placement="top-start" effect="light">
                            <span slot="content">
                              <h3>Create Date</h3>
                              Set the date of the data creation.<br>
                              This is useful when you want to sort the data by date
                            </span>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </label>
                    <el-date-picker
                        v-model="created_at"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        type="datetime"
                        placeholder="Select date and time">
                    </el-date-picker>
                </div>
            </template>
            <template v-else>
                <h3>Row and Cell Color Customization</h3>
                <p>Using this module, You can set cell and row level colors of your data, It's a pro feature, Please
                    purchase pro to unlock this feature</p>
               <get-pro/>
            </template>
        </div>

            <div class="pt-[16px] flex flex-col gap-2" :class="{ 'single-child': shouldNotContinueAdding }">
                <div v-if="!shouldNotContinueAdding">
                    <el-checkbox v-model="continueAdding">{{ $t('Continue Adding') }}</el-checkbox>
                </div>

                <div class="flex justify-between items-center">
                    <div @click="row_config = !row_config"
                         class="mr-3 cursor-pointer flex items-center px-2 py-[7px] bg-white border-[#D6DAE1] border-solid border rounded-[8px]">
                        <img :src="assetUrl('icons/setting-02.svg')" alt="settings"/>
                    </div>

                    <div class="flex gap-2 items-center">
                        <NinjaButton
                            size="small"
                            @click="closeModal"
                            type="secondary"
                            :btn-text="$t('Cancel')"
                        />

                        <NinjaButton
                            v-if="editId"
                            size="small"
                            @click="addData"
                            :disabled="btnLoading"
                            :btn-text="$t('Update')"
                        />
                        <NinjaButton
                            v-else
                            size="small"
                            @click="addData"
                            :disabled="btnLoading"
                            :btn-text="$t('Add')"
                        />
                    </div>

                </div>
            </div>
        </div>

    </el-dialog>
</template>

<script type="text/babel">
    import each from 'lodash/each';

    import wp_editor from '../../../common/_wp_editor';
    import mayBeSelect from '../../../common/_maybeMultiSelect';
    import ImageSelector from '../../../common/ImageSelector';
    import NinjaDatePicker from '../Extras/_NinjaDatePicker'
    import GetPro from "../Tools/GetPro";
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import {assetUrl} from "../../utils/ninjatablesadmin";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'add_data',
        props: ['title', 'show', 'columns', 'table_id', 'item', 'manualSort', 'insertAfterPosition', 'insertAfterId', 'type'],
        data() {
            return {
                showModal: this.show,
                row_config: false,
                editorOption: {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike', 'link'],        // toggled buttons
                            ['blockquote', 'code-block'],
                            [{'header': 1}, {'header': 2}],               // custom button values
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            [{'script': 'sub'}, {'script': 'super'}],      // superscript/subscript
                            [{'align': []}],
                            [{'direction': 'rtl'}]
                        ]
                    }
                },
                btnLoading: false,
                continueAdding: true,
                newColumn: {},
                has_pro: !!window.ninja_table_admin.hasPro,
                position: 'last',
                modal_title: 'Add Row',
                item_settings: {},
                created_at: '',
                adding_counter: 1
            }
        },
        computed: {
            editId() {
                return this.item && this.item.id;
            },
            shouldNotContinueAdding() {
                return !!(this.editId || this.type === 'duplicate');
            }
        },
        watch: {
            item() {
                this.initNewColumnObj();
                if (!this.item) {
                    this.modal_title = 'Add Row'
                }
            }
        },
        methods: {
            assetUrl,
            addData() {
                let valid = false;
                each(this.newColumn, (value) => {
                    if (value) {
                        valid = true;
                    }
                });
                if (!valid) {
                    this.$message({
                        showClose: true,
                        message: 'Please add at least 1 value to the row',
                        type: 'error'
                    });
                    return;
                }
                this.btnLoading = true;

                let data = {
                    table_id: this.table_id,
                    row: this.newColumn,
                    id: this.editId,
                    created_at: this.created_at,
                    settings: this.item_settings,
                    position: this.position
                };
                if (this.insertAfterId) {
                    data['insert_after_id'] = this.insertAfterId;
                    data['position'] = parseInt(this.insertAfterPosition) + 1;
                }

                // check it
                this.$post('tables/'+this.table_id+'/item', data)
                    .then((response) => {
                        if (!response.item) {
                            this.$message({
                                showClose: true,
                                message: 'Failed to add/update data. Please reload this page and try again',
                                type: 'error'
                            });
                            return;
                        }

                        this.$message({
                            showClose: true,
                            message: response.message,
                            type: 'success'
                        });
                        this.initNewColumnObj();
                        if (this.editId) {
                            this.$emit('updateItem', response.item);
                        } else {
                            let position = data.position;
                            if (!this.manualSort && !this.insertAfterId) {
                                position = 'last-first';
                            }
                            this.$emit('createItem', response.item, position);
                        }
                        if (this.editId || !this.continueAdding) {
                            this.$emit('modal_close');
                        }
                        if (this.type === 'duplicate') {
                            this.$emit('modal_close');
                        }

                        this.adding_counter++;
                    })
                    .catch((error) => {
                        this.$message({
                            showClose: true,
                            message: error.responseJSON.data.message,
                            type: 'error'
                        });
                    })
                   this.btnLoading = false;
            },
            closeModal() {
                this.$emit('modal_close');
            },
            initNewColumnObj() {
                let columnObj = {};
                each(this.columns, (column) => {
                    if (this.item && this.item.values[column.key]) {
                        columnObj[column.key] = this.item.values[column.key];
                    } else if (column.data_type == 'selection' && column.isMultiple == 'yes') {
                        columnObj[column.key] = [];
                    } else {
                        columnObj[column.key] = '';
                    }
                });
                this.newColumn = columnObj;
                this.initItemSettings();
            },
            initItemSettings() {
                if (this.item && this.item.settings) {
                    this.item_settings = this.item.settings;
                }
                if (!this.item_settings.cell) {
                    this.item_settings.cell = {};
                }
                each(this.columns, (column) => {
                    if (!this.item_settings.cell[column.key]) {
                        this.item_settings.cell[column.key] = {};
                    }
                });
                if (this.item) {
                    this.created_at = this.item.created_at;
                }
            },
            onEditorChange(key, {editor, html, text}) {
                this.newColumn[key] = html;
            },
            slugify(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-')           // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                    .replace(/^-+/, '')             // Trim - from start of text
                    .replace(/-+$/, '');            // Trim - from end of text
            },
            getFromSelectionStr(str) {
                if (str) {
                    return str.split("\n");
                }
                return [];
            }
        },
        mounted() {
            this.initNewColumnObj();
        },
        components: {
            NinjaButton,
            NinjaInput,
          GetPro,
            wp_editor: wp_editor,
            NinjaDatePicker,
            mayBeSelect,
            ImageSelector
        }
    }
</script>

<style lang="scss">
    .dialog-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        &.single-child {
            justify-content: flex-end;
        }
    }

    .row_config_container {
        display: block;
        padding: 10px 15px;
        background: rgb(216, 237, 253);
        position: relative;
        border-radius: 5px;
    }
</style>
