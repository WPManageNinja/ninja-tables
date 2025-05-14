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
                    <div class="mb-2 font-[500] text-[14px]">Classic</div>
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

        <el-collapse class="ninja-tables_rendering_accordion">
            <el-collapse-item name="1">
                <template #title>
                    <span style="font-weight: 400; font-size: 14px;">{{ $t('Caching Config') }}</span>
                </template>
                <div class="mt-5">
                    <div class="border-b-solid">
                        <div class="flex items-center" v-if="config.table.hasCacheFeature">
                            <el-switch active-value="yes" inactive-value="no" v-model="tableSettings.shouldNotCache"
                                       style="font-weight: 400;"/>
                            <span class="ml-3 text-[14px]">{{ $t('Disable Caching') }}</span>
                        </div>
                        <div class="flex items-center" v-if="config.table.hasCacheFeature">
                            <el-switch active-value="yes" inactive-value="no" v-model="tableSettings.frontend_loader"
                                       style="font-weight: 400;"/>
                            <span class="ml-3 text-[14px]">{{ $t('Display Frontend Loader') }}</span>
                        </div>
                        <div v-if="config.table.hasExternalCachingInterval" class="w-1/2">
                            <div class="mb-2 text-[14px] font-[500]">{{ $t('Caching Interval (In Minutes)') }}</div>
                            <NinjaInput type="number" size="small" v-model="tableSettings.caching_interval"/>
                            <p>Keep Blank or 0 to disable caching for table data</p>
                            <p v-if="tableSettings.caching_interval > 60">Current Caching Interval:
                                <b>{{ (tableSettings.caching_interval / 60).toFixed(2) }} hours</b></p>
                        </div>

                        <div class="my-4 w-1/2"
                             v-if="config.settings.render_type == 'legacy_table' && tableSettings.shouldNotCache != 'yes'">
                            <span class="mb-2 text-[14px] font-[500] mr-2">{{ $t('Enable Full HTML Cache') }}</span>
                            <el-switch active-value="yes" inactive-value="no" v-model="tableSettings.enable_html_cache"
                                       style="font-weight: 400;"/>

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
            <NinjaButton type="primary" size="small" :btn-text="$t('Save Settings')" @click="storeSettings"/>
        </div>
    </div>
</template>

<script type="text/babel">
import {useEventBus} from "../../../eventBus";
import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";

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
