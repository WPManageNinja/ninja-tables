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
            <div class="text-[#0E121B] text-[16px] font-[500] mt-4 mb-2">{{ $t('Default Styling Library') }}</div>
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
                                            <el-tooltip placement="right-start" effect="light"
                                                        :content="tableStyle.description">
                                            <el-icon class="tooltip-icon-color">
                                                <InfoFilled/>
                                            </el-icon>
                                        </el-tooltip>
                                        </span>
                        <el-switch
                            size="small"
                            :model-value="isStyleActive(tableStyle.key)"
                            @change="(val) => toggleStyle(tableStyle.key, val)"
                            :id="'table_style_' + tableStyle.key">
                        </el-switch>
                    </div>
                </label>
            </div>

            <div class="text-[#0E121B] text-[16px] font-[500] mt-4 mb-2">{{ $t('Default Features') }}</div>
            <div class="form_group label-normalize">
                <div for="show_title" class="my-1 flex items-center justify-between">
                                        <span>
                                            {{ $t('Show Table Title') }}
                                            <el-tooltip placement="top-end" effect="light"
                                                        content="Enable this if you want to show table title in frontend">
                                                <el-icon class="tooltip-icon-color">
                                                    <InfoFilled/>
                                                </el-icon>
                                            </el-tooltip>
                                        </span>
                    <el-switch
                        size="small"
                        v-model="default_settings.show_title"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'show_title'">
                    </el-switch>
                </div>

                <div for="show_description" class="my-1 flex items-center justify-between">
                                        <span>
                                            {{ $t('Show Table Description') }}
                                            <el-tooltip placement="top-end" effect="light"
                                                        content="Enable this if you want to show table description in frontend">
                                                <el-icon class="tooltip-icon-color">
                                                    <InfoFilled/>
                                                </el-icon>
                                            </el-tooltip>
                                        </span>
                    <el-switch
                        size="small"
                        v-model="default_settings.show_description"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'show_description'">
                    </el-switch>
                </div>

                <div for="enable_search" class="my-1 flex items-center justify-between">
                                        <span>
                                            {{ $t('Enable the visitor to filter or search the table.') }}
                                        </span>
                    <el-switch
                        size="small"
                        v-model="default_settings.enable_search"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'enable_search'">
                    </el-switch>
                </div>

                <div for="column_sorting" class="my-1 flex items-center justify-between">
                    <span>{{ $t('Enable sorting of the table by the visitor') }}</span>
                    <el-switch
                        size="small"
                        v-model="default_settings.column_sorting"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'column_sorting'">
                    </el-switch>
                </div>

                <div for="hide_all_borders" class="my-1 flex items-center justify-between">
                    <span>{{ $t('Hide All Borders') }}</span>
                    <el-switch
                        size="small"
                        v-model="default_settings.hide_all_borders"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'hide_all_borders'">
                    </el-switch>
                </div>
            </div>

            <div class="text-[#0E121B] text-[16px] font-[500] mt-4 mb-2">{{ $t('Default Pagination Setting') }}</div>
            <div class="form_group label-normalize">
                <div for="hide_pagination" class="flex items-center justify-between">
                    <span>{{ $t('Hide Pagination (Show all data at once)') }}</span>
                    <el-switch
                        size="small"
                        v-model="default_settings.show_all"
                        :active-value="1"
                        :inactive-value="0"
                        :id="'hide_pagination'">
                    </el-switch>
                </div>

                <div class="w-1/2">
                    <label class="font-[400] block">{{ $t('Items Per Page') }}</label>
                    <el-input-number style="height: 40px; width: 100%;" :min="1" v-model="default_settings.perPage"
                                     :disabled="default_settings.show_all == true || default_settings.show_all == '1'"
                    />
                </div>
            </div>

            <div class="text-[#0E121B] text-[16px] font-[500] mt-4 mb-2">{{ $t('Default Font Setting') }}</div>
            <div class="grid grid-cols-2 items-center gap-5">
                <div>
                    <label class="font-[400]">{{ $t('Font Family') }}</label>
                    <el-select class="ninja-select" v-model="default_settings.table_font_family"
                               :placeholder="$t('Select Font')">
                        <el-option v-for="(family, key) in fontFamily" :key="key"
                                   :label="family === 'inherit' ? 'theme-font' : family"
                                   :value="family"></el-option>
                    </el-select>
                </div>

                <div>
                    <label class="font-[400] block">{{ $t('Font Size') }}</label>
                    <el-input-number style="height: 40px; width: 100%;" :min="1" :max="50"
                                     v-model="default_settings.table_font_size"></el-input-number>
                </div>
            </div>


            <div class="text-[#0E121B] text-[16px] font-[500] mt-4 mb-2">{{ $t('Default Table Color') }}</div>
            <div class="w-1/2">
                <label class="font-[400]">{{ $t('Select Color') }}</label>
                <el-select class="ninja-select" v-model="default_settings.table_color" placeholder="Select Color">
                    <el-option v-for="(colorName, colorKey) in tableColors" :key="colorKey"
                               :label="colorName"
                               :value="colorKey"></el-option>
                </el-select>
            </div>

            <div class="flex justify-end mt-4">
                <NinjaButton type="primary" size="small" @click="store" :btn-text="$t('Save Settings')"/>
            </div>
        </div>
    </div>
</template>

<script>
import {tableLibs} from '../../data/data'
import intersection from 'lodash/intersection';
import forEach from 'lodash/forEach'
import NinjaButton from "../../@ui-utils/NinjaButton.vue";
import NinjaInput from "../../@ui-utils/NinjaInput.vue";

export default {
    name: "Privacy",
    components: {NinjaInput, NinjaButton},
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
                    const switchFields = ['show_title', 'show_description', 'enable_search', 'column_sorting', 'hide_all_borders', 'table_font_size', 'perPage'];
                    switchFields.forEach(field => {
                        if (settings[field] !== undefined) {
                            settings[field] = parseInt(settings[field]);
                        }
                    });

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


