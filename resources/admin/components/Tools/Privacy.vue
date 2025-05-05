<template>
    <div class="privacy">
        <div class="ninja_header">
            <h2>Permission <span v-show="!hasPro">(Pro Feature)</span></h2>
        </div>

        <div v-loading="fetching" class="ninja_content">
            <div class="ninja_block">
                <p>By default, Only Administrator have access to manage the tables. By selecting additional roles bellow, You can give access to manage your Tables to other user roles.</p>
            </div>
            <hr />
            <template v-if="hasPro">
                <div class="form-group">
                    <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange">
                        {{ $t('Check all') }}
                    </el-checkbox>
                </div>

                <div class="form-group">
                    <el-checkbox-group v-model="capability" @change="handleCheckedCapabilitiesChange">
                        <el-checkbox v-for="role in roles" :value="role.key" :key="role.key">
                            {{ role.name }}
                        </el-checkbox>
                    </el-checkbox-group>
                </div>

                <div v-if="capability && capability.length" class="form-group">
                    <el-checkbox
                        :true-value="'yes'"
                        :false-value="'no'"
                        v-model="sql_permission">
                        Enable SQL-Module Permission for selected user roles
                    </el-checkbox>
                </div>

                <div class="form-group">
                    <el-button @click="store" type="primary" size="small">Save</el-button>
                </div>
            </template>

            <template v-else>
                 {{ $t('Activate Ninja Tables Pro Add-on plugin to unlock this feature') }}
               <p>
                 <get-pro/>
               </p>
            </template>
        </div>
    </div>
</template>

<script>
    import GetPro from "./GetPro";
    export default {
        name: "Privacy",
      components: {GetPro},
      data() {
            return {
                hasPro: false,
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
            this.hasPro = window.ninja_table_admin.hasPro === true;
            this.get();
        }
    };
</script>

<style>
    .el-text-info {
        color: #58b7ff;
    }

    .privacy label {
        margin-bottom: initial;
    }

    #capability {
        margin-left: 75px;
    }
</style>


