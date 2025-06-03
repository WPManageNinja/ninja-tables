<template>
    <div class="ninja-tables-frontend-editing">
        <div v-if="!hasPro || !config.table.isEditable"
             class="frontend-editing-conditional-content bg-white p-5 rounded-[12px] my-3">

            <div v-if="!hasPro" class="nt-instruction w-full overflow-hidden text-center p-[30px] mb-1">
                <h1 class="my-3">{{ $t('Frontend Editing Settings') }}</h1>
                <p class="text-[14px] font-[400] text-[#525866]">
                    {{ $t('Frontend Editing is a pro only features. Please purchase') }} <b>"Ninja Tables Pro"</b> {{ $t('to use this feature.') }} <br>
                    {{ $t('Using this module, you can let your frontend users to add/edit/delete records based on user role. Also, you can separate the records by user submission.') }} </p>
                <div class="flex justify-center my-4">
                    <a v-if="!hasPro"
                       href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade"
                       target="_blank">
                        <NinjaButton
                            type="pro"
                            :icon="assetUrl('icons/get-pro.svg')"
                            :btnText="$t('Get Pro')"
                        />
                    </a>
                </div>
            </div>

            <div v-else-if="!config.table.isEditable" class="nt-instruction text-center my-[2px]">
                <h3 class="nt-modal-subtitle mb-3">{{ $t('Frontend Editing Settings') }}</h3>
                <p class="nt-modal-description">{{ $t(`This table can not be editable on frontend. Only "Default" data source tables can be editable`) }}</p>
            </div>
        </div>
        <div v-else class="frontend-editing-collapse">
            <el-collapse v-model="activeCollapse" class="ninja-tables_rendering_accordion my-5" accordion>
                <el-collapse-item name="collapse1">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <el-switch
                                size="small"
                                v-model="settings.allow_frontend"
                                @click="(e)=>{
                                 activeCollapse = settings.allow_frontend === 'yes' ? ['collapse1'] : []
                                 e.stopPropagation();
                                 handleDisable(settings.allow_frontend)
                               }"
                                active-value="yes"
                                inactive-value="no"
                            />
                            <span class="font-[400] text-[14px]">{{ $t('Enable Frontend Editing') }}</span>
                        </div>
                    </template>
                    <div class="my-5">
                        <div class="text-[18px] text-[500] my-2">{{ $t("User Roles and Data Editing Permissions") }}
                        </div>
                        <div class="mb-3 text-[14px]">
                            {{
                                $t("Please specify user roles to be able to edit/delete this table. Only selected user roles can edit/delete the data.")
                            }}
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <div class="nt-checkbox-group-wrapper">
                                    <div class="nt-checkbox-group-header"
                                         style="border-bottom: 1px solid #E1E4EA">
                                        <div>{{ $t('User Roles for Edit/Add Table Rows') }}</div>
                                        <div>
                                            <el-checkbox
                                                v-model="editRolesCheckAll"
                                                :indeterminate="editRolesIndeterminate"
                                                @change="handleEditRolesCheckAllChange"
                                            >
                                                {{ $t('Select All') }}
                                            </el-checkbox>
                                        </div>
                                    </div>
                                    <div class="p-4 rounded-b-[8px] bg-white">
                                        <el-checkbox-group
                                            v-model="settings.user_roles_editing"
                                            @change="handleEditRolesChange"
                                            class="nt-checkbox-group">
                                            <el-checkbox
                                                v-for="(role, role_key) in editing_user_roles"
                                                :key="role_key"
                                                :value="role_key"
                                                style="font-weight: 300"
                                            >
                                                {{ role }}
                                            </el-checkbox>
                                        </el-checkbox-group>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="nt-checkbox-group-wrapper">
                                    <div class="nt-checkbox-group-header"
                                         style="border-bottom: 1px solid #E1E4EA">
                                        <div>{{ $t('User Roles for Deleting Table Rows') }}</div>
                                        <div>
                                            <el-checkbox
                                                v-model="deleteRolesCheckAll"
                                                :indeterminate="deleteRolesIndeterminate"
                                                @change="handleDeleteRolesCheckAllChange"
                                            >
                                                {{ $t('Select All') }}
                                            </el-checkbox>
                                        </div>
                                    </div>
                                    <div class="p-4 rounded-b-[8px] bg-white">
                                        <el-checkbox-group 
                                            v-model="settings.user_roles_deleting"
                                            @change="handleDeleteRolesChange"
                                            class="nt-checkbox-group">
                                            <el-checkbox
                                                v-for="(role, role_key) in user_roles"
                                                :key="role_key"
                                                :value="role_key"
                                                style="font-weight: 300"
                                            >
                                                {{ role }}
                                            </el-checkbox>
                                        </el-checkbox-group>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-[18px] text-[500] my-2">{{ $t("Own Data Only") }}</div>
                        <div class="mb-2 text-[14px]">
                            <el-checkbox
                                true-value="yes"
                                false-value="no"
                                v-model="settings.own_data_only"
                            >{{ $t('Users can see and edit/delete only own data') }}
                            </el-checkbox>
                        </div>

                        <div v-show="settings.own_data_only === 'yes'" class="bg-[#EBF1FF] p-4 mb-4 rounded-[8px]">
                            {{ $t(`Your Selected user roles only see their own data and manage those data. Other user roles can not see any data. If you want to show all the data without editing tools to all users, you can use the following shortcode:`) }}
                            <br /><pre class="mt-1"><b>[ninja_tables disable_edit="yes" id="{{ tableId }}"]</b></pre>
                        </div>

                        <div class="mb-5">
                            <div class="text-[18px] text-[500] my-2">{{
                                    $t("User Roles and Data Editing Permissions")
                                }}
                            </div>
                            <div class="mb-3 text-[14px] font-[400]">
                                {{
                                    $t("Please specify which columns should be editable from the front-end. You can also define which columns are required.")
                                }}
                            </div>
                            <div class="ninja-tables_table_edit">
                                <el-table border :data="columns" class="mb-5 nt-inner-table">
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
                                    <NinjaButton @click="updateSettings" :btn-text="$t('Save Settings')" />
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
import {assetUrl} from "../../../utils/ninjatablesadmin";

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
            editRolesCheckAll: false,
            editRolesIndeterminate: false,
            deleteRolesCheckAll: false,
            deleteRolesIndeterminate: false,
        }
    },
    methods: {
        assetUrl,
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
        },
        handleEditRolesCheckAllChange(val) {
            this.settings.user_roles_editing = val ? Object.keys(this.editing_user_roles) : [];
            this.editRolesIndeterminate = false;
        },
        handleEditRolesChange(value) {
            let checkedCount = value.length;
            this.editRolesCheckAll = checkedCount === Object.keys(this.editing_user_roles).length;
            this.editRolesIndeterminate = checkedCount > 0 && checkedCount < Object.keys(this.editing_user_roles).length;
        },
        handleDeleteRolesCheckAllChange(val) {
            this.settings.user_roles_deleting = val ? Object.keys(this.user_roles) : [];
            this.deleteRolesIndeterminate = false;
        },
        handleDeleteRolesChange(value) {
            let checkedCount = value.length;
            this.deleteRolesCheckAll = checkedCount === Object.keys(this.user_roles).length;
            this.deleteRolesIndeterminate = checkedCount > 0 && checkedCount < Object.keys(this.user_roles).length;
        },
    },
    mounted() {
        this.getEditSettings();
    }
}
</script>
