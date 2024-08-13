<template>
    <el-form ref="form" :model="model" label-width="200px" class="form-wrapper">
        <el-tabs v-model="activeTab" @tab-click="onTabClick">
            <!-- Basic Settings -->
            <el-tab-pane class="basic_settings" label="Basic Settings" name="basic">
                <!-- Column Name -->
                <el-form-item>
                    <template slot="label">
                        {{ $t('Column Name') }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>Column Name</h3>
                                <p>
                                    Enter a column name to set the header title.
                                </p>
                            </div>
                            <i class="el-icon-info el-text-info"/>
                        </el-tooltip>
                    </template>
                    <el-input size="small" v-model="model.name"/>
                </el-form-item>

                <!-- Column Key -->
                <el-form-item>
                    <template slot="label">
                        {{ $t('Column Key') }}

                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>Column Key</h3>

                                <p>
                                    Column key is for data mapping, export and import table data.
                                </p>
                            </div>

                            <i class="el-icon-info el-text-info"/>
                        </el-tooltip>
                    </template>
                    <el-input size="small" v-model="model.key" :disabled="updating"/>
                    <small v-if="!updating">Please use english letters only</small>
                </el-form-item>

                <!-- Data Type -->
                <el-form-item>
                    <template slot="label">
                        {{ $t('Data Type') }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3> {{ $t('Data Type') }}</h3>
                                <p>
                                    Choose the data type of the column.
                                </p>
                            </div>
                            <i class="el-icon-info el-text-info"/>
                        </el-tooltip>
                    </template>
                    <el-select class="nt_column_type_select" size="mini" v-model="model.data_type" placeholder="Select Data Type of this column">
                        <el-option
                            v-for="(typeName, typeKey) in dataTypesOptions"
                            :key="typeKey"
                            :label="typeName"
                            :value="typeKey">
                        </el-option>
                    </el-select>
                    <p v-show="hasPro">Select HTML Field if you want to add Link, media or any type of html</p>
                </el-form-item>

                <template v-if="model.data_type == 'date'">
                    <!--Date Format -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t('Date Format') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3> {{ $t('Date Format') }}</h3>

                                    <p>
                                        Pattern of the date value.
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>

                        <el-radio-group v-model="model.formatType">
                            <el-radio label="standard">{{ $t('Standard') }}</el-radio>
                            <el-radio label="custom" @click.native="showProPopUp" :disabled="!hasPro">Custom</el-radio>
                        </el-radio-group>

                        <!-- Format dropdown -->
                        <el-form-item v-if="model.formatType != 'custom'">
                            <select v-model="model.dateFormat">
                                <option value="">{{ $t('Select a Format') }}</option>
                                <option v-for="(format, i) in dateFormats" :value="i" :key="i">
                                    {{ i }} - (Ex: {{ format }})
                                </option>
                            </select>
                        </el-form-item>

                        <!-- Format input -->
                        <el-form-item v-else>
                            <el-input size="small" v-model="model.dateFormat"
                                      placeholder="Enter moment.js supported format"/>
                        </el-form-item>
                    </el-form-item>

                    <!-- Show time -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t('Show Time') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Show Time</h3>

                                    <p>
                                        If you select yes, then time picker will be available
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-switch active-value="yes" v-model="model.showTime"/>
                        <select v-model="model.timeFormat" v-if="model.showTime === 'yes'">
                          <option value="">{{ $t('Select a Format') }}</option>
                          <option v-for="(format, i) in timeFormats" :value="i" :key="i">
                            {{ i }} - (Ex: {{ format }})
                          </option>
                        </select>
                    </el-form-item>
                    <!-- First Day -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t('First Day') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>First Day</h3>

                                    <p>
                                        The first day of the week, e.g. Sunday, Monday, etc.
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-select class="nt_column_type_select" size="mini" v-model="model.firstDayOfWeek" placeholder="Select first day of the week">
                            <el-option
                                    v-for="(typeName, typeKey) in weekDays"
                                    :key="typeKey"
                                    :label="typeName"
                                    :value="typeKey">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </template>

                <!--Number Format -->
                <template v-else-if="model.data_type == 'number' && hasPro">


                    <el-form-item>
                        <template slot="label">
                            {{ $t('Separator Style') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3> {{ $t('Thousand Separator') }}</h3>
                                    <p>
                                        Please Provide The Thousand/Decimal Separator If Any.
                                    </p>
                                </div>
                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-radio-group @change="changeDecimalStyle()" v-model="model.decimal_system">
                            <el-radio label="us">US Style - decimal point (123,234.01)</el-radio>
                            <el-radio label="eu">European Style - decimal comma (123.234,01)</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </template>

                <!--Selection Field -->
                <template v-else-if="model.data_type == 'selection'">
                    <el-form-item>
                        <template slot="label">
                            {{ $t('Select Items') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Select Field</h3>
                                    <p>
                                        Use Select Field to add data in your table from predefined list
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>

                        <!-- Format input -->
                        <el-form-item>
                            <p v-if="!has_pro"><b>Selection feature is only available on Pro version Please upgrade to
                                pro to unlock this feature</b></p>
                            <el-input type="textarea"
                                      size="small"
                                      :disabled="!has_pro"
                                      v-model="model.selections"
                                      placeholder="Enter Select items one per line"
                                      :autosize="{ minRows: 4, maxRows: 8}"
                            />
                            <small>Enter Select items one per line</small>
                        </el-form-item>
                    </el-form-item>

                    <el-form-item>
                        <template slot="label">
                            {{ $t('Placeholder') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Placeholder</h3>
                                    <p>
                                        Enter the selection placeholder, default: 'Select'
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>

                        <!-- placeholder input -->
                        <el-form-item>
                            <el-input type="text"
                                      size="small"
                                      :disabled="!has_pro"
                                      v-model="model.placeholder"
                                      placeholder="Enter placeholder"
                            />
                        </el-form-item>
                    </el-form-item>

                    <el-form-item>
                        <template slot="label">
                            {{ $t('Enable Multi-Selection') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Multiple Selection</h3>

                                    <p>
                                        If you select yes, Then admin can select multiple item on create data
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-switch active-value="yes" v-model="model.isMultiple">
                        </el-switch>
                    </el-form-item>
                </template>

                <!--Image Field -->
                <template v-else-if="model.data_type == 'image'">
                    <template v-if="!hasPro">
                        <el-form-item>
                            <p style="color: red">Image upload with lightbox, download link is a pro feature. It will
                                not work without Pro Version <b>
                                <get-pro/>
                              </b>
                            </p>
                        </el-form-item>
                    </template>
                    <el-form-item label="Image Linkable?">
                        <el-radio :disabled="!hasPro" v-model="model.link_type" label="none">Image Only</el-radio>
                        <el-radio :disabled="!hasPro" v-model="model.link_type" label="image_light_box">Image Lightbox</el-radio>
                        <el-radio :disabled="!hasPro" v-model="model.link_type" label="iframe_ligtbox">Iframe Lightbox</el-radio>
                        <el-radio :disabled="!hasPro" v-model="model.link_type" label="hyperlinked">Link to URL</el-radio>
                    </el-form-item>
                    <el-form-item v-if="model.link_type == 'file_download'" label="Download Button Text / HTML">
                        <el-input :disabled="!hasPro" type="textarea" placeholder="Download Button Text / HTML"
                                  v-model="model.download_button"></el-input>
                    </el-form-item>
                    <el-form-item v-if="model.link_type == 'hyperlinked' || model.link_type == 'file_download'">
                        <el-checkbox :disabled="!hasPro" true-label="_blank" false-label="_self" v-model="model.link_target">Open Link
                            in new window
                        </el-checkbox>
                    </el-form-item>

                </template>

                <!--Button Field -->
                <template v-else-if="model.data_type == 'button'">
                    <template v-if="!hasPro">
                        <el-form-item>
                            <p style="color: red">Button on Table is a pro Feature. It will not work without Pro Version. <b>
                              <get-pro/>
                            </b>
                            </p>
                        </el-form-item>
                    </template>
                    <el-form-item label="Button Text">
                        <el-input size="small" type="text" placeholder="Button Text (HTML supported)"
                                  v-model="model.button_text"></el-input>
                        <el-checkbox :disabled="!hasPro" true-label="_blank" false-label="_self" v-model="model.link_target">Open Link
                            in new tab
                        </el-checkbox>
                        <el-checkbox :disabled="!hasPro" true-label="nt_rounded_btn" false-label="" v-model="model.btn_extra_class">Make
                            Button as rounded corner
                        </el-checkbox>
                      <el-checkbox :disabled="!hasPro" true-label="download" false-label="" v-model="model.force_download">Make
                        Force download
                      </el-checkbox>
                    </el-form-item>
                    <el-form-item label="Button Style">
                        <div class="ninja_color_blocks">
                            <div class="ninja_color_block">
                                <ninja-color-picker
                                    :disabled="!hasPro"
                                    label="Background"
                                    v-model="model.btn_bg_color"
                                ></ninja-color-picker>
                            </div>
                            <div class="ninja_color_block">
                                <ninja-color-picker
                                    :disabled="!hasPro"
                                    label="Text Color"
                                    v-model="model.btn_text_color"
                                ></ninja-color-picker>
                            </div>
                            <div class="ninja_color_block">
                                <ninja-color-picker
                                    :disabled="!hasPro"
                                    label="Border Color"
                                    v-model="model.btn_border_color"
                                ></ninja-color-picker>
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item>
                      <template slot="label">
                        {{ $t('Link With Rel Attribute') }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                          <div slot="content">
                            <h3>Sponsored, Nofollow, NoReferrer & Noopener</h3>
                            <p>
                              Check one or multiple a rel attribute of the column
                            </p>
                          </div>
                          <i class="el-icon-info el-text-info"/>
                        </el-tooltip>
                      </template>
                      <el-checkbox-group v-model="model.relAttributes" v-if="model.relAttributes">
                        <el-checkbox v-for="attr in ['sponsored', 'nofollow', 'noreferrer', 'noopener']" :label="attr" :key="attr"></el-checkbox>
                      </el-checkbox-group>
                    </el-form-item>
                </template>

                <!-- Responsive Breakpoint -->
                <el-form-item>
                    <template slot="label">
                        {{ $t("Responsive Breakpoint") }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <h3>Responsive Breakpoint</h3>

                                <p>
                                    Choose responsive breakpoints of your table columns. <br>
                                    For more details check <a
                                    href="https://ninjatables.com/docs/column-responsive-breakpoints">documentation</a>.
                                </p>
                            </div>

                            <i class="el-icon-info el-text-info"/>
                        </el-tooltip>
                    </template>

                    <el-select size="mini" v-model="model.breakpoints" placeholder="Select Responsive Breakpoint">
                        <el-option
                            v-for="(option, optionKey) in breakPointsOptions"
                            :key="optionKey"
                            :label="option"
                            :value="optionKey">
                        </el-option>
                    </el-select>
                </el-form-item>

                <wp-post-dynamic-column
                    v-if="dataSourceType == 'wp-posts'"
                    :columns="columns"
                    :column="model"
                />
                <template v-else-if="dataSourceType == 'wp_woo'">
                    <dynamic-woo-column
                        :columns="columns"
                        :column="model"
                    />
                </template>
            </el-tab-pane>

            <!-- Advanced Settings -->
            <el-tab-pane label="Advanced Settings" name="advanced">
                <div class="advanced-settings">

                    <div class="ninja_table_inline_upgrade" v-if="!hasPro">

                        <H3>Advanced Column Settings</H3>
                        <p>
                            Customize your table's column's width, custom css class, content alignments, column styling
                            with this feature.
                            Advanced Column Settings is a pro feature and You can use it once you upgrade to Ninja
                            Tables Pro.
                            Ninja Table Pro has lots of features that will help you to build any type of Tables.
                        </p>
                        <get-pro size="small"/>
                    </div>

                    <!-- Extra classes -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t("Extra Classes") }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Extra CSS Classes</h3>

                                    <p>
                                        Enter extra CSS classes to the column. <br>
                                        Use `space` to separate each class.
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>

                        <el-input size="small" v-model="model.classes" :disabled="!hasPro"/>
                    </el-form-item>

                    <!-- Max width -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t("Max Width") }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>{{ $t('Maximum Width') }}</h3>

                                    <p>
                                        Enter the maximum width of the column. This will be applied for the entire
                                        column
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-col :xs="22" :md="22" :lg="22" :xl="22">
                            <el-form-item>
                                <el-input size="small" type="number" :disabled="!hasPro" v-model="model.width"/>
                            </el-form-item>
                        </el-col>

                        <el-col :xs="2" :md="2" :lg="2" :xl="2">
                            <el-form-item>
                                <el-select size="small" v-model="model.maxWidthUnit" placeholder="Select">
                                    <el-option label="px" value="px"/>
                                    <el-option label="%" value="%"/>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-form-item>

                    <!-- Header Text alignment -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t("Header Text Align") }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Header Text Alignment</h3>

                                    <p>
                                        Choose the text alignment. This will be applied only for header
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-select size="mini" v-model="model.textAlign" placeholder="Text Align">
                            <el-option
                                v-for="(alignmentLabel, alignmentVal) in alignmentOptions"
                                :key="alignmentVal"
                                :label="alignmentLabel"
                                :value="alignmentVal">
                            </el-option>
                        </el-select>
                    </el-form-item>

                    <!-- Content Text alignment -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t("Row Content Text Align") }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Content Text Alignment</h3>
                                    <p> Choose the text alignment for Column Rows</p>
                                </div>
                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </template>


                        <el-select size="mini" v-model="model.contentAlign" placeholder="Content Alignment">
                            <el-option
                                v-for="(alignmentLabel, alignmentVal) in contentAlignmentOptions"
                                :key="alignmentVal"
                                :label="alignmentLabel"
                                :value="alignmentVal">
                            </el-option>
                        </el-select>
                    </el-form-item>

                    <!-- Enable / Disable Table HTML -->
                    <el-form-item>
                        <el-checkbox :disabled="!hasPro" v-model="model.enable_html_content" :value="true"
                                     label="Enable HTML Table Header Content"></el-checkbox>
                    </el-form-item>

                    <!-- model.header_html_content -->
                    <el-form-item v-if="model.enable_html_content">
                        <template slot="label">
                            {{ $t("Header HTML Content") }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Header HTML Content</h3>
                                    <p>
                                        Provide content for table column header if you want to show html content.
                                    </p>
                                </div>
                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>

                        <wp_editor v-model="model.header_html_content"></wp_editor>
                    </el-form-item>

                    <el-form-item>
                        <template slot="label">
                            {{ $t("Filterable") }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Filterable</h3>

                                    <p>
                                        If You enable this then this column data will not be filterable at the frontend.
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-checkbox :disabled="!hasPro" v-model="model.unfilterable" true-label="yes" false-label="no"
                                     value="yes" label="Disable frontend search for this column data"></el-checkbox>
                    </el-form-item>

                    <el-form-item>
                        <template slot="label">
                            {{ $t("Sortable") }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Sortable</h3>

                                    <p>
                                        If You enable this then this column data will not be sortable at the frontend.
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-checkbox :disabled="!hasPro" v-model="model.unsortable" true-label="yes" false-label="no"
                                     value="yes" label="Disable frontend sorting for this column"></el-checkbox>
                    </el-form-item>

                    <el-form-item>
                        <template slot="label">
                            {{ $t("Column Background") }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Background color</h3>

                                    <p>
                                        You can set background color of this particular column that will show on the
                                        frontend table.
                                    </p>
                                </div>
                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-color-picker
                            :disabled="!hasPro"
                            v-model="model.background_color"
                            show-alpha
                            size="small"
                        ></el-color-picker>
                    </el-form-item>

                    <el-form-item>
                        <template slot="label">
                            {{ $t("Column Text Color") }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>Text Color color</h3>

                                    <p>
                                        You can set Column Text color of this particular column that will show on the
                                        frontend table.
                                    </p>
                                </div>
                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>
                        <el-color-picker
                            :disabled="!hasPro"
                            v-model="model.text_color"
                            show-alpha
                            size="small"
                        ></el-color-picker>
                    </el-form-item>

                </div>
            </el-tab-pane>

            <!-- Conditional Settings -->
            <el-tab-pane label="Conditional Formatting" name="conditional">
                <condition :column="model" :has-pro="hasPro"/>
            </el-tab-pane>

            <!-- Transform Value -->
            <el-tab-pane label="Transform Value" name="transformer">
                <content-transformer :settings="settings" :columns="columns" :column="model"/>
            </el-tab-pane>

            <hr style="margin:10px 0">

            <!-- Buttons -->
            <div class="form_group">
                <div class="pull-right">
                    <template v-if="!updating">
                        <el-button @click.prevent="cancel" size="small" v-if="!hideCancel">
                            {{ $t('Cancel') }}
                        </el-button>

                        <el-button
                            @click.prevent="addColumn"
                            :loading="doingAjax"
                            type="primary"
                            size="small">Add Column
                        </el-button>
                    </template>

                    <template v-else>
                        <el-popover
                            v-if="!hideDelete"
                            placement="top"
                            width="170"
                            v-model="showConfirm"
                            trigger="click"
                        >
                            <p>Are you sure to delete this?</p>
                            <div style="text-align: right; margin: 0">
                                <el-button
                                    type="text"
                                    size="mini"
                                    @click="showConfirm = false"
                                >cancel
                                </el-button>

                                <el-button
                                    type="primary"
                                    size="mini"
                                    @click="deleteColumn"
                                >confirm
                                </el-button>
                            </div>
                            <el-button
                                v-if="!hideDelete"
                                type="danger"
                                size="small"
                                slot="reference"
                            >{{ $t('Delete') }}
                            </el-button>
                        </el-popover>

                        <el-button :loading="doingAjax" @click.prevent="store" type="primary" size="small">
                            {{ $t('Update') }}
                        </el-button>
                    </template>
                </div>
            </div>
        </el-tabs>
    </el-form>
</template>

<script>
    import wpEditor from '../../../../common/_wp_editor';
    import conditional from './_conditional';
    import WPPostDynamicColumn from '../../DataProviders/WPPostDynamicColumn';
    import ContentTransformer from './ContentTransformer';
    import DynamicWooColumn from '../../TableNav/_WPWooDynamicColumn';
    import NinjaColorPicker from '../../Extras/ColorPicker'
    import GetPro from "../../Tools/GetPro";


    export default {
        name: "ColumnsEditor",
        components: {
          GetPro,
            'wp_editor': wpEditor,
            'condition': conditional,
            'wp-post-dynamic-column': WPPostDynamicColumn,
            'content-transformer': ContentTransformer,
            DynamicWooColumn,
            NinjaColorPicker
        },
        props: {
            model: {
                type: Object,
                default: () => ({})
            },
            hasPro: {
                type: Boolean,
                default: false
            },
            updating: {
                type: Boolean,
                default: false
            },
            moreSettings: {
                type: Boolean,
                default: false
            },
            hideCancel: {
                type: Boolean,
                default: false
            },
            dataSourceType: {
                type: String,
                default: 'default'
            },
            columns: {
                type: Array,
                default: () => []
            },
            settings: {
                type: Object
            }
        },
        data() {
            return {
                hideDelete: false,
                dataTypesOptions: {
                    text: this.$t("Single Line Text Field"),
                    textarea: this.$t("Text Area"),
                    html: this.$t("HTML Field"),
                    number: this.$t("Numeric Value"),
                    date: this.$t("Date Field"),
                    selection: this.$t("Select Field")
                },
                breakPointsOptions: {
                    '': this.$t('Always show in all devices'),
                    'all': this.$t('Hidden On Desktop'),
                    'xs': this.$t('Initial Hidden Mobile'),
                    'xs sm': this.$t('Initial Hidden Mobile and Tab'),
                    'xs sm md lg': this.$t('Initial Hidden Mobile, Tab and Regular Computers'),
                    'hidden': this.$t('Totally hidden on all devices'),
                },
                dateFormats: {
                    'M/D/YYYY': "4/28/2018",
                    'M/D/YY': "4/28/18",
                    'MM/DD/YY': "04/28/18",
                    'MM/DD/YYYY': "04/28/2018",
                    'MMM/DD/YYYY': "Apr/28/2018",
                    'YY/MM/DD': "18/04/28",
                    'YYYY-MM-DD': "2018-04-28",
                    'DD-MMM-YY': "28-Apr-18"
                },
              timeFormats: {
                'h:m a': "4:8 am/pm",
                'h:m:s a': "4:8:2 am/pm",
                'hh:mm a': "04:08 am/pm",
                'hh:mm:ss a': "04:08:12 am/pm",
                'h:m A': "4:8 AM/PM",
                'h:m:s A': "4:8:2 AM/PM",
                'hh:mm A': "04:08 AM/PM",
                'hh:mm:ss A': "04:08:12 AM/PM",
                'H:m': "1:8",
                'H:m:s': "1:8:2",
                'HH:mm': "01:08",
                'HH:mm:ss': "01:08:12",
              },
                weekDays: {
                    0: 'Sunday',
                    1: 'Monday',
                    2: 'Tuesday',
                    3: 'Wednesday',
                    4: 'Thursday',
                    5: 'Friday',
                    6: 'Saturday'
                },
                formatType: 'standard',
                has_pro: !!window.ninja_table_admin.hasPro,
                alignmentOptions: {
                    '': 'Default',
                    'center': 'Center',
                    'left': 'Left',
                    'right': 'Right',
                    'justify': 'Justify',
                    'start': 'Start',
                    'end': 'End',
                },
                contentAlignmentOptions: {
                    '': 'Default',
                    'center': 'Center',
                    'left': 'Left',
                    'right': 'Right',
                },
                activeTab: 'basic',
                showConfirm: false,
                doingAjax: false
            };
        },
        watch: {
            formatType() {
                if (this.formatType === "custom") {
                    this.model.dateFormat = "";
                }
            },
            hideDelete(oldValue, newValue) {
                this.hideDelete = this.activeTab != 'basic';
            },
            'model.data_type': function () {
                if(this.model.data_type == 'image') {
                    if(!this.model.link_type) {
                        this.$set(this.model, 'link_type', 'none');
                    }
                } else if(this.model.data_type == 'number' && !this.model.decimal_system) {
                    if(this.model.decimalSeparator == ',') {
                        this.$set(this.model, 'decimal_system', 'eu');
                    } else {
                        this.$set(this.model, 'decimal_system', 'us');
                    }
                    this.changeDecimalStyle();
                }
            }
        },
        methods: {
            addColumn() {
                if (!this.hasPro) {
                    if(this.model.data_type == 'button' || this.model.data_type == 'image') {
                        this.model.data_type = 'text';
                    }
                }
                this.$emit('add');
            },
            cancel() {
                this.$emit('cancel');
            },
            deleteColumn() {
                this.$emit('delete');
            },
            store() {
                this.$emit('store');
            },
            onTabClick(tab, event) {
                if (tab.name == 'basic') {
                    this.hideDelete = false;
                } else {
                    this.hideDelete = true;
                    if (this.moreSettings) {
                        this.moreSettings = !this.moreSettings;
                    }
                }
            },
            showProPopUp() {
                if (!this.hasPro) {
                    window.ninjaTableBus.$emit('show_pro_popup', 1);
                }
            },
            changeDecimalStyle() {
                if(this.model.decimal_system == 'us') {
                    this.$set(this.model, 'decimalSeparator', '.');
                    this.$set(this.model, 'thousandSeparator', ',');
                } else {
                    this.$set(this.model, 'decimalSeparator', ',');
                    this.$set(this.model, 'thousandSeparator', '.');
                }
            }
        },

        mounted() {
            if (this.dataSourceType == 'default') {
                this.$set(this.dataTypesOptions, 'image', 'Image/File/Lightbox');
            }
            this.dataTypesOptions.button = this.$t('Button/Link');

            if (!this.model) return;
            if (!this.model.hasOwnProperty('dateFormat')) {
                this.$set(this.model, 'dateFormat', "");
            }
            this.model.dateFormat = this.model.dateFormat || "";
            this.model.enable_html_content = ['true', true].indexOf(this.model.enable_html_content) !== -1;
            this.model.header_html_content = this.model.header_html_content || '';
            if (!this.model.contentAlign) {
                this.$set(this.model, 'contentAlign', '');
            }
            if (!this.model.textAlign) {
                this.$set(this.model, 'textAlign', '');
            }

            if (!this.model.maxWidthUnit) {
                this.$set(this.model, 'maxWidthUnit', 'px');
            }
            if (!this.model.timeFormat) {
              this.$set(this.model, 'timeFormat', '');
            }
          if (!this.model.relAttributes) {
            this.$set(this.model, 'relAttributes', []);
          }
          if (!this.model.force_download) {
            this.$set(this.model, 'force_download', '');
          }
            window.ninjaTableBus.$on('tableDoingAjax', (status) => {
                this.doingAjax = status;
            });
        },
    };
</script>

<style lang="scss">
    .form-wrapper {
        padding: 10px;

        label {
            display: initial;
            max-width: initial;
            margin-bottom: initial;
        }

        .el-form-item {
            margin-bottom: 15px;
        }

        .more-settings {
            &:hover {
                cursor: pointer;
            }

            i {
                font-size: 1.5em;
            }
        }
        .form_group {
            margin-top: 10px;
        }

        .basic_settings .el-select {
            min-width: 400px;
            max-width: 100%;
        }
    }
</style>
