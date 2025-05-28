<template>

    <div class="nt_rendering nt-table-configuration-content">
        <div class="text-[18px] font-[600] text-[#0E121B]">{{ $t('Table Render Settings') }}</div>
        <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">{{
                $t("Please the select the settings for your table render method. Using Legacy table you can use shortcodes in your cells and it will render the full table from php side. Table styles will be same for both tables. Most of the cases you will need Ajax Table which is recommended settings.")
            }}
        </div>

        <div class="grid grid-cols-2 gap-5 mb-5">
            <div :class="`nt_rendering_card ${tableSettings.render_type === 'ajax_table' && 'is_active'}`"
                 @click="()=>changeTableType('ajax_table')">
                <div>
                    <div class="mb-2 font-[500] text-[14px]">Ajax</div>
                    <div class="text-[12px] text-[#525866]">
                        {{ $t("Use this setting if you want to use serverside processing") }}
                    </div>
                </div>
                <div class="radio_button"></div>
            </div>
            <div :class="`nt_rendering_card ${tableSettings.render_type === 'legacy_table' && 'is_active'}`"
                 @click="()=>changeTableType('legacy_table')">
                <div>
                    <div class="mb-2 font-[500] text-[14px] flex items-center gap-1">
                        Legacy
                        <img v-if="!hasPro" class="h-4 w-4" :src="assetUrl('icons/get-pro.svg')" alt="">
                    </div>
                    <div class="text-[12px] text-[#525866]">
                        <span>{{ $t("Recommended settings for advanced features") }}</span>
                        <div class="flex gap-4 mt-2">
                            <div>
                                <div>
                                    <span class="dashicons dashicons-yes text-[#335cff]"></span>
                                    <span> {{ $t("Colspan ( Cell-Merge )") }}</span>
                                </div>
                                <div>
                                    <span class="dashicons dashicons-yes text-[#335cff]"></span>
                                    <span> {{ $t("Server Side Dom-Generation") }}</span>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <span class="dashicons dashicons-yes text-[#335cff]"></span>
                                    <span> {{ $t("Render shortcode into table cells") }}</span>
                                </div>
                                <div>
                                    <span class="dashicons dashicons-yes text-[#335cff]"></span>
                                    <span> {{ $t("Better for SEO") }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="radio_button"></div>
            </div>
        </div>

        <el-collapse v-if="config.table.hasCacheFeature || (config.settings.render_type == 'legacy_table' && tableSettings.shouldNotCache != 'yes') || tableSettings.enable_html_cache == 'yes' || config.table.hasExternalCachingInterval" class="ninja-tables_rendering_accordion">
            <el-collapse-item name="1">
                <template #title>
                    <span style="font-weight: 400; font-size: 14px;">{{ $t('Caching Configuration') }}</span>
                </template>
                <div class="mt-5">
                    <div class="border-b-solid">
                        <div v-if="config.table.hasCacheFeature" class="mb-3 text-[16px] font-[500]">{{ $t("Disable Caching") }}
                            <el-tooltip placement="right" effect="light"
                                        popper-class="nt-custom-tooltip"
                                        :content="$t(`To optimize and load faster, we cache the table contents. It's not recommended to disable caching unless you know what you are doing.`)">
                                <el-icon class="tooltip-icon-color">
                                    <InfoFilled/>
                                </el-icon>
                            </el-tooltip>
                        </div>
                        <div class="flex gap-6 mb-4" v-if="config.table.hasCacheFeature">
                            <div class="frontend-loader">
                                <el-checkbox :label="$t('Display Frontend Loader')" true-value="yes" false-value="no" v-model="tableSettings.frontend_loader"
                                             style="font-weight: 400;"/>
                            </div>

                            <div class="disable-cache">
                                <el-checkbox :label="$t('Disable Caching')" true-value="yes" false-value="no" v-model="tableSettings.shouldNotCache"
                                             style="font-weight: 400;"/>
                            </div>
                        </div>
                        <div v-if="config.table.hasExternalCachingInterval" class="w-1/2">
                            <div class="mb-2 text-[14px] font-[500]">{{ $t('Caching Interval (In Minutes)') }}</div>
                            <NinjaInput type="number" size="small" v-model="tableSettings.caching_interval"/>
                            <p>{{ $t('Keep Blank or 0 to disable caching for table data') }}</p>
                            <p v-if="tableSettings.caching_interval > 60">Current Caching Interval:
                                <b>{{ (tableSettings.caching_interval / 60).toFixed(2) }} hours</b></p>
                        </div>


                        <div class="my-4 w-1/2"
                             v-if="config.settings.render_type == 'legacy_table' && tableSettings.shouldNotCache != 'yes'">

                            <div class="mb-3 text-[16px] font-[500]">{{ $t("Enable Full HTML Cache") }}
                                <el-tooltip placement="right" effect="light"
                                            popper-class="nt-custom-tooltip"
                                            :content="$t('If you enable this then ninja tables will cache the full html of the table and render that without interact with database. You can set the time how many minutes it will cache each iteration.')">
                                    <el-icon class="tooltip-icon-color">
                                        <InfoFilled/>
                                    </el-icon>
                                </el-tooltip>
                            </div>

                            <div>
                                <el-checkbox :label="$t('Enable HTML Cache')" true-value="yes" false-value="no" v-model="tableSettings.enable_html_cache"
                                             style="font-weight: 400;"/>
                            </div>
                            <div v-if="tableSettings.enable_html_cache == 'yes'">
                                <div class="mb-2 text-[14px] font-[500]">{{ $t('Caching Interval (In Minutes)') }}</div>
                                <NinjaInput type="number" size="small" v-model="tableSettings.html_caching_minutes"
                                            placeholder="Enter Value"/>
                                <p v-if="tableSettings.html_caching_minutes > 60">Current Caching Interval:
                                    <b>{{ (tableSettings.html_caching_minutes / 60).toFixed(2) }} hours</b></p>
                            </div>
                        </div>
                    </div>
                </div>
            </el-collapse-item>
        </el-collapse>

        <div class="flex justify-end mt-4">
            <NinjaButton type="primary" :btn-text="$t('Save Settings')" @click="storeSettings"/>
        </div>
    </div>
</template>

<script type="text/babel">
import {useEventBus} from "../../../eventBus";
import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";
import {assetUrl} from "../../../utils/ninjatablesadmin";

export default {
    name: 'ninja-rendering_settings',
    components: {NinjaButton, NinjaInput},
    props: ['tableSettings', 'config'],
    data() {
        return {
            bus: useEventBus(),
            hasPro: !!window.ninja_table_admin.hasPro,
        }
    },
    methods: {
        assetUrl,
        storeSettings() {
            this.$emit('storeSettings');
        },
        changeTableType(tableType) {
            if (!this.hasPro && tableType == 'legacy_table') {
                this.bus.emit('show_pro_popup', 1);
                this.tableSettings.render_type = 'ajax_table';
                return;
            }
            this.tableSettings.render_type = tableType;
        },
    }
}
</script>
