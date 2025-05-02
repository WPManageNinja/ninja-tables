<template>
    <el-dialog
            v-if="leadVisible"
            :visible.sync="leadVisible"
            title="We made a few tweaks to Ninja Tables"
    >
        <div v-loading="loading" class="ninja_permission_wrapper">
            <p>Hey {{display_name}},<br/>
                Never miss an important update - opt in to our security & feature updates notifications. We will never
                spam / share your data, We will only send emails about important updates</p>
            <el-button @click="optin('yes')" type="success">Opt-in and Continue</el-button>
            <el-button @click="optin('no')" class="pull-right" size="small">Skip</el-button>

            <div class="ninja_permissions">
                <a @click.prevent="showPermission = !showPermission" href="#">What permissions are being granted?</a>
                <p v-show="showPermission" class="permissions">
                    Name, email, Site URL, Plugins info, ip Address and uninstall event
                </p>
            </div>
        </div>
    </el-dialog>
</template>

<script type="text/babel">

    export default {
        name: 'ninja_lead',
        data() {
            return {
                loading: false,
                leadVisible: !!window.ninja_table_admin.show_lead_pop_up,
                display_name: window.ninja_table_admin.current_user_name,
                showPermission: false
            }
        },
        methods: {
            optin(status) {
                this.loading = true;
                this.$post({
                    action: 'ninja_table_lead_optin',
                    status: status
                })
                    .then((response) => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: 'success'
                        });
                    })
                    .fail((error) => {

                    })
                    .always(() => {
                        this.leadVisible = false;
                        this.loading = false;
                        if (status === 'yes') {
                            window.ninja_table_admin.show_lead_pop_up = false;
                        }
                    });
            }
        }
    }
</script>

<style lang="scss">
    .ninja_permissions {
        margin-top: 40px;
        text-align: center;
        a, p {
            font-size: 12px;
            color: gray;
            text-decoration: none;
        }
    }
</style> 
