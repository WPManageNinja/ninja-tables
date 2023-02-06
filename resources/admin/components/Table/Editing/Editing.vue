<template>
    <div class="table_editing">
        <div v-if="!hasPro">
            <div class="el-main-editing">
                <div class="ninja_header_editing">
                    <h2 class="">
                        Frontend Editing Settings
                    </h2>
                </div>
                <div class="editing_body text-center">
                    <h3>Frontend Editing is a pro only features. Please purchase <b>"Ninja Tables Pro"</b> to use this feature</h3>
                    <p>Using this module, You can let your frontend users to add/edit/delete records based on user role. Also, You can separate the records by user submission</p>
                    <get-pro/>
                </div>
            </div>
        </div>
        <div v-else-if="!isActivated">
            <h3>
                Custom Filters is introduced in version 3.2.0. Please update <b>Ninja tables pro</b> plugin to use
                this feature
            </h3>
        </div>
        <div v-else-if="!config.table.isEditable">
            <h3>This table can not be editable on frontend</h3>
            <p>Only "Default" data source tables can be editable</p>
        </div>
        <div v-else v-loading="fetching" class="el-main-editing">
            <div class="ninja_header_editing">
                <h2>
                    Frontend Editing Settings
                </h2>
                <div class="heading_actions">
                    <el-button @click="updateSettings()" size="small" type="primary">Update Settings</el-button>
                </div>
            </div>
            <div class="editing_body">
                <div class="editing_sub_section">
                    <el-checkbox true-label="yes" false-label="no" v-model="settings.allow_frontend">
                        Enable Frontend editing
                        <el-tooltip placement="top-start" effect="light" content="Allow editing table from the frontend">
                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </el-checkbox>
                </div>
                <template v-if="settings.allow_frontend == 'yes'">
                    <div class="editing_sub_section">
                        <div class="ninja_section_block_header">
                            <h3>Data Editing Permissions</h3>
                            <p>
                                Please specific user roles to be able to edit/delete this table. Only selected user
                                roles
                                can edit/delete the data.
                            </p>
                        </div>
                        <div class="form_row_full">
                            <div class="form_group form_row_half">
                                <label>
                                    User Roles for Edit/Add Table Rows
                                    <el-tooltip placement="top-start" effect="light" content="Your selected user roles can edit this table rows from frontend. Please note, Adminstrators will have this access by default">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-checkbox-group v-model="settings.user_roles_editing">
                                    <el-checkbox v-for="(role, role_key) in editing_user_roles" :label="role_key"
                                                 :key="role_key">
                                        {{role}}
                                    </el-checkbox>
                                </el-checkbox-group>
                            </div>
                            <div class="form_group form_row_half">
                                <label>
                                    User Roles for Deleting Table Rows
                                    <el-tooltip placement="top-start" effect="light" content="Your selected user roles can delete this table rows from frontend. Please note, Adminstrators will have this access by default">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-checkbox-group v-model="settings.user_roles_deleting">
                                    <el-checkbox v-for="(role, role_key) in user_roles" :label="role_key"
                                                 :key="role_key">
                                        {{role}}
                                    </el-checkbox>
                                </el-checkbox-group>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                Own Data Only
                                <el-tooltip placement="top-start" effect="light" content="If this is enabled, users will see and edit only the rows that were created by them">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                            <div class="form-group">
                                <el-checkbox true-label="yes" false-label="no" v-model="settings.own_data_only">
                                    Users can see and edit/delete only own data
                                </el-checkbox>
                            </div>
                            <div style="line-height: 120%" v-show="settings.own_data_only == 'yes'">
                                Your Selected user roles only see their own data and manage those data. Other user roles can not see any data. If you want to show all the data without editing tools to all users, you can use the following shortcode:
                                <br /><pre><b>[ninja_tables disable_edit="yes" id="{{ tableId }}"]</b></pre>
                            </div>
                        </div>
                    </div>
                    <div class="editing_sub_section">
                        <div class="ninja_section_block_header">
                            <h3>Editing Column Options</h3>
                            <p>
                                Please Specify which columns can be editable from front-end and also, You can specify
                                which
                                columns will be required
                            </p>
                        </div>

                        <div class="ninja_editing_pref">
                            <table class="wp-list-table ninja_editing_table widefat fixed striped">
                                <thead>
                                <tr>
                                    <th>Column Name</th>
                                    <th>
                                        Editable?
                                        <el-tooltip placement="top-start" effect="light" content="Select the columns that you need to be editable from frontend">
                                            <i class="el-icon-info el-text-info"></i>
                                        </el-tooltip>
                                    </th>
                                    <th>
                                        Required?
                                        <el-tooltip placement="top-start" effect="light" content="Select the columns that you need to be required from frontend">
                                            <i class="el-icon-info el-text-info"></i>
                                        </el-tooltip>
                                    </th>
                                    <th>
                                        Default Value
                                        <el-tooltip placement="top-start" effect="light" content="If you would like to have some values pre-defined in editors (i.e. default editor values) please enter these here.">
                                            <i class="el-icon-info el-text-info"></i>
                                        </el-tooltip>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="column in columns" :key="column.key">
                                    <th>{{ column.name }}</th>
                                    <td>
                                        <el-switch active-value="yes" inactive-value="no"
                                                   v-model="editing_items[column.key]"></el-switch>
                                    </td>
                                    <td>
                                        <el-switch active-value="yes" inactive-value="no"
                                                   v-model="required_items[column.key]"></el-switch>
                                    </td>
                                    <td>
                                        <el-input :placeholder="'Default Value for '+column.name" size="mini"
                                                  v-model="default_values[column.key]"></el-input>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="editing_sub_section">
                        <div class="ninja_section_block_header">
                            <h3>Appearance Settings</h3>
                            <p>
                                You can set the Editing Component Labels and Appearances
                            </p>
                        </div>

                        <div class="form-group">
                            <el-checkbox true-label="yes" false-label="no" v-model="appearance_settings.alwaysShow">
                                Always Show Edit Icons
                                <el-tooltip placement="top-start" effect="light" content="If you enable this then, Selected user roles can always see the edit buttons, Otherwise they will see a button to initialize editing">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </el-checkbox>
                        </div>


                        <div class="form_row_full">
                            <div class="form_group form_row_half">
                                <label>
                                    Add Row Button Label
                                    <el-tooltip placement="top-start" effect="light" content="Button label for Add New Data Default: 'New row'">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input size="mini" placeholder="eg: New row" v-model="appearance_settings.addText"></el-input>
                            </div>
                            <div class="form_group form_row_half">
                                <label>
                                    Edit Rows Button Label
                                    <el-tooltip placement="top-start" effect="light" content="Button label for Edit Rows Default: 'Edit rows'">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input size="mini" placeholder="eg: Edit rows" v-model="appearance_settings.showText"></el-input>
                            </div>
                        </div>

                        <div class="form_row_full">
                            <div class="form_group form_row_half">
                                <label>
                                    Add Popup Heading
                                    <el-tooltip placement="top-start" effect="light" content="Title for popup heading for adding new data">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input size="mini" placeholder="eg: Add Data" v-model="appearance_settings.addModalLabel"></el-input>
                            </div>
                            <div class="form_group form_row_half">
                                <label>
                                    Edit Popup Heading
                                    <el-tooltip placement="top-start" effect="light" content="Title for popup heading for editing existing data">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </label>
                                <el-input size="mini" placeholder="eg: Edit Data" v-model="appearance_settings.editModalLabel"></el-input>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                Editor Icon Position
                                <el-tooltip placement="top-start" effect="light" content="Edit icon postion. If you select Right then it will append the edit icons at the last column otherwise at the first column">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                            <br/>
                            <el-radio-group size="mini" v-model="appearance_settings.position">
                                <el-radio-button label="left">Left</el-radio-button>
                                <el-radio-button label="right">Right</el-radio-button>
                            </el-radio-group>
                        </div>
                    </div>
                </template>
                <div style="text-align: right" class="form_group">
                    <el-button @click="updateSettings()" size="small" type="primary">Update Settings</el-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import GetPro from "../../Tools/GetPro";
    export default {
        name: 'frontend-editing-settings',
      components: {GetPro},
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
                isActivated: !!window.ninja_table_admin.activated_features.ninja_table_front_editor
            }
        },
        methods: {
            getEditSettings() {
                this.fetching = true;
                this.$get({
                    action: 'ninja_table_pro_get_editing_settings',
                    table_id: this.tableId
                })
                    .then(response => {
                        this.settings = response.data.settings;
                        this.user_roles = response.data.user_roles;
                        this.editing_user_roles = response.data.editing_user_roles;
                        this.editing_items = response.data.editor_pref.editing_items;
                        this.required_items = response.data.editor_pref.required_items;
                        this.default_values = response.data.editor_pref.default_values;
                        this.appearance_settings = response.data.editor_pref.appearance_settings;
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
                        if(error.responseJSON && error.responseJSON.data) {
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
