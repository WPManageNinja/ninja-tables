<template>
    <div class="privacy">
        <div class="ninja_header">
            <h2>Global Settings</h2>
        </div>

        <div v-loading="loading" class="ninja_content">
            <div class="ninja_block">
                <h3>Global Javascript Error Handling</h3>
                <el-radio-group class="spaced_new_line" v-model="ninja_suppress_error">
                    <el-radio label="log_silently">Console Log JS Errors Silently</el-radio>
                    <el-radio label="yes">Handle Error But don't Log</el-radio>
                    <el-radio label="no">Don't Handle Global Javascript Errors (Default)</el-radio>
                </el-radio-group>
            </div>
            <el-button @click="storeSettings()" size="small" type="primary">Update Global Settings</el-button>
        </div>

        <div class="ninja_block ninja_heightlight_block">
            <h3>Clear Table Cache</h3>
            <p>If you want to clear all the Ninja table cache, please click on the following button</p>
            <el-button @click="clearCache()" size="small" type="danger">Clear Table Cache</el-button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "GlobalSettings",
        data() {
            return {
                loading: false,
                ninja_suppress_error: 'log_silently'
            };
        },
        methods: {
            getSettings() {
                this.loading = true;

                this.$get({
                    action: "ninja_tables_ajax_actions",
                    target_action: 'get_global_settings'
                })
                    .then(response => {
                        this.ninja_suppress_error = response.data.ninja_suppress_error;
                    })
                    .fail(error => {})
                    .always(() => {
                        this.loading = false;
                    });
            },
            storeSettings() {
                let data = {
                    action: "ninja_tables_ajax_actions",
                    target_action: 'update_global_settings',
                    suppress_error: this.ninja_suppress_error
                };
                this.$post(data)
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: "success"
                        });
                    })
                    .fail(e => {});
            },
            clearCache() {
                let data = {
                    action: "ninja_tables_ajax_actions",
                    target_action: 'clear_tables_cache'
                };
                this.$post(data)
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: "success"
                        });
                    })
                    .fail(e => {});
            }
        },
        mounted() {
            this.getSettings();
        }
    };
</script>
