<template>
    <el-form ref="form" :model="activeEditor" class="p-5">
        <div class="grid grid-cols-2 gap-x-5">
            <el-form-item>
                <label class="nt-form-label">
                    {{ $t('Filter Title') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Filter Title</h3>
                            <p>Just a Name to identify your Filter</p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </label>
                <NinjaInput v-model="activeEditor.title" :placeholder="$t('Enter Filter Title')" />
            </el-form-item>

            <el-form-item v-if="activeEditor.type !== 'reset_filter'">
                <label class="nt-form-label">
                    {{ $t('Filter Label') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Prefix</h3>
                            <p>This will show on your Table Filter</p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </label>

                <NinjaInput v-model="activeEditor.filter_prefix" :placeholder="$t(`Keep it blank if you don't need any filter instruction at the frontend`)"/>
            </el-form-item>
        </div>

        <el-form-item>
            <label class="nt-form-label">
                {{ $t('Filter UI Type') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Filter UI</h3>
                        <p>Select the filter type that you want to show the filter in the frontend</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </label>

            <el-select v-model="activeEditor.type" class="ninja-select">
                <el-option label="Select Dropdown" value="select"/>
                <el-option label="Radio" value="radio"/>
                <el-option label="Checkbox" value="checkbox"/>
                <el-option label="Text Input" value="text_input"/>
                <el-option label="Date Picker" value="date_picker"/>
                <el-option label="Date Range" value="date_range"/>
                <el-option label="Number Range" value="number_range"/>
                <el-option label="Reset Filter Button" value="reset_filter"/>
            </el-select>
        </el-form-item>

        <el-form-item v-if="need_placeholder">
            <label class="nt-form-label">
                {{ $t('Placeholder') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                      <h3>Default Placeholder</h3>
                        <p>This will show on as default placeholder to reset the label ( Ex: All )</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </label>

            <NinjaInput v-model="activeEditor.placeholder" />
        </el-form-item>

        <div v-if="activeEditor.type === 'select'">
            <div class="flex flex-col">
                <label class="nt-form-label">
                    {{ $t('Value Type') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                          <h3>Value</h3>
                            <p>Select How the value will be populated to the select dropdown</p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </label>

                <el-radio-group class="ninja_tables_radio_group" v-model="activeEditor.select_value_type">
                    <el-radio border label="Manual Data" value="manual"/>
                    <el-radio border label="Dynamic Data from Table Column" value="dynamic_data"/>
                </el-radio-group>
            </div>

            <div v-if="!is_manual_select_options && activeEditor.select_value_type" class="flex flex-col">
                <div class="flex flex-col mt-4">
                    <label class="nt-form-label">
                        {{ $t('Target Column') }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                              <h3>Column</h3>
                                <p>Select Column That you want to populate data</p>
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </label>

                    <el-radio-group class="ninja_tables_radio_group" v-model="activeEditor.dynamic_select_column">
                        <el-radio
                            v-for="column in current_columns"
                            :key="column.key"
                            :label="column.key"
                            :value="column.name"
                        />
                    </el-radio-group>
                </div>

                <div class="mt-4">
                    <div class="flex items-center gap-2">
                        <el-switch
                            size="small"
                            :true-value="'yes'"
                            :false-value="'no'"
                            v-model="activeEditor.parse_comma_separated"
                        />
                        <p class="text-[14px] font-[400]"> {{ $t('Parse Comma Separated Words') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-2">
                    <el-switch
                        size="small"
                        :true-value="'yes'"
                        :false-value="'no'"
                        v-model="activeEditor.disable_auto_sorting"
                    />
                    <p class="text-[14px] font-[400]">{{ $t('Disable automatic filter value sorting') }}</p>
                </div>

                <div v-if="activeEditor.disable_auto_sorting != 'yes'" class="flex flex-col mt-4">
                    <div class="flex flex-col">
                        <label class="nt-form-label">{{ $t('Sort Dynamic Value as: ') }}</label>
                        <el-radio-group class="ninja_tables_radio_group" v-model="activeEditor.sorting_type">
                            <el-radio label="Ascending Way" value="asc" />
                            <el-radio label="Descending Way" value="desc" />
                        </el-radio-group>
                    </div>

                    <div class="flex flex-col mt-4">
                        <label class="nt-form-label">{{ $t('Sort Algorithm ') }}</label>
                        <el-radio-group class="ninja_tables_radio_group" v-model="activeEditor.sorting_method">
                            <el-radio label="As Text Basis" value="text" />
                            <el-radio label="As Numeric Basis" value="numeric" />
                        </el-radio-group>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="activeEditor.is_multi_select">
                   {{ $t('Enable Multi-Select') }}
                </el-checkbox>
            </div>
        </div>

        <div v-if="has_filter_option || is_manual_select_options">
            <el-form-item class="mt-4">
                <label class="nt-form-label">
                    {{ $t('Filter Options') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>{{ $t('Options') }}</h3>
                            <p>{{ $t('Provide the values that you want to show on the frontend.Your values should match your table cell data') }}</p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </label>
                <key-pair-options :value="activeEditor.options"></key-pair-options>
            </el-form-item>
        </div>

        <div v-if="activeEditor.type === 'date_picker'">
            <div class="flex flex-col">
                <label class="nt-form-label">
                    {{ $t('Date Filter Operator') }}
                </label>
                <el-radio-group class="ninja_tables_radio_group" v-model="activeEditor.filter_operator">
                    <el-radio label="Less Than Equal" value="less" />
                    <el-radio label="Equal" value="equal" />
                    <el-radio label="Greater Than Equal" value="greater" />
                </el-radio-group>
            </div>

            <div class="flex flex-col mt-4">
                <label class="nt-form-label">
                    {{ $t('First Day') }}

                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>First Day</h3>

                            <p>
                                The first day of the week, e.g. Sunday, Monday, etc.
                            </p>
                        </template>

                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </label>

                <el-select class="ninja-select" size="small" v-model="activeEditor.firstDayOfWeek" placeholder="First day of the week">
                    <el-option
                        v-for="(typeName, typeKey) in weekDays"
                        :key="typeKey"
                        :label="typeName"
                        :value="typeKey">
                    </el-option>
                </el-select>
            </div>
        </div>

        <div v-else-if="activeEditor.type === 'date_range' || activeEditor.type === 'number_range'">
            <div>
                <label>
                    {{ $t('From Placeholder') }}
                </label>

                <NinjaInput v-model="activeEditor.from_placeholder" :placeholder="$t('From Placeholder')"/>
            </div>

            <div class="mt-4">
                <label>
                    {{ $t('To Placeholder') }}
                </label>

                <NinjaInput v-model="activeEditor.to_placeholder" :placeholder="$t('To Placeholder')"/>
            </div>

            <div class="mt-4">
                <label class="nt-form-label">
                    {{ $t('First Day') }}

                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>First Day</h3>

                            <p>
                                The first day of the week, e.g. Sunday, Monday, etc.
                            </p>
                        </template>

                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </label>

                <el-select class="ninja-select" size="small" v-model="activeEditor.firstDayOfWeek" placeholder="First day of the week">
                    <el-option
                        v-for="(typeName, typeKey) in weekDays"
                        :key="typeKey"
                        :label="typeName"
                        :value="typeKey">
                    </el-option>
                </el-select>
            </div>
        </div>

        <div v-if="activeEditor.type === 'text_input'">
            <label class="nt-form-label">
                {{ $t('Filter Prefix') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Filter Prefix</h3>
                        <p>You can use filter prefix to append the value of user inputed value. It will join with the value and perform the search</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </label>

            <NinjaInput v-model="activeEditor.filter_value_prefix" :placeholder="$t('Filter Prefix')"/>
        </div>

        <div v-if="need_filter_columns" class="mt-4">
            <label class="nt-form-label">
                {{ $t('Filter Columns') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>{{ $t('Columns') }}</h3>
                        <p>{{ $t('Select the columns that you want to apply this filter') }}</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </label>

            <el-select
                class="ninja-select"
                v-if="current_columns.length"
                v-model="activeEditor.columns"
                multiple
                placeholder="Select columns"
            >
                <el-option
                    v-for="column in current_columns"
                    :key="column.key"
                    :label="column.key"
                    :value="column.name"
                />
            </el-select>

            <div v-else class="bg-[#EBF1FF] p-3 mt-[10px] mb-[25px] rounded-[8px]">
                Sorry, No corresponding columns found based on your selection and column's data type
            </div>
        </div>

        <div v-if="activeEditor.type === 'reset_filter'">
            <label class="nt-form-label">
                {{ $t('Button Text') }}
            </label>

            <NinjaInput v-model="activeEditor.placeholder" />
        </div>

        <el-form-item class="mt-4">
            <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="activeEditor.strict">Enable Strict Mode (If Enable, Ninja Table will try to match exact value)</el-checkbox>
        </el-form-item>
    </el-form>
</template>

<script>
    import KeyPairOptions from './_key_pair_options'
    import each from 'lodash/each'
    import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
    export default {
        name: 'FilterEditor',
        components: {
            NinjaInput,
            KeyPairOptions,
        },
        props: ['activeEditor', 'columnKeyPairs', 'columns'],
        data() {
            return {
                weekDays: {
                    0: 'Sunday',
                    1: 'Monday',
                    2: 'Tuesday',
                    3: 'Wednesday',
                    4: 'Thursday',
                    5: 'Friday',
                    6: 'Saturday'
                },
            }
        },
        computed: {
            current_columns() {
                if(this.activeEditor.type === 'date_picker' || this.activeEditor.type === 'date_range') {
                    let columns = [];
                    each(this.columns, (column) => {
                        if(column.data_type === 'date') {
                            columns.push(column);
                        }
                    });
                    return columns;
                } else if(this.activeEditor.type === 'number_range') {
                    let columns = [];
                    each(this.columns, (column) => {
                        if(column.data_type === 'number') {
                            columns.push(column);
                        }
                    });
                    return columns;
                }
                return this.columns;
            },
            has_filter_option() {
                return [
                    'radio',
                    'checkbox',
                ].indexOf(this.activeEditor.type) !== -1;
            },
            is_manual_select_options() {
                return this.activeEditor.type === 'select' && this.activeEditor.select_value_type === 'manual';
            },
            need_placeholder() {
                return [
                    'radio',
                    'select',
                    'date_picker',
                    'text_input'
                ].indexOf(this.activeEditor.type) !== -1;
            },
            need_filter_columns() {
                let isDynamic = this.activeEditor.type === 'select' && this.activeEditor.select_value_type === 'dynamic_data' || this.activeEditor.type === 'reset_filter';
                return !isDynamic;
            }
        },
        watch: {
            'activeEditor.type': function (value) {
                if(value === 'select') {
                    this.activeEditor.select_value_type = 'manual';
                }
                if(!Array.isArray(this.activeEditor.columns)) {
                    this.activeEditor.columns = [];
                }
            }
        },
        mounted() {
            if(!Array.isArray(this.activeEditor.columns)) {
                this.activeEditor.columns = [];
            }
        }
    }
</script>

<style scoped lang="scss">
    .spaced > .el-radio {
        margin-left: 0px;
        margin-right: 30px !important;
        line-height: 2;
    }
</style>
