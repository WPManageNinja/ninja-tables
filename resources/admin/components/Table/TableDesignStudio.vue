<template>
    <div class="ninja_design">
        <div class="ninja_title_section">
            <div class="ninja_title">
                <h3 style="margin-right: 15px;">Table Style Customization</h3>
                <el-radio-group class="ninja_resp_tabs" size="mini" v-model="showingDevice">
                    <el-radio-button label="desktop"><span class="dashicons dashicons-desktop"></span> Desktop
                    </el-radio-button>
                    <el-radio-button label="tablet"><span class="dashicons dashicons-tablet"></span> Tablet
                    </el-radio-button>
                    <el-radio-button label="mobile"><span class="dashicons dashicons-smartphone"></span> Mobile
                    </el-radio-button>
                </el-radio-group>
            </div>
            <el-button :loading="savingSettings" :disabled="savingSettings" size="small" type="primary"
                       @click="storeSettings()">Update Settings
            </el-button>
        </div>
        <div class="ninja_design_wrapper">
            <div v-loading="!app_ready" style="background: white; padding: 10px 20px;" class="design_preview">
                <div class="ninja_upgrade_bar" v-if="showProNotice">
                    {{ $t('Color customization is a PRO feature. Please upgrade to pro apply this feature.') }}
                    <get-pro type="primary"/>
                </div>
                <div
                    :id="'footable_parent_'+tableId"
                    class="footable_parent ninja_table_wrapper loading_ninja_table wp_table_data_press_parent"
                    :class="wrapperClasses"
                >

                    <h3 v-if="tableSettings.show_title" class="table_title footable_title">{{ config.table.post_title
                        }}</h3>
                    <div v-if="tableSettings.show_description" class="table_description footable_description"
                         v-html="config.table.post_content"></div>
                    <table
                        v-show="app_ready"
                        :id="'footable_'+tableId"
                        :class="tableClasses"
                        :style="fontSetting"
                        class="table foo-table ninja_footable">
                        <colgroup>
                            <col
                                v-for="(column, column_index) in formattedColumns"
                                :key="column_index"
                                :class="['ninja_column_'+column_index, column.breakpoints]"></col>
                        </colgroup>
                        <thead></thead>
                    </table>
                </div>
                <div class="ninja_demo_disclaimer">
                    <hr/>
                    <p v-if="tableSettings.stackable == 'yes'">
                        <b>For Stackable Tables, Live preview is disabled here. Please check on preview url</b>
                    </p>
                    <p>
                        <b>Note: </b> For preview purpose, you are seeing up to 25 latest rows here and and per page 10
                        items if you enable paginate. Also note that, The table style may differ at the frontend as your
                        theme may overwrite few css elements.
                    </p>
                    <p>Some elements like custom filters and row-inline styling is not available in this design mode. Please check on live preview or in your embeded page.</p>
                </div>
            </div>
            <div class="design_controls">
                <el-tabs v-model="activeDesign" type="border-card">
                    <el-tab-pane label="Styling" name="features">
                        <div class="form_group">
                            <h3 class="ninja_inner_title">Select Styling Library</h3>
                            <el-radio-group size="mini" v-model="tableSettings.css_lib">
                                <el-radio-button
                                    v-for="(tableLib, libKey) in currentTableLibs"
                                    :key="libKey"
                                    :label="libKey">
                                    {{ tableLib.title }}
                                    <el-tooltip placement="top-end" effect="light" :content="tableLib.description">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </el-radio-button>
                            </el-radio-group>
                        </div>
                        <div v-if="availableStyles" class="form_group label-normalize">
                            <h3 class="ninja_inner_title">Styles</h3>
                            <label
                                v-for="tableStyle in availableStyles"
                                :key="tableStyle.key"
                                :for="'table_style_'+tableStyle.key">
                                <input v-model="tableSettings.css_classes" type="checkbox" name="table_styles"
                                       :value="tableStyle.key" :id="'table_style_'+tableStyle.key"/>
                                {{ tableStyle.title }}
                                <el-tooltip placement="top-end" effect="light" :content="tableStyle.description">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                        </div>
                        <div class="form_group label-normalize">
                            <h3 class="ninja_inner_title">Features</h3>
                            <label for="show_title">
                                <input v-model="tableSettings.show_title" type="checkbox" value="1" id="show_title"/> {{
                                $t('Show Table Title') }}
                                <el-tooltip placement="top-end" effect="light"
                                            content="Enable this if you want to show table title in frontend">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                            <label for="show_description">
                                <input v-model="tableSettings.show_description" type="checkbox" value="1"
                                       id="show_description"/> {{ $t('Show Table Description') }}
                                <el-tooltip placement="top-end" effect="light"
                                            content="Enable this if you want to show table description in frontend">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                            <label for="enable_search">
                                <input v-model="tableSettings.enable_search" type="checkbox" value="1"
                                       id="enable_search"/> {{ $t('Enable the visitor to filter or search the table.')
                                }}
                            </label>
                            <label
                                v-if="tableLibs[tableSettings.library].supports.sorting && !tableSettings.enable_ajax"
                                for="column_sorting">
                                <input v-model="tableSettings.column_sorting" type="checkbox" value="1"
                                       id="column_sorting"/> {{ $t('Enable sorting of the table by the visitor') }}
                            </label>
                            <label><input v-model="tableSettings.hide_header_row" type="checkbox">
                                Hide Header Row
                            </label>
                            <label><input v-model="tableSettings.hide_all_borders" type="checkbox">
                                Hide All Borders
                            </label>
                            <label><input v-model="tableSettings.hide_on_empty" type="checkbox">
                                Hide empty items on responsive breakdown <span v-show="!has_pro">(Pro Only)</span>
                                <el-tooltip placement="top-end" effect="light"
                                            content="If You enable this then the empty ietems will not show into responsive drawer / Stackable View">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                            <label><input v-model="tableSettings.hide_responsive_labels" type="checkbox">
                                Hide Labels on responsive breakdown <span v-show="!has_pro">(Pro Only)</span>
                                <el-tooltip placement="top-end" effect="light"
                                            content="If You enable this then columns headings will not show into responsive drawer / Stackable View">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                        </div>

                        <div class="form_group label-normalize">
                            <h3 class="ninja_inner_title">
                                Stackable Table Configuration
                                <el-tooltip placement="top-end" effect="light"
                                            content="With stackable table, You can show your rows as list item. You can target by device width">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </h3>

                            <div class="form_group">
                                <el-checkbox true-label="yes" false-label="no" v-model="tableSettings.stackable">
                                    Enable Stackable Table
                                </el-checkbox>
                                <template v-if="tableSettings.stackable == 'yes'">
                                    <h3 style="margin-top: 15px" class="ninja_inner_title">Target Devices
                                        <el-tooltip placement="top-end" effect="light"
                                                    content="Select the device by width in where the stackable tables will be enabled">
                                            <i class="el-icon-info el-text-info"></i>
                                        </el-tooltip>
                                    </h3>
                                    <el-checkbox-group
                                        v-model="tableSettings.stacks_devices">
                                        <el-checkbox label="xs">Mobile Device</el-checkbox>
                                        <el-checkbox label="sm">Tablet Device</el-checkbox>
                                        <el-checkbox label="md">Laptop</el-checkbox>
                                        <el-checkbox label="lg">Large Devices (imac)</el-checkbox>
                                    </el-checkbox-group>

                                    <h3 style="margin-top: 15px" class="ninja_inner_title">Stacked Appearance
                                        <el-tooltip placement="top-end" effect="light"
                                                    content="You can customize the appearance in stacked view of your table">
                                            <i class="el-icon-info el-text-info"></i>
                                        </el-tooltip>
                                    </h3>
                                    <el-checkbox-group
                                        v-model="tableSettings.stacks_appearances">
                                        <el-checkbox label="hide_stacked_th">Hide column headings</el-checkbox>
                                        <el-checkbox label="ninja_stacked_no_cell_border">Hide internal borders
                                        </el-checkbox>
                                    </el-checkbox-group>
                                </template>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="Table Colors" name="color_customization">
                        <div class="form_group">
                            <h3 class="ninja_inner_title">Select Color Scheme</h3>
                            <el-radio-group size="mini" v-model="tableSettings.table_color_type">
                                <el-radio-button label="pre_defined_color">Pre Defined Scheme</el-radio-button>
                                <el-radio-button label="custom_color">Custom Scheme</el-radio-button>
                            </el-radio-group>
                        </div>
                        <div v-if="tableSettings.table_color_type == 'pre_defined_color'" class="form_group">
                            <select class="form_control" v-model="tableSettings.table_color">
                                <option v-for="(colorName, colorKey) in colors" :key="colorKey" :value="colorKey">{{
                                    colorName }}
                                </option>
                            </select>
                        </div>
                        <div v-else class="form_group ninja_color_customization">
                            <h3 class="ninja_inner_title">Search Bar Colors</h3>
                            <div class="ninja_color_blocks">
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Background"
                                        v-model="tableSettings.table_search_color_primary"
                                    ></ninja-color-picker>
                                </div>
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Icon"
                                        v-model="tableSettings.table_search_color_secondary"
                                    ></ninja-color-picker>
                                </div>
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Border"
                                        v-model="tableSettings.table_search_color_border"
                                    ></ninja-color-picker>
                                </div>
                            </div>

                            <h3 class="ninja_inner_title">{{ $t('Table Header Colors') }}</h3>
                            <div class="ninja_color_blocks">
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Background"
                                        v-model="tableSettings.table_header_color_primary"
                                    ></ninja-color-picker>
                                </div>
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Text"
                                        v-model="tableSettings.table_color_header_secondary"
                                    ></ninja-color-picker>
                                </div>
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Border"
                                        v-model="tableSettings.table_color_header_border"
                                    ></ninja-color-picker>
                                </div>
                            </div>

                            <h3 class="ninja_inner_title">{{ $t('Table Body Colors') }}</h3>
                            <el-tabs>
                                <el-tab-pane label="Default">
                                    <div class="ninja_color_blocks">
                                        <div class="ninja_color_block">
                                            <ninja-color-picker
                                                    label="Background"
                                                    v-model="tableSettings.table_color_primary"
                                            ></ninja-color-picker>
                                        </div>
                                        <div class="ninja_color_block">
                                            <ninja-color-picker
                                                    label="Text"
                                                    v-model="tableSettings.table_color_secondary"
                                            ></ninja-color-picker>
                                        </div>
                                        <div class="ninja_color_block">
                                            <ninja-color-picker
                                                    label="Border"
                                                    v-model="tableSettings.table_color_border"
                                            ></ninja-color-picker>
                                        </div>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="Hover">
                                    <div class="ninja_color_blocks">
                                        <div class="ninja_color_block">
                                            <ninja-color-picker
                                                    label="Background"
                                                    v-model="tableSettings.table_color_primary_hover"
                                            ></ninja-color-picker>
                                        </div>
                                        <div class="ninja_color_block">
                                            <ninja-color-picker
                                                    label="Text"
                                                    v-model="tableSettings.table_color_secondary_hover"
                                            ></ninja-color-picker>
                                        </div>
                                        <div class="ninja_color_block">
                                            <ninja-color-picker
                                                    label="Border"
                                                    v-model="tableSettings.table_color_border_hover"
                                            ></ninja-color-picker>
                                        </div>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>

                            <div class="ninja_switch_wrapper">
                                <el-switch
                                    inactive-color="gray"
                                    active-text="Use Alternate Color Schema for Table Rows"
                                    active-value="yes" inactive-value="no"
                                    v-model="tableSettings.alternate_color_status"></el-switch>
                            </div>
                            <div class="ninja_alternate_colors" v-if="tableSettings.alternate_color_status == 'yes'">
                                <h3 class="ninja_inner_title">{{ $t('Odd Row Colors') }}</h3>
                                <div class="ninja_color_blocks">
                                    <div class="ninja_color_block">
                                        <ninja-color-picker
                                            label="Background"
                                            v-model="tableSettings.table_alt_2_color_primary"
                                        ></ninja-color-picker>
                                    </div>
                                    <div class="ninja_color_block">
                                        <ninja-color-picker
                                            label="Text"
                                            v-model="tableSettings.table_alt_2_color_secondary"
                                        ></ninja-color-picker>
                                    </div>
                                    <div class="ninja_color_block">
                                        <ninja-color-picker
                                            label="Hover Background"
                                            v-model="tableSettings.table_alt_2_color_hover"
                                        ></ninja-color-picker>
                                    </div>
                                </div>
                                <h3 class="ninja_inner_title">{{ $t('Even Row Colors') }}</h3>
                                <div class="ninja_color_blocks">
                                    <div class="ninja_color_block">
                                        <ninja-color-picker
                                            label="Background"
                                            v-model="tableSettings.table_alt_color_primary"
                                        ></ninja-color-picker>
                                    </div>
                                    <div class="ninja_color_block">
                                        <ninja-color-picker
                                            label="Text"
                                            v-model="tableSettings.table_alt_color_secondary"
                                        ></ninja-color-picker>
                                    </div>
                                    <div class="ninja_color_block">
                                        <ninja-color-picker
                                            label="Hover Background"
                                            v-model="tableSettings.table_alt_color_hover"
                                        ></ninja-color-picker>
                                    </div>
                                </div>
                            </div>
                            <h3 class="ninja_inner_title">{{ $t('Footer Colors') }}</h3>
                            <div class="ninja_color_blocks">
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Background"
                                        v-model="tableSettings.table_footer_bg"
                                    ></ninja-color-picker>
                                </div>
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Active"
                                        v-model="tableSettings.table_footer_active"
                                    ></ninja-color-picker>
                                </div>
                                <div class="ninja_color_block">
                                    <ninja-color-picker
                                        label="Border"
                                        v-model="tableSettings.table_footer_border"
                                    ></ninja-color-picker>
                                </div>
                            </div>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="Other" name="other_settings">

                        <div class="ninja_switch_wrapper">
                            <el-switch
                                inactive-color="gray"
                                active-text="Hide Pagination (Show all data at once)"
                                active-value="1" inactive-value="0"
                                v-model="tableSettings.show_all"></el-switch>
                        </div>

                        <div v-show="tableSettings.show_all == 0" class="form_group">
                            <div class="form_group">
                                <label for="items_per_page">{{ $t('Pagination Items Per Page') }}</label>
                                <input id="items_per_page" class="form_control" type="number"
                                       v-model="tableSettings.perPage"
                                       :disabled="tableSettings.show_all == true || tableSettings.show_all == '1'"/>
                            </div>

                            <label>{{ $t('Pagination Position') }}</label>
                            <el-radio-group
                                :disabled="tableSettings.show_all == true || tableSettings.show_all == '1'"
                                size="mini" v-model="tableSettings.pagination_position">
                                <el-radio-button label="left">Left</el-radio-button>
                                <el-radio-button label="center">Center</el-radio-button>
                                <el-radio-button label="right">Right</el-radio-button>
                            </el-radio-group>

                            <label><input v-model="tableSettings.paginate_to_top" type="checkbox">
                                Scroll to table top for pagination change <span v-show="!has_pro">(Pro Only)</span>
                                <el-tooltip placement="top-end" effect="light"
                                            content="If you enable this then on pagination change, the table will be scrolled to top">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>

                            <label><input :disabled="!has_pro" v-model="tableSettings.show_pager" type="checkbox">
                                Show Page sizes change option <span v-show="!has_pro">(Pro Only)</span>
                                <el-tooltip placement="top-end" effect="light"
                                            content="If you enable this then Users can change the items per page on frontend">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>

                            <div v-if="tableSettings.show_pager" class="form_group">
                                <label>{{ $t('Page Sizes (Number as Comma Separated)') }}</label>
                                <input class="form_control" type="text"
                                       placeholder="Default: 10,20,50,100"
                                       v-model="tableSettings.paze_sizes"/>
                            </div>

                        </div>

                        <div class="form_group">
                            <label>{{ $t('Search Bar Position') }}</label>
                            <el-radio-group
                                :disabled="!has_pro"
                                size="mini" v-model="tableSettings.search_position">
                                <el-radio-button label="left">Left</el-radio-button>
                                <el-radio-button label="center">Center</el-radio-button>
                                <el-radio-button label="right">Right</el-radio-button>
                                <el-radio-button label="">Default</el-radio-button>
                            </el-radio-group>

                            <label><input v-model="tableSettings.nt_search_full_width" type="checkbox">
                                Make search input as full width <span v-show="!has_pro">(Pro Only)</span>
                                <el-tooltip placement="top-end" effect="light"
                                            content="If You enable this, Then the search input will take all the available space (100% width)">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </label>
                        </div>

                        <div class="form_group">
                            <label>Select Sorting Method</label>
                            <el-radio-group size="mini" v-model="tableSettings.sorting_type">
                                <el-radio-button :disabled="!config.table.isCreatedSortable" label="by_created_at">By
                                    Created at
                                </el-radio-button>
                                <el-radio-button label="by_column">By Column</el-radio-button>
                                <el-radio-button :disabled="!config.table.isSortable" label="manual_sort">Manual Sort
                                </el-radio-button>
                            </el-radio-group>
                            <div v-if="config.table.isCreatedSortable && tableSettings.sorting_type == 'by_created_at'" class="">
                                <span>{{ $t('Sort Type') }}
                                    <select v-model="tableSettings.default_sorting">
                                        <option value="new_first">{{ $t('Show New Items First') }}</option>
                                        <option value="old_first">{{ $t('Show Old Items First') }}</option>
                                    </select>
                                </span>
                            </div>
                            <div v-else-if="tableSettings.sorting_type == 'by_column'">
                                <label>{{ $t('Select Column') }}
                                    <select v-model="tableSettings.sorting_column">
                                        <option v-for="column in config.columns" :key="column.key" :value="column.key">
                                            {{ column.name }}
                                        </option>
                                    </select>
                                </label>
                                <label>{{ $t('Sort Type') }}
                                    <select v-model="tableSettings.sorting_column_by">
                                        <option value="ASC">Ascending Way</option>
                                        <option value="DESC">Descending Way</option>
                                    </select>
                                </label>
                            </div>
                            <div v-else-if="config.table.isSortable && tableSettings.sorting_type == 'manual_sort'">
                                <p>You can sort the table data from <b>Table Rows</b> Manually. Click Sort Manually
                                    checkbox to sort the data using drag and drop feature</p>
                            </div>

                            <el-button v-if="tableSettings.sorting_type" size="mini"
                                       @click="tableSettings.sorting_type = ''">reset
                            </el-button>

                        </div>

                        <div class="form_group">
                            <label>{{ $t('Row Details (Responsive drawer)') }} <span
                                v-show="!has_pro">(PRO)</span></label>
                            <el-radio-group size="mini" v-model="tableSettings.expand_type">
                                <el-radio-button label="default">
                                    Default
                                    <el-tooltip placement="top-end" effect="light"
                                                content="Show All the responsive columns data into the responsive drawer">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </el-radio-button>
                                <el-radio-button label="expandFirst">
                                    Expand First
                                    <el-tooltip placement="top-end" effect="light" content="This will automatically expand the first row of the table when displayed on a device that
                            hides any columns.">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </el-radio-button>
                                <el-radio-button label="expandAll">
                                    Expand All
                                    <el-tooltip placement="top-end" effect="light" content="This will automatically expand all rows of the table when displayed on a device that hides
                            any columns.">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </el-radio-button>
                            </el-radio-group>
                        </div>

                        <div class="form_group">
                            <label>{{ $t('Toggle Position') }}</label>
                            <el-radio-group size="mini" v-model="tableSettings.togglePosition">
                                <el-radio-button label="first">
                                    First Column
                                    <el-tooltip placement="top-end" effect="light"
                                                content="If you use responsive breakdown then the '+' icon will show at the first visible column">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </el-radio-button>
                                <el-radio-button label="last">
                                    Last Column
                                    <el-tooltip placement="top-end" effect="light"
                                                content="If you use responsive breakdown then the '+' icon will show at the last visible column">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </el-radio-button>
                            </el-radio-group>
                        </div>

                        <div class="form_group">
                            <label for="extra_css_class">{{ $t('Extra CSS Class for the table') }}</label>
                            <input id="extra_css_class" class="form_control" type="text"
                                   v-model="tableSettings.extra_css_class"/>
                        </div>

                        <div class="form_group">
                            <label>{{ $t('Sticky Header') }} <span v-if="!has_pro">(Pro)</span></label>
                            <el-checkbox :disabled="!has_pro" true-label="yes" false-label="no"
                                         v-model="tableSettings.sticky_header">Enable Sticky header for table
                            </el-checkbox>
                            <template v-if="tableSettings.sticky_header == 'yes'">
                                <label style="margin-top: 10px">Sticky Top Offset
                                    <el-tooltip placement="top-end" effect="light"
                                                content="You can set an offset value for the sticky table header.">
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </label>
                                <input placeholder="positive or negative number" class="form_control" type="text"
                                       v-model="tableSettings.sticky_header_offset"/>
                                <small>Please give positive/negative number or you can provide jquery element object
                                </small>

                                <el-checkbox :disabled="!has_pro" true-label="yes" false-label="no"
                                             v-model="tableSettings.disable_sticky_on_mobile">Disable Sticky header for mobile devices
                                </el-checkbox>

                            </template>
                        </div>
                      <div class="form_group font-setting">
                        <label>Table Font Setting</label>
                        <div class="font">
                          <label>{{$t('Font Family')}}</label>
                          <el-select v-model="tableSettings.table_font_family" placeholder="Select Font">
                            <el-option v-for="(family, key) in fontFamily" :key="key" :label="family === 'inherit' ? 'theme-font' : family" :value="family"></el-option>
                          </el-select>
                        </div>
                        <div class="font">
                          <label>{{$t('Font Size')}}</label>
                          <el-input-number :min="1" :max="50" v-model="tableSettings.table_font_size"></el-input-number>
                        </div>
                      </div>
                    </el-tab-pane>
                </el-tabs>

                <div class="ninja_design_tips" v-if="design_tips.length">
                    <ul class="ninja_design_tips_lists">
                        <li v-for="design_tip in design_tips"><i class="el-icon-warning"></i> <span
                            v-html="design_tip"></span></li>
                    </ul>
                </div>

                <div v-if="!has_pro" class="upgrade_box">
                  <get-pro text="Get Pro"/>
                </div>
            </div>
        </div>
        <sortable-upgrade-notice :show="sortableUpgradeNotice"
                                 @close="sortableUpgradeNotice = false"></sortable-upgrade-notice>
    </div>
</template>

<script type="text/babel">
    import {tableLibs} from '../../data/data'
    import get from 'lodash/get'
    import size from 'lodash/size'
    import forEach from 'lodash/forEach'
    import intersection from 'lodash/intersection';
    import SortableUpgradeNotice from '../includes/SortableUpgradeNotice.vue';
    import NinjaColorPicker from '../Extras/ColorPicker';
    import GetPro from "../Tools/GetPro";

    export default {
        name: 'table_preview',
        props: ['config'],
        components: {
          GetPro,
            SortableUpgradeNotice,
            NinjaColorPicker
        },
        data() {
            return {
                fontFamily: ['inherit', 'cursive', 'fantasy', 'monospace', 'sans-serif', 'serif', 'system-ui', 'ui-monospace', 'ui-rounded', 'ui-sans-serif', 'ui-serif'],
                rows: [],
                activeDesign: 'features',
                tableId: this.$route.params.table_id,
                tableSettings: this.config.settings,
                table_body_html: '',
                data_loaded: false,
                script_loaded: false,
                footableLoading: false,
                tableLibs: tableLibs(),
                has_pro: !!window.ninja_table_admin.hasPro,
                savingSettings: false,
                tableInnerHtml: '',
                showingDevice: 'desktop',
                hasSortable: !!window.ninja_table_admin.hasSortable,
                sortableUpgradeNotice: false,
                columnCss: ''
            }
        },
        computed: {
            fontSetting() {
              return {
                '--ninja-table-font-family': this.tableSettings.table_font_family,
                '--ninja-table-font-size': this.tableSettings.table_font_size+'px'
              }
            },
            wrapperClasses() {
                let classes = [];
                classes.push(this.tableSettings.css_lib);
                classes.push('ninja_device_' + this.showingDevice);
                if (this.tableSettings.table_color_type == 'custom_color' || this.tableSettings.table_color != 'ninja_no_color_table') {
                    classes.push('colored_table');
                }
                return classes;
            },
            tableClasses() {
                let classes = [];
                classes.push('foo_table_' + this.tableId);

                if (this.tableSettings.table_color_type == 'custom_color') {
                    classes.push('inverted');
                    classes.push('ninja_custom_color');
                } else {
                    if (this.tableSettings.table_color && this.tableSettings.table_color != 'ninja_no_color_table') {
                        classes.push('inverted');
                    }
                    classes.push(this.tableSettings.table_color);
                }

                if (this.tableSettings.pagination_position) {
                    classes.push('footable-paging-' + this.tableSettings.pagination_position);
                } else {
                    classes.push('footable-paging-right');
                }

                if (this.tableSettings.hide_header_row) {
                    classes.push('ninjatable_hide_header_row');
                }
                if (this.tableSettings.hide_all_borders) {
                    classes.push('hide_all_borders');
                }
                classes.push('ninja_table_pro');

                if (this.tableSettings.search_position) {
                    classes.push('ninja_search_' + this.tableSettings.search_position);
                }

                let table_css_classes = [];
                if (this.tableSettings.css_classes) {
                    table_css_classes = this.availableCssClasses.filter(value => -1 != this.tableSettings.css_classes.indexOf(value));
                }

                if (this.tableSettings.hide_responsive_labels) {
                    classes.push('nt_hide_breakpoint_labels');
                }

                if (this.tableSettings.nt_search_full_width) {
                    classes.push('nt_search_full_width');
                }


                if (this.tableSettings.css_lib == 'semantic_ui') {
                    classes.push('ui');
                }

                return [...table_css_classes, ...classes];
            },
            formattedColumns() {
                let columns = this.config.columns;
                let formattedColumns = [];
                jQuery.each(columns, (index, column) => {
                    formattedColumns.push({
                        name: column.key,
                        title: column.name,
                        breakpoints: column.breakpoints,
                        type: column.data_type,
                        sortable: true,
                        classes: ['ninja_column_' + index],
                        visible: (column.breakpoints == 'hidden') ? false : true
                    });
                });
                return formattedColumns;
            },
            app_ready() {
                return this.data_loaded && this.script_loaded
            },
            currentTableLibs() {
                return this.tableLibs[this.tableSettings.library].css_libs;
            },
            colors() {
                return this.tableLibs[this.tableSettings.library].colors;
            },
            availableStyles() {
                let lib = this.currentTableLibs[this.tableSettings.css_lib];
                if (lib)
                    return lib.styles;
                return false;
            },
            availableCssClasses() {
                let cssClasses = [];
                forEach(this.availableStyles, (style) => {
                    cssClasses.push(style.key);
                });
                return cssClasses;
            },
            showProNotice() {
                if (this.has_pro) {
                    return false;
                }
                if (
                    (
                        this.tableSettings.table_color_type == 'custom_color' &&
                        this.activeDesign == 'color_customization'
                    )
                    ||
                    (
                        this.activeDesign == 'color_customization' &&
                        this.tableSettings.table_color &&
                        this.tableSettings.table_color != 'ninja_no_color_table')
                ) {
                    return true;
                }
                return false;
            },
            design_tips() {
                let tips = [];
                if (this.tableSettings.table_color_type == 'custom_color') {
                    if (
                        !this.tableSettings.table_search_color_primary ||
                        !this.tableSettings.table_header_color_primary ||
                        !this.tableSettings.table_color_primary ||
                        !this.tableSettings.table_color_secondary
                    ) {
                        tips.push('You should set colors at <b>"Table Colors"</b> Tab');
                    }
                }
                return tips;
            }
        },
        watch: {
            data_loaded() {
                if (this.app_ready) {
                    this.reInitFootables();
                }
            },
            script_loaded() {
                if (this.app_ready) {
                    this.reInitFootables();
                }
            },
            tableSettings: {
                handler(val) {
                    this.$nextTick(() => {
                        this.generateColorCss();
                    });
                },
                deep: true
            },
            tableClasses: {
                handler(val) {
                    this.$nextTick(() => {
                        this.reInitFootables();
                    });
                },
                deep: true
            },
            'tableSettings.enable_search'() {
                this.$nextTick(() => {
                    this.reInitFootables();
                });
            },
            'tableSettings.column_sorting'() {
                this.$nextTick(() => {
                    this.reInitFootables();
                });
            },
            'tableSettings.show_all'() {
                this.$nextTick(() => {
                    this.reInitFootables();
                });
            },
            'tableSettings.togglePosition'() {
                this.$nextTick(() => {
                    this.reInitFootables();
                });
            },
            'tableSettings.expand_type': function (new_val, old_val) {
                if (new_val != 'default') {
                    if (!this.has_pro) {
                        this.tableSettings.expand_type = 'default';
                        window.ninjaTableBus.$emit('show_pro_popup', 1);
                        return;
                    }
                }
                this.$nextTick(() => {
                    this.reInitFootables();
                });
            },
            'tableSettings.sorting_type': function (newVal, oldVal) {
                if (newVal === 'manual_sort') {
                    if (!this.has_pro) {
                        this.tableSettings.sorting_type = oldVal;
                        window.ninjaTableBus.$emit('show_pro_popup', 1);
                    } else if (!this.hasSortable) {
                        if (!this.hasSortable) {
                            this.tableSettings.sorting_type = oldVal;
                            this.sortableUpgradeNotice = true
                        }
                    } else {
                        this.initManualSorting();
                    }
                }
            },
            activeDesign() {
                this.checkColorPro();
            }
        },
        methods: {
            fetchTableBody() {
              this.$get(`tables/${this.tableId}/preview-html`)
                    .then(response => {
                        this.tableInnerHtml = response;
                        this.data_loaded = true;
                    })
                    .catch(error => {
                        jQuery('#footable_' + this.tableId).append('<h1>Error Loading</h1>');
                    });
            },
            initManualSorting() {
                let promise = new Promise((resolve, reject) => {
                    window.ninjaTableBus.$emit('initManualSorting', {
                        table_id: this.tableId,
                        noData: true
                    }, resolve, reject);
                })
            },
            storeSettings() {
                this.checkColorPro();
                this.savingSettings = true;
                let filteredTableSettings = this.filterTableSettings(this.tableSettings);
                let data = {
                    columns: this.columns,
                    table_settings: this.tableSettings
                };
                this.$post(`settings/${this.tableId}`, data)
                    .success((res) => {
                        this.$message({
                            showClose: true,
                            message: res.message,
                            type: 'success'
                        });
                    })
                    .fail((error) => {

                    })
                    .always(() => {
                        this.savingSettings = false;
                    });
            },
            filterTableSettings(settings) {
                let validStyles = [];
                forEach(this.availableStyles, (style) => {
                    validStyles.push(style.key);
                });
                settings.css_classes = intersection(validStyles, this.tableSettings.css_classes);

                return settings;
            },
            reInitFootables() {
                if (!this.app_ready) {
                    return;
                }
                if (typeof FooTable == 'object') {
                    let ft = FooTable.get('#footable_' + this.tableId);
                    if (ft) {
                        ft.destroy();
                    }
                }
                jQuery('#footable_' + this.tableId).find('thead,tbody,tfoot').remove();
                this.footableLoading = false;
                jQuery('#footable_' + this.tableId).append(this.tableInnerHtml);
                this.initFootables();
            },
            initFootables() {
                if (this.footableLoading || !this.script_loaded) {
                    return;
                }
                this.footableLoading = true;
                let NinjaTableApp = window.ninjaTableApp;
                let $table = jQuery('#footable_' + this.tableId);

                if (this.tableSettings.hide_on_empty) {
                    $table.on('expanded.ft.row', function (e, ft, row) {
                        $table.find('table.footable-details td:empty').parent().addClass('nt_has_hide');
                    });
                }

                let config = this.getTableConfig();
                NinjaTableApp.initTable($table, config);
                this.footableLoading = false;
            },
            dysel(options) {
                // get options
                var links = options.links;
                var callback = options.callback;
                var nocache = options.nocache;
                var debug = options.debug;

                // js and css file loader
                var loadjscssfile = function (filename, cb) {
                    filename = filename.toString();
                    var ext = filename.split('.').pop();
                    var fileref = null;
                    if (ext == "js") {
                        // for Javascript
                        fileref = document.createElement('script');
                        fileref.setAttribute("type", "text/javascript");
                        fileref.setAttribute("src", filename);
                    } else if (ext == "css" || filename.indexOf('googleapis.com/css?') > -1) {
                        // for CSS + google fonts
                        fileref = document.createElement("link");
                        fileref.setAttribute("rel", "stylesheet");
                        fileref.setAttribute("type", "text/css");
                        fileref.setAttribute("href", filename);
                    }
                    // callback trigger (w/debug if needed)
                    if (typeof fileref != "undefined") {
                        if (cb) {
                            var mycallback = cb;
                            if (debug) { // if debug redefine callback and add console.log
                                mycallback = function () {

                                    cb();
                                }
                            }
                            // trigger the callback when resource is loaded
                            fileref.onreadystatechange = mycallback;
                            fileref.onload = mycallback
                        }
                        if (debug) {

                        }
                        // push it into the header
                        document.getElementsByTagName("head")[0].appendChild(fileref);
                    }
                }

                // START HERE, i nest the final callback at the deepest
                // (callbacks will be stacked in reverse order from here)
                var totalScript = callback;

                // create nested functions as callbacks,
                // at the end, if needed, the callback from options is executed
                // like func_1(loadfile_1, func_2(loadfile_2, func_3(loadfile_3, cbFromOptions)))
                for (var i = links.length - 1; i >= 0; i--) {
                    var old = totalScript;
                    let currentLink = links[i];
                    if (nocache) {
                        currentLink += '?' + +new Date().getTime();
                    }
                    totalScript = function (oldcb) {
                        loadjscssfile(this, oldcb);
                    }.bind(currentLink, old);
                }
                // execute the nested callbacks
                totalScript();
            },
            loadRequiredScripts() {
                let that = this;
                this.dysel({
                    links: window.ninja_table_admin.preview_required_scripts,
                    callback() {
                        that.script_loaded = true;
                    }
                })
            },
            size,
            get,
            generateColorCss() {
                if (this.tableSettings.table_color_type == 'pre_defined_color') {
                    jQuery('#table_designer_css').html('');
                    return;
                }
                let prefix = '#footable_' + this.tableId;
                let css = `
                    ${prefix} {
                        background-color: ${this.tableSettings.table_color_primary} !important;
                        color: ${this.tableSettings.table_color_secondary} !important;
                    }
                     ${prefix} thead tr.footable-filtering th {
                        background-color: ${this.tableSettings.table_search_color_primary} !important;
                        color: ${this.tableSettings.table_search_color_secondary} !important;
                    }
                    ${prefix}:not(.hide_all_borders) thead tr.footable-filtering th {
                        ${this.tableSettings.table_search_color_border ? `
                         border : 1px solid ${this.tableSettings.table_search_color_border} !important;
                        ` : `
                        border : 1px solid transparent !important;
                        `}
                    }
                    ${prefix} .input-group-btn:last-child > .btn:not(:last-child):not(.dropdown-toggle) {
                         background-color: ${this.tableSettings.table_search_color_secondary} !important;
                         color: ${this.tableSettings.table_search_color_primary} !important;
                    }
                     ${prefix} tr.footable-header, ${prefix} tr.footable-header th {
                        background-color: ${this.tableSettings.table_header_color_primary} !important;
                        color: ${this.tableSettings.table_color_header_secondary} !important;
                    }
                    ${prefix}:not(.hide_all_borders) tr.footable-header th {
                        border-color: ${this.tableSettings.table_color_header_border} !important;
                    }
                    ${prefix}:not(.hide_all_borders) tbody tr td {
                       border-color: ${this.tableSettings.table_color_border} !important;
                    }
                    ${prefix} tbody tr:hover {
                        background-color: ${this.tableSettings.table_color_primary_hover} !important;
                        color: ${this.tableSettings.table_color_secondary_hover} !important;
                    }
                    ${prefix} tbody tr:hover td {
                        border-color: ${this.tableSettings.table_color_border_hover} !important;
                    }

                     ${(this.tableSettings.alternate_color_status == 'yes') ? `
                         ${prefix} tbody tr:nth-child(even) {
                             background-color: ${this.tableSettings.table_alt_color_primary} !important;
                             color: ${this.tableSettings.table_alt_color_secondary} !important;
                         }
                         ${prefix} tbody tr:nth-child(odd) {
                             background-color: ${this.tableSettings.table_alt_2_color_primary} !important;
                             color: ${this.tableSettings.table_alt_2_color_secondary} !important;
                         }
                         ${prefix} tbody tr:nth-child(even):hover {
                             background-color: ${this.tableSettings.table_alt_color_hover} !important;
                         }
                         ${prefix} tbody tr:nth-child(odd):hover {
                             background-color: ${this.tableSettings.table_alt_2_color_hover} !important;
                         }
                     ` : `
                     `}

                     ${prefix} tfoot .footable-paging {
                       background-color: ${this.tableSettings.table_footer_bg} !important;
                    }
                    ${prefix} tfoot .footable-paging .footable-page.active a {
                        background-color: ${this.tableSettings.table_footer_active} !important;
                    }
                    ${prefix}:not(.hide_all_borders) tfoot .footable-paging td {
                        border-color: ${this.tableSettings.table_footer_border} !important;
                    }
                `;
                jQuery('#table_designer_css').html(css);
            },
            changeColor(color, element) {
                this.$set(this.tableSettings, element, color);
            },
            checkColorPro() {
                if (this.has_pro) {
                    return;
                }
                if (this.tableSettings.table_color &&
                    this.tableSettings.table_color != 'ninja_no_color_table' ||
                    this.tableSettings.table_color_type != 'pre_defined_color'
                ) {
                    this.tableSettings.table_color_type = 'pre_defined_color';
                    this.tableSettings.table_color = 'ninja_no_color_table';
                }
            },
            generateDefaultCss() {
                let columnContentCss = this.config.table.custom_css;
                this.config.columns.forEach((column, index) => {
                    if (column.background_color || column.text_color || column.contentAlign) {
                        columnContentCss += `#footable_parent_${this.tableId} thead tr th.ninja_column_${index}, #footable_parent_${this.tableId} tbody tr td.ninja_column_${index} { background-color: ${column.background_color}; color: ${column.text_color}; }`;
                        columnContentCss += `#footable_parent_${this.tableId} tbody tr td.ninja_column_${index} { text-align: ${column.contentAlign}; }`;
                    }
                });
                jQuery('#ninja_table_designer_common_css').html(columnContentCss);
            },
            getTableConfig() {
                let custom_css = {};
                this.config.columns.forEach((column, index) => {

                    custom_css['ninja_column_' + index] = {
                        'text-align': column.textAlign,
                        'width': column.width + 'px'
                    };
                });

                let settings = {
                    default_sorting: 'old_first',
                    defualt_filter: false,
                    defualt_filter_column: null,
                    expandAll: this.tableSettings.expand_type === "expandAll",
                    expandFirst: this.tableSettings.expand_type === "expandFirst",
                    filtering: !!this.tableSettings.enable_search,
                    i18n: {},
                    use_parent_width: this.showingDevice !== 'desktop',
                    sorting: !!this.tableSettings.column_sorting,
                    togglePosition: this.tableSettings.togglePosition
                };

                let initConfig = {
                    "toggleColumn" : this.tableSettings.togglePosition,
                    "cascade" : true,
                    "useParentWidth" : this.showingDevice !== 'desktop',
                    "columns" : this.config.columns,
                    "expandFirst" : this.tableSettings.expand_type === "expandFirst",
                    "expandAll" :  this.tableSettings.expand_type === "expandAll",
                    'empty' : '',
                    filtering: {
                        enabled: !!this.tableSettings.enable_search
                    },
                    paging: {
                        enabled: this.tableSettings.show_all == '0' || this.tableSettings.show_all == 0,
                        "size" : 10,
                        "container" : "#footable_parent_"+this.tableId+" .paging-ui-container",
                    },
                    sorting: {
                        enabled: !!this.tableSettings.column_sorting
                    },
                };

                return {
                    columns: this.formattedColumns.forEach(item => Object.assign({}, item)),
                    custom_css: custom_css,
                    settings: settings,
                    render_type: 'legacy_table',
                    instance_name: 'ninja_table_instance_0',
                    table_id: this.table_id,
                    title: '',
                    init_config: initConfig
                };
            }
        },
        mounted() {
            this.fetchTableBody();
            this.loadRequiredScripts();
            if (!this.tableSettings.table_color_type) {
                if (this.tableSettings.table_color == 'ninja_table_custom_color') {
                    this.$set(this.tableSettings, 'table_color_type', 'custom_color');
                } else {
                    this.$set(this.tableSettings, 'table_color_type', 'pre_defined_color');
                }
            }
            if (this.tableSettings.alternate_color_status === undefined) {
                this.$set(this.tableSettings, 'alternate_color_status', 'no');
            }
            jQuery('.ninja_design_wrapper').css('width', jQuery('.wrap').width() + 'px');
            jQuery(window).on('resize', function () {
                jQuery('.ninja_design_wrapper').css('width', jQuery('.wrap').width() + 'px');
            });
            this.generateDefaultCss();
            this.generateColorCss();
        }
    }
</script>
<style lang="scss">
    .striped > tbody > :nth-child(odd) {
        background: transparent;
    }

    .footable_parent.ninja_device_mobile {
        width: 480px;
        margin: 0 auto;
    }

    .footable_parent.ninja_device_tablet {
        max-width: 768px;
        padding: 0px 20px;
        margin: 0 auto;
    }

    .design_preview .footable_parent {
        .footable-header th {
            // word-break: break-all;
        }
    }
    .font-setting {
      .font {
        label {
          width: 30%;
        }
        margin-top: 10px;
      }
    }
    .ninja_footable {
      tbody {
        tr {
          td {
            font-size: var(--ninja-table-font-size);
            font-family: var(--ninja-table-font-family);
          }
        }
      }
    }
</style>
