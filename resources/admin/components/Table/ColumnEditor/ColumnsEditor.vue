<template>
    <el-form ref="form" :model="model" class="form-wrapper" style="max-height: 70vh; overflow-y: auto;">
        <el-tabs class="nt_tab_design" v-model="activeTab" @tab-click="onTabClick">
            <!-- Basic Settings -->
            <el-tab-pane class="basic_settings ninja_modal_body px-[10px] pt-[18px]" label="Basic Settings" name="basic">

                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item>
                            <label class="nt-form-label">
                                {{ $t('Column Name') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>Column Name</h3>
                                        <p>{{ $t('Enter a column name to set the header title.') }}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                </el-tooltip>
                            </label>
                            <NinjaInput
                                v-model="model.name"
                                :placeholder="$t('Enter a column name to set the header title')"
                            />
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item>
                            <label class="nt-form-label">
                                {{ $t('Column Key') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>Column Key</h3>
                                        <p>{{ $t('Column key is for data mapping, export and import table data.') }}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                </el-tooltip>
                            </label>
                            <NinjaInput
                                size="small" v-model="model.key"
                                :disabled="updating"
                                :placeholder="$t('Enter a colum key')"
                            />
                            <small v-if="!updating">{{ $t('Please use english letters only') }}</small>
                        </el-form-item>
                    </el-col>
                </el-row>

                <!-- Data Type -->
                <el-form-item class="nt-form-group mt-[-40px]">
                    <label class="nt-form-label">
                        {{ $t('Data Type') }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3>{{ $t('Data Type') }}</h3>
                                <p>{{ $t('Choose the data type of the column.') }}</p>
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </label>
                    <el-select class="ninja-select" v-model="model.data_type" placeholder="Select Data Type of this column">
                        <el-option
                            v-for="(typeName, typeKey) in dataTypesOptions"
                            :key="typeKey"
                            :label="typeName"
                            :value="typeKey">
                        </el-option>
                    </el-select>
                    <p v-show="hasPro" class="mt-1">{{ $t('Select HTML Field if you want to add Link, media or any type of html') }}</p>
                </el-form-item>

                <div v-if="model.data_type === 'date'">
                    <!--Date Format -->
                    <el-form-item class="nt-form-group">
                        <div class="flex flex-col w-full">
                            <label class="nt-form-label">
                                {{ $t('Date Format') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>Date Format</h3>
                                        <p>
                                            {{ $t('Choose the date format of the column.') }}
                                        </p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                </el-tooltip>
                            </label>

                            <div class="flex gap-x-2">
                                <el-radio-group v-model="model.formatType" class="ninja_tables_radio_group">
                                    <el-radio border value="standard" class="mr-2">{{ $t('Standard') }}</el-radio>
                                    <el-radio border value="custom" @click.native="showProPopUp" :disabled="!hasPro" class="mr-2">Custom</el-radio>
                                </el-radio-group>

                                <!-- Format dropdown -->
                                <div v-if="model.formatType !== 'custom'" class="nt-form-group  w-4/6">
                                    <el-select v-model="model.dateFormat" class="ninja-select w-full">
                                        <el-option value="">{{ $t('Select a Format') }}</el-option>
                                        <el-option v-for="(format, i) in dateFormats" :value="i" :key="i">
                                            {{ i }} - (Ex: {{ format }})
                                        </el-option>
                                    </el-select>
                                </div>

                                <!-- Format input -->
                                <div v-else class="nt-form-group w-4/6">
                                    <NinjaInput
                                        v-model="model.dateFormat"
                                        placeholder="Enter moment.js supported format"
                                    />
                                </div>
                            </div>
                        </div>
                    </el-form-item>

                    <!-- Show time -->
                    <el-form-item class="nt-form-group">
                        <el-checkbox v-model="model.showTime" :true-value="'yes'" :false-value="'no'">
                            {{ $t('Show Time') }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>Show Time</h3>

                                    <p>
                                        {{ $t('If you select yes, then time picker will be available') }}
                                    </p>
                                </template>

                                <el-icon class="tooltip-icon-color">
                                    <InfoFilled/>
                                </el-icon>
                            </el-tooltip>
                        </el-checkbox>

                        <el-select v-model="model.timeFormat" v-if="model.showTime === 'yes'" class="ninja-select">
                            <el-option value="">{{ $t('Select a Format') }}</el-option>
                            <el-option v-for="(format, i) in timeFormats" :value="i" :key="i">
                                {{ i }} - (Ex: {{ format }})
                            </el-option>
                        </el-select>

                    </el-form-item>

                    <!-- First Day -->
                    <el-form-item class="flex flex-col">
                        <label class="nt-form-label">
                            {{ $t('First Day') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>First Day</h3>

                                    <p>
                                        {{ $t('The first day of the week, e.g. Sunday, Monday, etc.') }}
                                    </p>
                                </template>

                                <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                            </el-tooltip>
                        </label>

                        <el-select class="ninja-select" size="small" v-model="model.firstDayOfWeek" placeholder="Select first day of the week">
                            <el-option
                                v-for="(typeName, typeKey) in weekDays"
                                :key="typeKey"
                                :label="typeName"
                                :value="typeKey">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </div>


                <!--Number Format -->
                <div v-else-if="model.data_type === 'number' && hasPro" class="flex flex-col">
                    <label class="nt-form-label mb-2">
                        {{ $t('Separator Style') }}

                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3> {{ $t('Thousand Separator') }}</h3>
                                <p>
                                    {{ $t('Please Provide The Thousand/Decimal Separator If Any.') }}
                                </p>
                            </template>
                            <el-icon class="tooltip-icon-color">
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                    </label>

                    <el-radio-group
                        @change="changeDecimalStyle()"
                        v-model="model.decimal_system"
                        class="ninja_tables_radio_group"
                    >
                        <el-radio value="us"> {{ $t('US Style - decimal point (123,234.01)') }}</el-radio>
                        <el-radio value="eu">{{ $t('European Style - decimal comma (123.234,01)') }}</el-radio>
                    </el-radio-group>
                </div>

                <!--Selection Field -->
                <div v-else-if="model.data_type === 'selection'" class="nt-form-group">
                    <div class="flex flex-col">
                        <label class="nt-form-label">
                            {{ $t('Select Items') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>{{ $t('Select Field') }}</h3>
                                    <p>
                                        {{ $t('Use Select Field to add data in your table from predefined list') }}
                                    </p>
                                </template>

                                <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                            </el-tooltip>
                        </label>

                        <!-- Format input -->
                        <el-form-item class="nt-form-group mt-2">
                            <p v-if="!has_pro">
                                <b> {{ $t(`Selection feature is only available on Pro version Please upgrade to
                                pro to unlock this feature`) }} </b>
                            </p>
                            <el-input type="textarea"
                                      size="small"
                                      :disabled="!has_pro"
                                      v-model="model.selections"
                                      :placeholder="$t('Enter Select items one per line')"
                                      :autosize="{ minRows: 4, maxRows: 8}"
                            />
                            <small>{{ $t('Enter Select items one per line') }}</small>
                        </el-form-item>
                    </div>

                    <div class="flex flex-col">
                        <label class="nt-form-label mb-2">
                            {{ $t('Placeholder') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>{{ $t('Placeholder') }}</h3>
                                    <p>
                                        {{ $t('Enter the selection placeholder, default: Select') }}
                                    </p>
                                </template>

                                <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                            </el-tooltip>
                        </label>

                        <!-- placeholder input -->
                        <NinjaInput
                            v-model="model.placeholder"
                            :disabled="!has_pro"
                            placeholder="Enter placeholder"
                        />
                    </div>

                    <div class="nt-form-group mt-2">
                        <el-checkbox
                            v-model="model.isMultiSelect"
                            :true-value="'yes'"
                            :false-value="'no'"
                        >
                            {{ $t('Enable Multi-Selection') }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>{{ $t('Multiple Selection') }}</h3>

                                    <p>
                                       {{ $t('If you select yes, Then admin can select multiple item on create data') }}
                                    </p>
                                </template>
                                <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                            </el-tooltip>
                        </el-checkbox>
                    </div>
                </div>

                <!--Image Field -->
                <template v-else-if="model.data_type === 'image'">
                    <template v-if="!hasPro">
                        <el-form-item class="nt-form-group">
                            <p style="color: red"> {{ $t(`Image upload with lightbox, download link is a pro feature. It will
                                not work without Pro Version`) }}
                                <b>
                                    <get-pro/>
                                </b>
                            </p>
                        </el-form-item>
                    </template>
                    <el-form-item label="Image Linkable?" label-position="top" class="nt-form-group">
                        <el-radio-group class="ninja_tables_radio_group" v-model="model.link_type">
                            <el-radio :disabled="!hasPro" value="none" border>{{ $t('Image Only') }}</el-radio>
                            <el-radio :disabled="!hasPro" value="image_light_box" border>{{ $t('Image Lightbox') }}</el-radio>
                            <el-radio :disabled="!hasPro" value="iframe_ligtbox" border>{{ $t('Iframe Lightbox') }}</el-radio>
                            <el-radio :disabled="!hasPro" value="hyperlinked" border>{{ $t('Link to URL') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item v-if="model.link_type == 'file_download'" label="Download Button Text / HTML" label-position="top">
                        <el-input :disabled="!hasPro" type="textarea" placeholder="Download Button Text / HTML"
                                  v-model="model.download_button"></el-input>
                    </el-form-item>
                    <el-form-item v-if="model.link_type == 'hyperlinked' || model.link_type == 'file_download'">
                        <el-checkbox :disabled="!hasPro" :true-value="'_blank'" :false-value="'_self'" v-model="model.link_target">Open Link
                            in new window
                        </el-checkbox>
                    </el-form-item>

                </template>

                <!--Button Field -->
                <template v-else-if="model.data_type === 'button'">
                    <template v-if="!hasPro">
                        <el-form-item class="nt-form-group">
                            <p style="color: red"> {{$t('Button on Table is a pro Feature. It will not work without Pro Version.')}} <b>
                                <get-pro/>
                            </b>
                            </p>
                        </el-form-item>
                    </template>

                    <div class="flex flex-col">
                        <label class="nt-form-label">
                            {{ $t('Button Text') }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>{{ $t('Button Text') }}</h3>
                                    <p>
                                        {{$t('Enter the button text you want to show on the button.')}}
                                    </p>
                                </template>
                                <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                </el-tooltip>
                        </label>

                        <div class="flex flex-col mb-4">
                            <NinjaInput
                                v-model="model.button_text"
                                :placeholder="$t('Button Text (HTML supported)')"
                            />

                            <div class="mt-2">
                                <el-checkbox :disabled="!hasPro" :true-value="'_blank'" :false-value="'_self'" v-model="model.link_target">
                                    {{ $t('Open Link in new tab') }}
                                </el-checkbox>
                                <el-checkbox :disabled="!hasPro" :true-value="'nt_rounded_btn'" :false-value="''" v-model="model.btn_extra_class">
                                    {{ $t('Make Button as rounded corner') }}
                                </el-checkbox>
                                <el-checkbox :disabled="!hasPro" :true-value="'download'" :false-value="''" v-model="model.force_download">
                                   {{ $t('Make Force download') }}
                                </el-checkbox>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div class="grid grid-cols-2 gap-x-5">
                           <div class="mt-2">
                               <label class="nt-form-label">
                                   {{ $t('Button Background Color') }}
                                   <el-tooltip class="item" placement="bottom-start" effect="light">
                                       <template #content>
                                           <h3>{{ $t('Button Background Color') }}</h3>
                                           <p>
                                               {{ $t('Change the button background color') }}
                                           </p>
                                       </template>
                                       <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                   </el-tooltip>
                               </label>
                               <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                   <ColorPicker :disabled="!hasPro" v-model="model.btn_bg_color" />
                               </div>
                           </div>

                            <div class="mt-2">
                                <label class="nt-form-label">
                                    {{ $t('Button Text Color') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <template #content>
                                            <h3>{{ $t('Button Text Color') }}</h3>
                                            <p>
                                                {{ $t('Change the button text color') }}
                                            </p>
                                        </template>
                                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                    </el-tooltip>
                                </label>
                                <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                    <ColorPicker :disabled="!hasPro" v-model="model.btn_text_color" />
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="nt-form-label">
                                    {{ $t('Button Border Color') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <template #content>
                                            <h3>{{ $t('Button Border Color') }}</h3>
                                            <p>
                                                {{ $t('Change the button border color') }}
                                            </p>
                                        </template>
                                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                    </el-tooltip>
                                </label>
                                <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                    <ColorPicker :disabled="!hasPro" v-model="model.btn_border_color" />
                                </div>
                            </div>

                            <div class="flex flex-col mt-4">
                                <label class="nt-form-label">
                                    {{ $t('Link With Rel Attribute') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <template #content>
                                            <h3>Sponsored, Nofollow, NoReferrer & Noopener</h3>
                                            <p>
                                                Check one or multiple a rel attribute of the column
                                            </p>
                                        </template>
                                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                                    </el-tooltip>
                                </label>
                                <el-checkbox-group class="mt-2" v-model="model.relAttributes" v-if="model.relAttributes">
                                    <el-checkbox
                                        v-for="attr in ['sponsored', 'nofollow', 'noreferrer', 'noopener']"
                                        :key="attr"
                                        :label="attr"
                                        :value="attr"
                                    />
                                </el-checkbox-group>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Responsive Breakpoint -->
                <el-form-item class="flex flex-col mt-5">
                    <label class="nt-form-label">
                        {{ $t("Responsive Breakpoint") }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3>Responsive Breakpoint</h3>

                                <p>
                                    Choose responsive breakpoints of your table columns. <br>
                                    For more details check <a
                                    href="https://ninjatables.com/docs/column-responsive-breakpoints">documentation</a>.
                                </p>
                            </template>

                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </label>

                    <el-select :empty-values="[undefined, null]" class="ninja-select" v-model="model.breakpoints" placeholder="Select Responsive Breakpoint">
                        <el-option
                            v-for="(option, optionKey) in breakPointsOptions"
                            :key="optionKey"
                            :label="option"
                            :value="optionKey">
                        </el-option>
                    </el-select>
                </el-form-item>

                <WPPostDynamicColumn
                    v-if="dataSourceType === 'wp-posts'"
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
            <el-tab-pane label="Advanced Settings" name="advanced" class="ninja_modal_body px-[10px] pt-[10px]">
                <div class="advanced-settings">
                    <div class="nt-instruction mb-[20px] mt-[5px]" v-if="!hasPro">
                        <h3 class="nt-modal-title mb-3">{{ $t('Advanced Column Settings') }}</h3>
                        <div class="text-[14px] font-[400] text-[#525866]">
                            {{ $t(`Customize your table's column width, custom CSS class, column styling with this Pro feature.`) }}
                            <a class="nt-link" target="_blank" href="https://ninjatables.com/docs/advanced-settings/">
                                {{ $t('View documentation') }}
                            </a>
                        </div>
                        <div class="mt-4">
                            <GetPro />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-5">
                        <!-- Extra classes -->
                        <div class="flex flex-col mt-2">
                            <label class="nt-form-label">
                                {{ $t("Extra Classes") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>Extra Classes</h3>

                                        <p>
                                            {{ $t('Add extra classes to this column. This will be applied for the entire column') }}
                                        </p>
                                    </template>
                                    <el-icon class="tooltip-icon-color">
                                        <InfoFilled/>
                                    </el-icon>
                                </el-tooltip>
                            </label>

                            <!--                        <el-input size="small" v-model="model.classes" :disabled="!hasPro"/>-->
                            <NinjaInput
                                v-model="model.classes"
                                :disabled="!hasPro"
                            />
                        </div>

                        <!-- Max width -->
                        <div class="flex flex-col mt-2">
                            <label class="nt-form-label">
                                {{ $t("Column Width") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>{{ $t('Column Width') }}</h3>
                                        <p>{{ $t('Set the column width. This will be applied for the entire column') }}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color">
                                        <InfoFilled/>
                                    </el-icon>
                                </el-tooltip>
                            </label>
                            <!-- width input -->
                            <div>
                                <el-input
                                    :disabled="!hasPro"
                                    v-model="model.width"
                                    class="nt-input-append"
                                >
                                    <template #append>
                                        <el-select v-model="model.maxWidthUnit" placeholder="Select"
                                                   style="width: 75px">
                                            <el-option label="%" value="%"/>
                                            <el-option label="px" value="px"/>
                                        </el-select>
                                    </template>
                                </el-input>
                            </div>
                        </div>

                        <!-- Header Text alignment -->
                        <div class="flex flex-col mt-4">
                            <label class="nt-form-label">
                                {{ $t("Header Text Align") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3> {{ $t("Header Text Alignment") }}</h3>
                                        <p>{{ $t('Choose the text alignment. This will be applied only for header') }}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color">
                                        <InfoFilled/>
                                    </el-icon>
                                </el-tooltip>
                            </label>

                            <el-select :empty-values="[undefined, null]" class="ninja-select" v-model="model.textAlign" placeholder="Text Align">
                                <el-option
                                    v-for="(alignmentLabel, alignmentVal) in alignmentOptions"
                                    :key="alignmentVal"
                                    :label="alignmentLabel"
                                    :value="alignmentVal">
                                </el-option>
                            </el-select>
                        </div>

                        <!-- Content Text alignment -->
                        <div class="flex flex-col mt-4">
                            <label class="nt-form-label">
                                {{ $t("Row Content Text Align") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>Content Text Alignment</h3>
                                        <p> {{ $t('Choose the text alignment for Column Rows') }}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled/></el-icon>
                                </el-tooltip>
                            </label>


                            <el-select :empty-values="[undefined, null]" class="ninja-select" v-model="model.contentAlign" placeholder="Content Alignment">
                                <el-option
                                    v-for="(alignmentLabel, alignmentVal) in contentAlignmentOptions"
                                    :key="alignmentVal"
                                    :label="alignmentLabel"
                                    :value="alignmentVal">
                                </el-option>
                            </el-select>
                        </div>
                    </div>

                    <!-- Enable / Disable Table HTML -->
                    <div class="mt-4">
                        <el-checkbox
                            :disabled="!hasPro"
                            v-model="model.enable_html_content"
                            :value="true"
                            label="Enable HTML Table Header Content" />
                    </div>

                    <!-- model.header_html_content -->
                    <div v-if="model.enable_html_content" class="flex flex-col mt-2">
                        <label class="nt-form-label">
                            {{ $t("Header HTML Content") }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>Header HTML Content</h3>

                                    <p>{{ $t('Provide content for table column header if you want to show html content.') }}</p>
                                </template>
                                <el-icon class="tooltip-icon-color"><InfoFilled/></el-icon>
                            </el-tooltip>
                        </label>

                        <wp_editor v-model="model.header_html_content"></wp_editor>
                    </div>

                    <!--Filterable Column-->
                    <div class="flex flex-col mt-4">
                        <label class="nt-form-label">
                            {{ $t("Filterable") }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>Filterable</h3>

                                    <p>{{ $t('If You enable this then this column data will not be filterable at the frontend.') }}</p>
                                </template>
                                <el-icon class="tooltip-icon-color"><InfoFilled/></el-icon>
                                </el-tooltip>
                        </label>

                        <div class="flex items-center gap-2">
                            <el-switch
                                size="small"
                                v-model="model.unfilterable"
                                :active-value="'yes'"
                                :inactive-value="'no'"
                                :disabled="!hasPro"
                            />
                            <p class="text-[14px] font-[400]" :class="{ 'text-[#a8abb2]': !hasPro }">{{ $t('Disable frontend search for this column data') }}</p>
                        </div>
                    </div>

                    <!--Sortable Column-->
                    <div class="flex flex-col mt-4">
                        <label class="nt-form-label">
                            {{ $t("Sortable") }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <template #content>
                                    <h3>Sortable</h3>

                                    <p>{{ $t('If You enable this then this column data will not be sortable at the frontend.') }}</p>
                                </template>
                                <el-icon class="tooltip-icon-color"><InfoFilled/></el-icon>
                            </el-tooltip>
                        </label>

                        <div class="flex items-center gap-2">
                            <el-switch
                                size="small"
                                v-model="model.unsortable"
                                :active-value="'yes'"
                                :inactive-value="'no'"
                                :disabled="!hasPro"
                            />
                            <p class="text-[14px] font-[400]" :class="{ 'text-[#a8abb2]': !hasPro }">{{ $t('Disable frontend sorting for this column') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-5">
                        <!-- Column Background Color-->
                        <div class="flex flex-col mt-4">
                            <label class="nt-form-label">
                                {{ $t("Background Color") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>Column Background Color</h3>

                                        <p>{{ $t('You can set Column Background color of this particular column that will show on the frontend table.') }}</p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled/></el-icon>
                                </el-tooltip>
                            </label>

                            <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                <ColorPicker :disabled="!hasPro" v-model="model.background_color" />
                            </div>
                        </div>

                        <!--Column Text Color-->
                        <div class="flex flex-col mt-4">
                            <label class="nt-form-label">
                                {{ $t("Column Text Color") }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <template #content>
                                        <h3>{{ $t("Column Text Color") }}</h3>
                                        <p>
                                            {{ $t('You can set Column Text color of this particular column that will show on the frontend table.') }}
                                        </p>
                                    </template>
                                    <el-icon class="tooltip-icon-color"><InfoFilled/></el-icon>
                                </el-tooltip>
                            </label>

                            <div class="border-solid border border-[lightgray] px-[5] py-1 w-full rounded-lg">
                                <ColorPicker :disabled="!hasPro" v-model="model.text_color" />
                            </div>
                        </div>
                    </div>
                </div>
            </el-tab-pane>

            <!-- Conditional Settings -->
            <el-tab-pane label="Conditional Formatting" name="conditional" class="ninja_modal_body px-[10px] pt-[16px]">
                <condition :column="model" :has-pro="hasPro"/>
            </el-tab-pane>

            <!-- Transform Value -->
            <el-tab-pane label="Transform Value" name="transformer" class="ninja_modal_body px-[10px] pt-[16px]">
                <content-transformer :settings="settings" :columns="columns" :column="model"/>
            </el-tab-pane>

            <!-- Buttons -->
            <div v-if="!updating" class="flex justify-end px-[10px] py-4 gap-2">
                <div class="flex align-center gap-2">
                    <NinjaButton
                        @click.prevent="cancel"
                        v-if="!hideCancel"
                        type="secondary"
                        :btnText="$t('Cancel')"
                    />

                    <NinjaButton
                        @click.prevent="addColumn"
                        :loading="doingAjax"
                        type="primary"
                        :btnText="$t('Add Column')"
                    />
                </div>
            </div>

            <div v-else class="flex justify-between items-center px-[10px] py-4">
                <div>
                    <el-button
                        link
                        v-if="!hideDelete"
                        type="danger"
                        class="underline"
                        @click="handleColumnDelete"
                    >
                        {{ $t('Delete Column') }}
                    </el-button>
                </div>


                <div class="flex items-center gap-2">
                    <NinjaButton
                        v-show="hideCancel"
                        @click.prevent="cancel"
                        type="secondary"
                        :btnText="$t('Cancel')"
                    />

                    <NinjaButton
                        :loading="doingAjax"
                        @click.prevent="store"
                        :btnText="$t('Update')"
                    />
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
    import { useEventBus } from './../../../eventBus';
    import NinjaButton from "../../../@ui-utils/NinjaButton.vue";
    import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
    import ColorPicker from "../../../@ui-utils/ColorPicker.vue";


    export default {
        name: "ColumnsEditor",
        components: {
            ColorPicker,
            NinjaInput,
            NinjaButton,
            GetPro,
            'wp_editor': wpEditor,
            'condition': conditional,
            'content-transformer': ContentTransformer,
            DynamicWooColumn,
            WPPostDynamicColumn,
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
                bus : useEventBus(),
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
                        this.model.link_type = 'none';
                    }
                } else if(this.model.data_type == 'number' && !this.model.decimal_system) {
                    if(this.model.decimalSeparator == ',') {
                        this.model.decimal_system = 'eu';
                    } else {
                        this.model.decimal_system = 'us';
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

            handleColumnDelete() {
                this.$confirm(this.$t('Are you sure to delete this column?'), 'Warning', {
                    confirmButtonText: this.$t('Yes, Delete'),
                    cancelButtonText: this.$t('Cancel'),
                    type: 'warning',
                    customClass: 'nt-delete-confirm',
                    confirmButtonClass: 'nt-delete-confirm-btn',
                    cancelButtonClass: 'nt-delete-cancel-btn'
                }).then(() => {
                    this.deleteColumn();
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: this.$t('Delete canceled')
                    });
                    this.hideDelete = false;
                });
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
                    this.bus.emit('show_pro_popup', 1);
                }
            },
            changeDecimalStyle() {
                if(this.model.decimal_system == 'us') {
                    this.model.decimalSeparator = '.';
                    this.model.thousandSeparator = ',';
                } else {
                    this.model.decimalSeparator = ',';
                    this.model.thousandSeparator = '.';
                }
            }
        },
        mounted() {
            if (this.dataSourceType === 'default') {
                this.dataTypesOptions.image = 'Image/File/Lightbox';
            }
            this.dataTypesOptions.button = this.$t('Button/Link');

            if (!this.model) return;
            if (!this.model.hasOwnProperty('dateFormat')) {
                this.model.dateFormat = "";
            }
            this.model.dateFormat = this.model.dateFormat || "";
            this.model.enable_html_content = ['true', true].indexOf(this.model.enable_html_content) !== -1;
            this.model.header_html_content = this.model.header_html_content || '';
            if (!this.model.contentAlign) {
                this.model.contentAlign = '';
            }
            if (!this.model.textAlign) {
                this.model.textAlign = '';
            }

            if (!this.model.maxWidthUnit) {
                this.model.maxWidthUnit = 'px';
            }
            if (!this.model.timeFormat) {
                this.model.timeFormat = '';
            }

            if (!this.model.relAttributes) {
                this.model.relAttributes = [];
            }
            if (!this.model.force_download) {
                this.model.force_download = '';
            }
            this.bus.on('tableDoingAjax', (status) => {
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
