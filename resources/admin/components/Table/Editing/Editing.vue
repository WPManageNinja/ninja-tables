<template>
    <div class="ninja-tables-frontend-editing">
        <div v-if="!hasPro || !config.table.isEditable"
             class="frontend-editing-conditional-content bg-white p-5 mt-5 rounded-[12px]">
            <h1 class="my-3">{{ $t('Frontend Editing Settings') }}</h1>
            <div v-if="!hasPro">
                <p class="my-3 text-sm inline-block">Frontend Editing is a pro only features. Please purchase <b>"Ninja
                    Tables Pro"</b> to use this feature.
                    Using this module, You can let your frontend users to add/edit/delete records based on user role.
                    Also, You can separate the records by user submission. </p>
                <p class="my-3 text-sm">Get Pro from here
                    <GetPro/>
                </p>
            </div>
            <div v-else-if="!config.table.isEditable">
                <p class="my-3 text-sm">This table can not be editable on frontend. Only "Default" data source tables
                    can be editable</p>
            </div>
        </div>
        <div v-else class="frontend-editing-collapse">
            <el-collapse v-model="activeCollapse" class="ninja-tables_rendering_accordion my-5" accordion>
                <el-collapse-item name="collapse1">
                    <template #title>
                        <div>
                            <el-switch
                                size="small"
                                v-model="settings.allow_frontend"
                                class="mr-2"
                                @click="(e)=>{
                                 activeCollapse = settings.allow_frontend === 'yes' ? ['collapse1'] : []
                                 e.stopPropagation();
                                 handleDisable(settings.allow_frontend)
                               }"
                                active-value="yes"
                                inactive-value="no"
                            />
                            <span style="font-weight: 400; font-size: 14px;">{{ $t('Enable Frontend Editing') }}</span>
                        </div>
                    </template>
                    <div class="my-5">
                        <div class="text-[18px] text-[500] my-2">{{ $t("User Roles and Data Editing Permissions") }}
                        </div>
                        <div class="mb-3 text-[14px]">
                            {{
                                $t("Please specific user roles to be able to edit/delete this table. Only selected user roles can edit/delete the data.")
                            }}
                        </div>

                        <div class="flex items-center">
                            <div class="">
                                <div class="text-[18px] text-[500] my-2">{{
                                        $t("User Roles for Edit/Add Table Rows")
                                    }}
                                </div>
                                <el-checkbox-group v-model="settings.user_roles_editing">
                                    <el-checkbox
                                        v-for="(role, role_key) in editing_user_roles"
                                        :key="role_key"
                                        :label="role"
                                        :value="role_key"
                                    />
                                </el-checkbox-group>
                            </div>
                            <div class="">
                                <div class="text-[18px] text-[500] my-2">{{
                                        $t("User Roles for Deleting Table Rows")
                                    }}
                                </div>
                                <el-checkbox-group v-model="settings.user_roles_deleting">
                                    <el-checkbox
                                        v-for="(role, role_key) in user_roles"
                                        :key="role_key"
                                        :label="role"
                                        :value="role_key"
                                    />
                                </el-checkbox-group>
                            </div>
                        </div>

                        <div class="text-[18px] text-[500] my-2">{{ $t("Own Data Only") }}</div>
                        <div class="mb-4 text-[14px]">
                            <el-checkbox
                                true-value="yes"
                                false-value="no"
                                v-model="settings.own_data_only"
                            >{{ $t('Users can see and edit/delete only own data') }}
                            </el-checkbox>
                        </div>

                        <div class="mb-5">
                            <div class="text-[18px] text-[500] my-2">{{
                                    $t("User Roles and Data Editing Permissions")
                                }}
                            </div>
                            <div class="mb-3 text-[14px] font-[400]">
                                {{
                                    $t("Please Specify which columns can be editable from front-end and also, You can specify which columns will be required")
                                }}
                            </div>
                            <div class="ninja-tables_table_edit">
                                <el-table border :data="columns" class="mb-5">
                                    <el-table-column :label="$t('Column Name')" prop="name"></el-table-column>
                                    <el-table-column :label="$t('Is Editable?')" prop="label">
                                        <template #default="scope">
                                            <el-switch
                                                v-model="editing_items[scope.row.key]"
                                                active-value="yes"
                                                inactive-value="no"
                                                size="small"
                                            ></el-switch>
                                        </template>
                                    </el-table-column>
                                    <el-table-column :label="$t('Is Required?')" prop="element_type">
                                        <template #default="scope">
                                            <el-switch
                                                v-model="required_items[scope.row.key]"
                                                active-value="yes"
                                                inactive-value="no"
                                                size="small"
                                            ></el-switch>
                                        </template>
                                    </el-table-column>
                                    <el-table-column :label="$t('Default Value')">
                                        <template #default="scope">
                                            <NinjaInput :placeholder="'Default Value for '+scope.row.key"
                                                        v-model="default_values[scope.row.key]"/>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>

                            <div>
                                <div class="text-[18px] text-[500] my-2">{{ $t('Appearance Settings') }}</div>
                                <div class="mb-3 text-[14px] font-[400]">
                                    {{ $t("You can set the Editing Component Labels and Appearances") }}
                                </div>
                                <div>
                                    <div class="mb-4 text-[14px]">
                                        <el-checkbox
                                            true-value="yes"
                                            false-value="no"
                                            v-model="appearance_settings.alwaysShow"
                                        >{{ $t('Always Show Edit Icons') }}
                                        </el-checkbox>
                                    </div>
                                    <div class="px-6 py-5 border-solid border border-[#E1E4EA] rounded-[10px]">
                                        <div class="grid grid-cols-2 gap-5">
                                            <div class="w-full">
                                                <div class="mb-2 text-[14px] font-[500]">{{
                                                        $t('Add Row Button Label')
                                                    }}
                                                </div>
                                                <NinjaInput v-model="appearance_settings.addText"/>
                                            </div>
                                            <div>
                                                <div class="mb-2 text-[14px] font-[500]"> {{
                                                        $t("Edit Row Button Label")
                                                    }}
                                                </div>
                                                <NinjaInput v-model="appearance_settings.showText"/>
                                            </div>

                                            <div>
                                                <div class="mb-2 text-[14px] font-[500]">{{
                                                        $t("Add Popup Heading")
                                                    }}
                                                </div>
                                                <NinjaInput v-model="appearance_settings.addModalLabel"/>
                                            </div>
                                            <div>
                                                <div class="mb-2 text-[14px] font-[500]">{{
                                                        $t("Edit Popup Heading")
                                                    }}
                                                </div>
                                                <NinjaInput v-model="appearance_settings.editModalLabel"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-[18px] text-[500] my-2">{{ $t("Set your action icon position") }}</div>
                                <div class="ninja_tables_radio_group my-2">
                                    <el-radio-group border v-model="appearance_settings.position">
                                        <el-radio border value="left">{{ $t('Left') }}</el-radio>
                                        <el-radio border value="right"> {{ $t('Right') }}</el-radio>
                                    </el-radio-group>
                                </div>

                                <div class="flex justify-end">
                                    <NinjaButton type="primary" @click="updateSettings" size="small"
                                                 :btn-text="$t('Save Settings')"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-collapse-item>
            </el-collapse>
        </div>
    </div>
</template>

<script>
import GetPro from "../../Tools/GetPro";
import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";

export default {
    name: 'frontend-editing-settings',
    components: {
        NinjaButton,
        NinjaInput,
        GetPro,
    },
    props: ['config'],
    data() {
        return {
            fetching: false,
            saving: false,
            tableId: this.config.table.ID,
            columns: this.config.columns,
            settings: {
                allow_frontend: 'no',
                user_roles_editing: [],
                user_roles_deleting: [],
            },
            editing_user_roles: {},
            user_roles: {},
            editing_items: {},
            required_items: {},
            default_values: {},
            appearance_settings: {},
            hasPro: !!window.ninja_table_admin.hasPro,
            isActivated: !!window.ninja_table_admin.activated_features.ninja_table_front_editor,
            activeCollapse: [],
        }
    },
    methods: {
        handleDisable(val) {
            val === 'no' && this.updateSettings();
        },
        initializeFlags(target, columns) {
            if (Object.keys(target).length === 0) {
                columns.forEach(column => {
                    target[column.key] = 'no';
                });
            }
        },
        initializeSettings() {
            if (Object.keys(this.editing_items).length === 0) {
                this.initializeFlags(this.editing_items, this.columns);
            }

            if (Object.keys(this.required_items).length === 0) {
                this.initializeFlags(this.required_items, this.columns);
            }

            if (this.appearance_settings && !this.appearance_settings.position) {
                this.appearance_settings.position = 'right';
            }
        },
        getEditSettings() {
            this.fetching = true;
            this.$get({
                action: 'ninja_table_pro_get_editing_settings',
                table_id: this.tableId
            })
                .then(response => {
                    this.settings = response.data.settings;
                    if (this.settings.allow_frontend === 'yes') {
                        this.activeCollapse = ['collapse1']
                    }

                    this.user_roles = response.data.user_roles;
                    this.editing_user_roles = response.data.editing_user_roles;
                    this.editing_items = response.data.editor_pref.editing_items;
                    this.required_items = response.data.editor_pref.required_items;
                    this.default_values = response.data.editor_pref.default_values;
                    this.appearance_settings = response.data.editor_pref.appearance_settings;

                    this.initializeSettings();
                })
                .fail(error => {

                })
                .always(() => {
                    this.fetching = false;
                });
        },
        updateSettings() {
            this.saving = true;
            const data = {
                action: 'ninja_table_pro_update_editing_settings',
                table_id: this.tableId,
                settings: this.settings,
                editing_items: this.editing_items,
                required_items: this.required_items,
                default_values: this.default_values,
                appearance_settings: this.appearance_settings,
            };
            this.$post(data)
                .then(response => {
                    this.$message({
                        type: 'success',
                        message: response.data.message
                    });
                })
                .fail(error => {
                    if (error.responseJSON && error.responseJSON.data) {
                        this.$message({
                            type: 'error',
                            message: error.responseJSON.data.message
                        });
                    } else {
                        this.$message({
                            type: 'error',
                            message: 'Something is wrong! Please try again'
                        });
                    }
                })
                .always(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        this.getEditSettings();
    }
}
</script>
