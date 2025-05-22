<template>
    <div class="nt-table-global-settings-content">
        <div class="nt-license-header text-[18px] font-[600] text-[#0E121B]">{{ $t('Global Settings') }}</div>
        <div class="text-[14px] font-[400] text-[#0E121B] my-5">
            {{ $t('You can configure global settings for Ninja Tables here. This settings will apply to all the tables.') }}
        </div>
        <div class="nt-global-settings-content" v-loading="loading">
            <div class="text-[16px] font-[500] text-[#3C434A]">{{ $t('Global Javascript Error Handling') }}</div>
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



            <div class="text-[16px] font-[500] text-[#3C434A]">{{ $t('Clear Cache') }}</div>
            <div class="text-[14px] font-[400] text-[#3C434A] mt-[10px] mb-[20px]">
                {{
                    $t(`If you want to clear all the Ninja table cache, please click on the "Clear Table Cache" button or if you want to clear the cache by popular external plugins please click "Clear External Caches`)
                }}
            </div>
            <div class="my-3 flex gap-3">
                <NinjaButton type="primary" @click="clearTableCache" size="small" :btn-text="$t('Clear Table Cache')"/>
                <NinjaButton type="danger" @click="clearExternalCache" size="small" :btn-text="$t('Clear External Caches')"/>
            </div>
        </div>
    </div>
</template>

<script>
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: "GlobalSettings",
        components: {NinjaButton},
        data() {
            return {
                loading: true,
                ninja_suppress_error: ''
            };
        },
        methods: {
            getSettings() {
                this.loading = true;

                this.$get('tables/tools/global-settings')
                    .then(response => {
                        this.ninja_suppress_error = response.data.ninja_suppress_error ?? 'log_silently';
                    })
                    .catch(error => {
                        // Handle error if needed
                    })
                    .finally(() => {
                        this.loading = false;
                    });
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
