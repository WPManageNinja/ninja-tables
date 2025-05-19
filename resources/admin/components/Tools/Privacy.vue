<template>
    <div class="nt-table-permission-content">
        <div class="nt-permission-header">
            <div class="text-[18px] font-[600] text-[#0E121B]">
                {{ $t('Permission') }} <span v-show="!hasPro">(Pro Feature)</span>
            </div>
            <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
                    {{$t(`By default, Only Administrator have access to manage the tables. By selecting additional roles bellow, You can give access to manage your Tables to other user roles.`) }}
            </div>
        </div>

        <div class="nt-permission-body">
            <div v-if="hasPro">
                <div class="border border-solid border-[#E1E4EA] rounded-[8px] mb-4">
                    <div class="bg-[#F9FAFB] flex justify-between items-center px-4 py-2 rounded-t-[8px]"
                         style="border-bottom: 1px solid #E1E4EA">
                        <div>Select</div>
                        <div>
                            <el-checkbox v-model="checkAll" :indeterminate="isIndeterminate" @change="handleCheckAllChange">
                                {{ $t('Select All') }}
                            </el-checkbox>
                        </div>
                    </div>
                    <div class="p-4">
                        <el-checkbox-group v-model="capability" @change="handleCheckedCapabilitiesChange">
                            <el-checkbox style="font-weight: 300" v-for="role in roles" :key="role.key" :value="role.key">
                                {{ role.name }}
                            </el-checkbox>
                        </el-checkbox-group>

                        <el-checkbox
                            v-if="capability && capability.length"
                            :true-value="'yes'"
                            :false-value="'no'"
                            v-model="sql_permission">
                            {{ $t('Enable SQL-Module Permission for selected user roles') }}
                        </el-checkbox>
                    </div>
                </div>

                <div>
                    <NinjaButton type="primary" size="small" @click="store" :btn-text="$t('Save')" />
                </div>
            </div>

            <div v-else>
                {{ $t('Activate Ninja Tables Pro Add-on plugin to unlock this feature') }}
                <p>
                    <GetPro />
                </p>
            </div>
        </div>
    </div>
</template>

<script>
    import GetPro from "./GetPro";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";
    export default {
        name: "Privacy",
      components: {NinjaButton, GetPro},
      data() {
            return {
                hasPro: window.ninja_table_admin.hasPro === true || window.ninja_table_admin.hasPro === '1',
                fetching: false,
                roles: [],
                checkAll: false,
                sql_permission: 'no',
                capability: ["administrator"],
                isIndeterminate: false,
                upgrade: `https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade`
            };
        },
        methods: {
            get() {
                this.fetching = true;
                this.$get('tables/tools/permission')
                    .then(response => {
                        this.capability = response.capability;
                        this.roles = response.roles;
                        this.sql_permission = response.sql_permission;
                        this.handleCheckedCapabilitiesChange(this.capability);
                    })
                    .catch(e => {})
                this.fetching = false;
            },
            store() {
                let data = {
                    capability: this.capability,
                    sql_permission: this.sql_permission
                };
                this.$post('pro/permission', data)
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.message,
                            type: "success"
                        });
                    })
                    .catch(error => {
                        this.$message({
                            showClose: true,
                            message: error.message,
                            type: "error"
                        });
                    });
            },
            handleCheckAllChange(val) {
                this.capability = val ? this.roles.map(item => item.key) : [];
                this.isIndeterminate = false;
            },
            handleCheckedCapabilitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.roles.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.roles.length;
            }
        },
        mounted() {
            this.get();
        }
    };
</script>


