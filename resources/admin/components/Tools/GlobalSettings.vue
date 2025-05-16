<template>
    <div class="nt-table-global-settings-content">
        <div class="nt-license-header text-[18px] font-[600] text-[#0E121B]">
            {{ $t('Global Settings') }}
        </div>
        <hr class="my-4">
        <div class="nt-global-settings-content">
            <div class="text-[16px] font-[400] text-[#0E121B]">{{ $t('Global Javascript Error Handling') }}</div>
            <div class="ninja_tables_radio_group my-3">
                <el-radio-group v-model="ninja_suppress_error">
                    <el-space direction="vertical" alignment="start">
                        <el-radio :value="'log_silently'"  border>Console Log JS Errors Silently</el-radio>
                        <el-radio :value="'yes'"  border>Handle Error But don't Log</el-radio>
                        <el-radio :value="'no'" border>Don't Handle Global Javascript Errors (Default)</el-radio>
                    </el-space>

                </el-radio-group>
                <NinjaButton class="my-3" type="primary" @click="storeSettings" size="small" :btn-text="$t('Update Global Settings')"/>
            </div>
            <hr class="my-3">



            <div class="text-[16px] font-[400] text-[#0E121B]">{{ $t('Clear Cache') }}</div>
            <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
                {{
                    $t(`If you want to clear all the Ninja table cache, please click on the "Clear Table Cache" button or if you want to clear the cache by popular external plugins please click "Clear External Caches`)
                }}
            </div>
            <div class="my-3 flex gap-3">
                <NinjaButton type="primary" @click="clearTableCache" size="small" :btn-text="$t('Clear Table Cache')"/>
                <NinjaButton type="primary" @click="clearExternalCache" size="small" :btn-text="$t('Clear External Caches')"/>
            </div>
        </div>
    </div>


<!--    <div class="privacy">-->
<!--        <div class="ninja_header">-->
<!--            <h2>Global Settings</h2>-->
<!--        </div>-->

<!--        <div v-loading="loading" class="ninja_content">-->
<!--            <div class="ninja_block">-->
<!--                <h3>Global Javascript Error Handling</h3>-->
<!--                <el-radio-group class="spaced_new_line" v-model="ninja_suppress_error">-->
<!--                    <el-radio :value="'log_silently'">Console Log JS Errors Silently</el-radio>-->
<!--                    <el-radio :value="'yes'">Handle Error But don't Log</el-radio>-->
<!--                    <el-radio :value="'no'">Don't Handle Global Javascript Errors (Default)</el-radio>-->
<!--                </el-radio-group>-->
<!--            </div>-->
<!--            <el-button @click="storeSettings()" size="small" type="primary">Update Global Settings</el-button>-->
<!--        </div>-->

<!--        <div class="ninja_block ninja_heightlight_block">-->
<!--            <h3>Clear Cache</h3>-->
<!--            <p>If you want to clear all the Ninja table cache, please click on the "Clear Table Cache" button or if you want to clear the cache by popular external plugins please click "Clear External Caches".</p>-->
<!--&lt;!&ndash;        It will remove all the cache of "LiteSpeed Cache", "WP Redis", "WP Rocket", "WP Fastest Cache", "Autoptimize", "WP-Optimize", "SiteGround Optimizer", "Cloudflare" if you have at least any one of these. It will also clear Godaddy internal caches and wp-cache caches.&ndash;&gt;-->
<!--            <el-button @click="clearTableCache" size="small" type="primary">Clear Table Cache</el-button>-->
<!--            <el-button @click="clearExternalCache" size="small" type="warning">Clear External Caches</el-button>-->
<!--        </div>-->
<!--    </div>-->
</template>

<script>
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: "GlobalSettings",
        components: {NinjaButton},
        data() {
            return {
                loading: false,
                ninja_suppress_error: 'log_silently'
            };
        },
        methods: {
            getSettings() {
                this.loading = true;

                this.$get('tables/tools/global-settings')
                    .then(response => {
                        this.ninja_suppress_error = response.data.ninja_suppress_error;
                    })
                    .catch(error => {})
              this.loading = false;
            },
            storeSettings() {
                this.$post('tables/tools/global-settings', {
                      suppress_error: this.ninja_suppress_error
                })
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: "success"
                        });
                    })
                    .catch(e => {});
            },
            clearTableCache() {
                this.$post('tables/tools/clear-table-cache')
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: "success"
                        });
                    })
                    .catch(e => {});
            },
            clearExternalCache() {
                this.$post('tables/tools/clear-external-cache')
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: "success"
                        });
                    })
                    .catch(e => {});
            }
        },
        mounted() {
            this.getSettings();
        }
    };
</script>
