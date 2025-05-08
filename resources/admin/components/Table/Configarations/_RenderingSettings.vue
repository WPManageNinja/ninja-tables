<template>
    <div class="ninja_rendering_settings">
        <div class="ninja_header">
            <h2>Table Render Settings</h2>
            <div class="ninja_actions_action">
                <el-button size="small" type="primary" @click="storeSettings()"> {{ $t('Update Configuration') }}</el-button>
            </div>
        </div>
        <div class="ninja_style_wrapper">
            <div class="ninja_section_block_body">
                <div class="section_block_item">
                    <p>Please the select the settings for your table render method. Using Legacy table you can use
                        shortcodes in your cells and it will render the full table from php side. Table styles will be
                        same for both tables. Most of the cases you will need Ajax Table which is recommended
                        settings.</p>
                    <div class="card_block">
                        <div @click="changeTableType('ajax_table')"
                             :class="(tableSettings.render_type == 'ajax_table') ? 'selected_type' : ''"
                             class="section_card">
                            <div v-show="tableSettings.render_type == 'ajax_table'" class="selected_ribbon">Selected
                            </div>
                            <h4>Ajax Table</h4>
                            <p>
                                Use this settings if you have lots of data and don't need cell merge features. It will
                                load your data over ajax. Please note that, shortcodes in table will not work here.
                            </p>
                        </div>
                        <div @click="changeTableType('legacy_table')"
                             :class="(tableSettings.render_type == 'legacy_table') ? 'selected_type' : ''"
                             class="section_card">
                            <div v-show="tableSettings.render_type == 'legacy_table'" class="selected_ribbon">Selected
                            </div>
                            <h4>Advanced Table (Legacy)</h4>
                            <div>
                                <p>
                                    Recommended settings for advanced features
                                </p>
                                <ul class="ninja_render_features">
                                    <li><span class="dashicons dashicons-yes"></span> Colspan ( Cell-Merge )</li>
                                    <li><span class="dashicons dashicons-yes"></span> Server Side Dom-Generation</li>
                                    <li><span class="dashicons dashicons-yes"></span> Render shortcode into table cells
                                    </li>
                                    <li><span class="dashicons dashicons-yes"></span> Better for SEO</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="config.table.hasCacheFeature" class="section_block_item">
                    <h3>
                        Disable Caching
                        <el-tooltip placement="right" effect="light">
                            <template #content>
                              <h3>Disable Caching</h3>
                                To optimize and load faster, we cache the table <br>
                                contents. It's not recommended to disable <br>
                                caching unless you know what you are doing
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </h3>
                  <div class="caching-block">
                    <div class="form-group">
                      <span style="margin-right: 5px;">Display Frontend Loader</span>
                      <el-switch v-model="tableSettings.frontend_loader" active-value="yes" inactive-value="no"/>
                    </div>
                  </div>
                    <div class="caching-block">
                        <div class="form-group">
                            <span style="margin-right: 5px;">Disable Caching</span>
                            <el-switch v-model="tableSettings.shouldNotCache" active-value="yes" inactive-value="no"/>
                        </div>
                    </div>

                </div>
                <div v-if="config.table.hasExternalCachingInterval" class="section_block_item">
                    <h3>
                        Caching Interval
                        <el-tooltip placement="right" effect="light">
                            <template #content>
                              <h3>Caching Interval</h3>
                                To optimize and load faster, You can cache the table data for certain minutes <br/>
                                so the data will load from cached data. Please Provide the value in minutes.
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </h3>
                    <div class="caching-block">
                        <div style="max-width: 400px" class="form-group">
                            <span style="margin-right: 5px;">Caching Interval (In Minutes)</span>
                            <el-input type="number" size="small" v-model="tableSettings.caching_interval"></el-input>
                            <p>Keep Blank or 0 to disable caching for table data</p>
                            <p v-if="tableSettings.caching_interval > 60">Current Caching Interval: <b>{{ (tableSettings.caching_interval / 60).toFixed(2) }} hours</b></p>
                        </div>
                    </div>
                </div>

                <div class="section_block_item" v-if="config.settings.render_type == 'legacy_table' && tableSettings.shouldNotCache != 'yes'">
                    <h3>
                        Enable Full HTML Cache
                        <el-tooltip placement="right" effect="light">
                            <template #content>
                              <h3>HTML Cache</h3>
                                If you enable this then ninja tables will cache the full html of the table and<br/>
                                render that without interact with database. You can set the time how many minutes it will cache each iteration.
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </h3>
                    <div class="caching-block">
                        <div class="form-group">
                            <span style="margin-right: 5px;">Enable HTML Caching</span>
                            <el-switch v-model="tableSettings.enable_html_cache" active-value="yes" inactive-value="no"/>
                        </div>
                        <div v-if="tableSettings.enable_html_cache == 'yes'" class="form-group">
                            <div style="max-width: 400px" class="form-group">
                                <span style="margin-right: 5px;">Caching Interval (In Minutes)</span>
                                <el-input type="number" size="small" v-model="tableSettings.html_caching_minutes"></el-input>
                                <p v-if="tableSettings.html_caching_minutes > 60">Current Caching Interval: <b>{{ (tableSettings.html_caching_minutes / 60).toFixed(2) }} hours</b></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import {useEventBus} from "../../../eventBus";
    import { InfoFilled } from '@element-plus/icons-vue';

    export default {
        name: 'ninja-rendering_settings',
        props: ['tableSettings', 'config'],
        data() {
            return {
                bus : useEventBus(),
                hasPro: !!window.ninja_table_admin.hasPro,
            }
        },
        components: {
          InfoFilled
        },
        methods: {
            storeSettings() {
                this.$emit('storeSettings');
            },
            changeTableType(tableType) {
                if(!this.hasPro && tableType == 'legacy_table') {
                    this.bus.emit('show_pro_popup', 1);
                    this.tableSettings.render_type = 'ajax_table';
                    return;
                }
                this.tableSettings.render_type  = tableType;
            },
        }
    }
</script>
