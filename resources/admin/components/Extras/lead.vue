<template>
    <el-dialog
        class="ninja_create-table-modal"
        v-if="leadVisible"
        v-model="leadVisible"
        title="We made a few tweaks to Ninja Tables"
    >
        <div v-loading="loading" class="p-5">
            <h3 class="nt-modal-subtitle">Hello {{display_name}},</h3>
            <p class="nt-modal-description">
                Never miss an important update - opt in to our security & feature updates notifications. We will never
                spam / share your data, We will only send emails about important updates
            </p>

           <div class="my-5 flex justify-end items-center gap-2">
               <NinjaButton
                   type="secondary"
                   @click="optin('no')"
                   :btn-text="$t('Skip')"
               />
               <NinjaButton
                   @click="optin('no')"
                   :btn-text="$t('Opt-in and Continue')"
               />
           </div>

            <div class="p-[10px] text-center">
                <a class="text-[#335CFF] shadow-none focus:shadow-none" @click.prevent="showPermission = !showPermission" href="#">What permissions are being granted?</a>
                <p v-show="showPermission" class="permissions mt-2">
                    Name, email, Site URL, Plugins info, ip Address and uninstall event
                </p>
            </div>
        </div>
    </el-dialog>
</template>

<script type="text/babel">

    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'ninja_lead',
        components: {NinjaButton},
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
