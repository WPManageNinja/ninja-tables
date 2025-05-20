<template>
    <div class="nt-table-default-appearance">
        <div class="text-[18px] font-[600] text-[#0E121B]">{{ $t('Global Appearance Settings') }}</div>
        <div class="text-[14px] font-[400] text-[#0E121B] my-5">
            {{
                $t(`The following settings will be applied to the newly created tables. Of course, You can customize the
                    appearance settings to each table level.`)
            }}
        </div>

        <div v-loading="fetching" class="nt-table-default-appearance-content">
            <div class="text-[#0E121B] text-[16px] font-[400] mb-2">{{ $t('Default Styling Library') }}</div>
            <div class="flex w-fit rounded-[8px] bg-[#F5F7FA] px-2 py-2 gap-3">
                <div
                    @click="default_settings.css_lib = 'semantic_ui'"
                    :class="{'bg-white rounded-[8px] shadow-md shadow-gray-300': default_settings.css_lib === 'semantic_ui', 'px-2 py-1 cursor-pointer': true}"
                >
                    {{ $t('Semantic UI') }}
                </div>
                <div
                    @click="default_settings.css_lib = 'bootstrap4'"
                    :class="{'bg-white rounded-[8px] shadow-md shadow-gray-300': default_settings.css_lib === 'bootstrap4', 'px-2 py-1 cursor-pointer': true}"
                >
                    {{ $t('Bootstrap 4') }}
                </div>
                <div
                    @click="default_settings.css_lib = 'bootstrap3'"
                    :class="{'bg-white rounded-[8px] shadow-md shadow-gray-300': default_settings.css_lib === 'bootstrap3', 'px-2 py-1 cursor-pointer': true}"
                >
                    {{ $t('Bootstrap 3') }}
                </div>
            </div>
            <div v-if="availableStyles" class="my-2 form_group label-normalize">
                <label v-for="tableStyle in availableStyles" :key="tableStyle.key"
                       :for="'table_style_' + tableStyle.key">
                    <div class="flex items-center justify-between">
                                        <span>
                                            {{ tableStyle.title }}
                                            <el-tooltip placement="right-start" effect="light" :content="tableStyle.description">
                                            <el-icon class="tooltip-icon-color">
                                                <InfoFilled />
                                            </el-icon>
                                        </el-tooltip>
                                        </span>
                        <el-switch
                            :model-value="isStyleActive(tableStyle.key)"
                            @change="(val) => toggleStyle(tableStyle.key, val)"
                            :id="'table_style_' + tableStyle.key">
                        </el-switch>
                    </div>
                </label>
            </div>

            <div class="text-[#0E121B] text-[16px] font-[400] mb-2">{{ $t('Default Features') }}</div>
            <div class="form_group label-normalize mt-4">
                <div for="show_title" class="flex items-center justify-between">
                                        <span>
                                            {{ $t('Show Table Title') }}
                                            <el-tooltip placement="top-end" effect="light" content="Enable this if you want to show table title in frontend">
                                                <el-icon class="tooltip-icon-color">
                                                    <InfoFilled />
                                                </el-icon>
                                            </el-tooltip>
                                        </span>
                    <el-switch
                        v-model="default_settings.show_title"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'show_title'">
                    </el-switch>
                </div>

                <div for="show_description" class="flex items-center justify-between">
                                        <span>
                                            {{ $t('Show Table Description') }}
                                            <el-tooltip placement="top-end" effect="light" content="Enable this if you want to show table description in frontend">
                                                <el-icon class="tooltip-icon-color">
                                                    <InfoFilled />
                                                </el-icon>
                                            </el-tooltip>
                                        </span>
                    <el-switch
                        v-model="default_settings.show_description"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'show_description'">
                    </el-switch>
                </div>

                <div for="enable_search" class="flex items-center justify-between">
                                        <span>
                                            {{ $t('Enable the visitor to filter or search the table.') }}
                                        </span>
                    <el-switch
                        v-model="default_settings.enable_search"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'enable_search'">
                    </el-switch>
                </div>

                <div
                    for="column_sorting" class="flex items-center justify-between">
                          <span>{{ $t('Enable sorting of the table by the visitor') }}</span>
                    <el-switch
                        v-model="default_settings.column_sorting"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'column_sorting'">
                    </el-switch>
                </div>

                <div for="hide_all_borders" class="flex items-center justify-between">
                    <span>{{$t('Hide All Borders')}}</span>
                    <el-switch
                        v-model="default_settings.hide_all_borders"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'hide_all_borders'">
                    </el-switch>
                </div>
            </div>
        </div>
    </div>


    <div class="privacy">

        <div v-loading="fetching" class="ninja_content">
            <div class="ninja_block">

                <div class="form_group" style="max-width: 400px">
                    <h3>Default Table Color</h3>
                    <select class="form_control" v-model="default_settings.table_color">
                        <option v-for="(colorName, colorKey) in tableColors" :key="colorKey" :value="colorKey">
                            {{ colorName }}
                        </option>
                    </select>
                </div>

                <div class="form_group">
                    <h3>Default Pagination Setting</h3>
                    <el-switch
                        inactive-color="gray"
                        active-text="Hide Pagination (Show all data at once)"
                        active-value="1" inactive-value="0"
                        v-model="default_settings.show_all"></el-switch>
                </div>
            </div>

            <div class="form_group" style="max-width: 400px">
                <label for="items_per_page">{{ $t('Pagination Items Per Page') }}</label>
                <input id="items_per_page" class="form_control" type="number"
                       v-model="default_settings.perPage"
                       :disabled="default_settings.show_all == true || default_settings.show_all == '1'"/>
            </div>

            <div class="font-setting">
                <h3>Default Font Setting</h3>
                <div class="font form_group ">
                    <label>{{ $t('Font Family') }}</label>
                    <select class="form_control" v-model="default_settings.table_font_family">
                        <option v-for="(family, key) in fontFamily" :key="key"
                                :label="family === 'inherit' ? 'theme-font' : family" :value="family"></option>
                    </select>
                </div>
                <div class="font form_group" style="max-width: 400px">
                    <label>{{ $t('Font Size') }}</label>
                    <input class="form_control" type="number" :min="1" :max="50"
                           v-model="default_settings.table_font_size"/>
                </div>
            </div>
            <div style="margin-top: 30px" class="form-group">
<!--                <el-button :loading="saving" @click="store" type="primary" size="small">Update</el-button>-->
                <NinjaButton type="primary" size="small" @click="store" :btn-text="$t('Update')" />
            </div>
        </div>
    </div>
</template>

<script>
import {tableLibs} from '../../data/data'
import intersection from 'lodash/intersection';
import forEach from 'lodash/forEach'
import NinjaButton from "../../@ui-utils/NinjaButton.vue";

export default {
    name: "Privacy",
    components: {NinjaButton},
    data() {
        return {
            fetching: false,
            saving: false,
            tableLibs: {},
            default_settings: {},
            fontFamily: ['inherit', 'cursive', 'fantasy', 'monospace', 'sans-serif', 'serif', 'system-ui', 'ui-monospace', 'ui-rounded', 'ui-sans-serif', 'ui-serif']
        };
    },
    computed: {
        tableColors() {
            let lib = tableLibs();
            return lib.footable.colors;
        },
        table_styles() {
            let lib = tableLibs();
            return lib.footable.css_libs;
        },
        availableStyles() {
            let lib = this.table_styles[this.default_settings.css_lib];
            if (lib)
                return lib.styles;
            return false;
        }
    },
    methods: {
        isStyleActive(styleKey) {
            if (!Array.isArray(this.default_settings.css_classes)) {
                this.default_settings.css_classes = [];
            }
            return this.default_settings.css_classes.includes(styleKey);
        },
        toggleStyle(styleKey, isActive) {
            if (!Array.isArray(this.default_settings.css_classes)) {
                this.default_settings.css_classes = [];
            }

            // Create a new array to maintain reactivity
            const updatedStyles = [...this.default_settings.css_classes];

            if (isActive && !updatedStyles.includes(styleKey)) {
                updatedStyles.push(styleKey);
            } else if (!isActive && updatedStyles.includes(styleKey)) {
                const index = updatedStyles.indexOf(styleKey);
                updatedStyles.splice(index, 1);
            }

            // Update the default_settings
            this.default_settings.css_classes = updatedStyles;
        },
        get() {
            this.fetching = true;
            this.$get('tables/tools/default-settings')
                .then(response => {
                    const settings = response.data.default_settings;
                    
                    // Convert string values to numbers for switch components
                    if (settings.show_title) {
                        settings.show_title = parseInt(settings.show_title);
                    }
                    if (settings.show_description) {
                        settings.show_description = parseInt(settings.show_description);
                    }
                    if (settings.enable_search) {
                        settings.enable_search = parseInt(settings.enable_search);
                    }
                    if (settings.column_sorting) {
                        settings.column_sorting = parseInt(settings.column_sorting);
                    }
                    if (settings.hide_all_borders) {
                        settings.hide_all_borders = parseInt(settings.hide_all_borders);
                    }
                    
                    this.default_settings = settings;
                })
                .catch(e => {
                })
                .finally(() => {
                    this.fetching = false;
                });
        },
        store() {
            this.saving = true;
            let validStyles = [];
            forEach(this.availableStyles, (style) => {
                validStyles.push(style.key);
            });
            this.default_settings.css_classes = intersection(validStyles, this.default_settings.css_classes);
            this.$post('tables/tools/default-settings', {
                default_settings: this.default_settings
            })
                .then(response => {
                    this.$message.success({
                        showClose: true,
                        message: response.data.message,
                        type: "success"
                    });
                })
                .catch(e => {
                })
                .finally(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        this.get();
    }
};
</script>


