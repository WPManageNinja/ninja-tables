<template>
    <el-form ref="form" :model="activeEditor" label-width="250px" class="form-wrapper">
        <el-form-item>
            <template #label>
                {{ $t('Filter Title') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Filter Title</h3>
                        <p>Just a Name to identify your Filter</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-input size="small" v-model="activeEditor.title" />
        </el-form-item>

        <el-form-item v-if="activeEditor.type != 'reset_filter'">
            <template #label>
                {{ $t('Filter Label') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Prefix</h3>
                        <p>This will show on your Table Filter</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-input size="small" v-model="activeEditor.filter_prefix" />
            <small>Keep it blank if you don't need any filter instruction at the frontend</small>
        </el-form-item>

        <el-form-item>
            <template #label>
                {{ $t('Filter UI Type') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Filter UI</h3>
                        <p>Select the filter type that you want to show the filter in the frontend</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-radio-group class="spaced" v-model="activeEditor.type">
                <el-radio label="Select Dropdown" value="select"/>
                <el-radio label="Radio" value="radio"/>
                <el-radio label="Checkbox" value="checkbox"/>
                <el-radio label="Date Picker" value="date_picker"/>
                <el-radio label="Date Range" value="date_range"/>
                <el-radio label="Text Input" value="text_input"/>
                <el-radio label="Number Range" value="number_range"/>
                <el-radio label="Reset Filter Button" value="reset_filter"/>
            </el-radio-group>
        </el-form-item>

        <el-form-item v-if="need_placeholder">
            <template #label>
                {{ $t('Placeholder') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                      <h3>Default Placeholder</h3>
                        <p>This will show on as default placeholder to reset the label ( Ex: All )</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-input size="small" v-model="activeEditor.placeholder"></el-input>
        </el-form-item>

        <template v-if="activeEditor.type == 'select'">
            <el-form-item>
                <template #label>
                    {{ $t('Value Type') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                          <h3>Value</h3>
                            <p>Select How the value will be populated to the select dropdown</p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>
                <el-radio-group size="small" v-model="activeEditor.select_value_type">
                    <el-radio-button label="Manual Data" value="manual" />
                    <el-radio-button label="Dynamic Data from Table Column" value="dynamic_data" />
                </el-radio-group>
            </el-form-item>

            <template v-if="!is_manual_select_options && activeEditor.select_value_type">
                <el-form-item>
                    <template #label>
                        {{ $t('Target Column') }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                              <h3>Column</h3>
                                <p>Select Column That you want to populate data</p>
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </template>
                    <el-radio-group class="spaced" v-model="activeEditor.dynamic_select_column">
                        <el-radio
                            v-for="column in current_columns"
                            :key="column.key"
                            :label="column.key"
                            :value="column.name"
                        />
                    </el-radio-group>
                </el-form-item>
                <el-form-item>
                    <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="activeEditor.parse_comma_separated"> Parse Comma Separated Words</el-checkbox>
                </el-form-item>
                <el-form-item>
                    <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="activeEditor.disable_auto_sorting"> Disable automatic filter value sorting</el-checkbox>
                </el-form-item>

                <template v-if="activeEditor.disable_auto_sorting != 'yes'">
                    <el-form-item label="Sort Dynamic Value as: ">
                        <el-radio-group v-model="activeEditor.sorting_type">
                            <el-radio label="Ascending Way" value="asc" />
                            <el-radio label="Descending Way" value="desc" />
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="Sort Algorithm">
                        <el-radio-group v-model="activeEditor.sorting_method">
                            <el-radio label="As Text Basis" value="text" />
                            <el-radio label="As Numeric Basis" value="numeric" />
                        </el-radio-group>
                    </el-form-item>
                </template>
            </template>

            <el-form-item>
                <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="activeEditor.is_multi_select">Enable Multi-Select</el-checkbox>
            </el-form-item>

        </template>
        <template v-if="has_filter_option || is_manual_select_options">
            <el-form-item>
                <template #label>
                    {{ $t('Filter Options') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Options</h3>
                            <p>Provide the values that you want to show on the frontend. Your values should match your table cell data</p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>
                <key-pair-options :value="activeEditor.options"></key-pair-options>
            </el-form-item>
        </template>

        <template v-if="activeEditor.type == 'date_picker'">
            <el-form-item >
                <template #label>
                    {{ $t('Date Filter Operator') }}
                </template>
                <el-radio-group v-model="activeEditor.filter_operator">
                    <el-radio label="Less Than Equal" value="less" />
                    <el-radio label="Equal" value="equal" />
                    <el-radio label="Greater Than Equal" value="greater" />
                </el-radio-group>
            </el-form-item>
            <el-form-item>
                <template #label>
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
                </template>
                <el-select class="nt_column_type_select" size="small" v-model="activeEditor.firstDayOfWeek" placeholder="First day of the week">
                    <el-option
                        v-for="(typeName, typeKey) in weekDays"
                        :key="typeKey"
                        :label="typeName"
                        :value="typeKey">
                    </el-option>
                </el-select>
            </el-form-item>
        </template>

        <template v-else-if="activeEditor.type == 'date_range' || activeEditor.type == 'number_range'">
            <el-form-item >
                <template #label>
                    {{ $t('From Placeholder') }}
                </template>
                <el-input size="small" placeholder="From Placeholder" v-model="activeEditor.from_placeholder" />
            </el-form-item>
            <el-form-item>
                <template #label>
                    {{ $t('To Placeholder') }}
                </template>
                <el-input size="small" placeholder="To Placeholder" v-model="activeEditor.to_placeholder" />
            </el-form-item>
            <el-form-item>
                <template #label>
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
                </template>
                <el-select class="nt_column_type_select" size="small" v-model="activeEditor.firstDayOfWeek" placeholder="First day of the week">
                    <el-option
                        v-for="(typeName, typeKey) in weekDays"
                        :key="typeKey"
                        :label="typeName"
                        :value="typeKey">
                    </el-option>
                </el-select>
            </el-form-item>
        </template>

        <el-form-item v-if="activeEditor.type == 'text_input'">
            <template #label>
                {{ $t('Filter Prefix') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Filter Prefix</h3>
                        <p>You can use filter prefix to append the value of user inputed value. It will join with the value and perform the search</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-input size="small" placeholder="Filter Prefix" v-model="activeEditor.filter_value_prefix" />
        </el-form-item>

        <el-form-item v-if="need_filter_columns">
            <template #label>
                {{ $t('Filter Columns') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Columns</h3>
                        <p>Select the columns that you want to apply this filter</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-checkbox-group v-if="current_columns.length" v-model="activeEditor.columns">
                <el-checkbox
                    v-for="column in current_columns"
                    :key="column.key"
                    :label="column.key"
                    :value="column.name"
                />
            </el-checkbox-group>
            <div v-else>
                Sorry, No corresponding columns found based on your selection and column's data type
            </div>
        </el-form-item>

        <el-form-item v-if="activeEditor.type == 'reset_filter'">
            <template #label>
                {{ $t('Button Text') }}
            </template>
            <el-input size="small" v-model="activeEditor.placeholder" />
        </el-form-item>

        <el-form-item>
            <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="activeEditor.strict">Enable Strict Mode (If Enable, Ninja Table will try to match exact value)</el-checkbox>
        </el-form-item>
    </el-form>
</template>

<script type="text/babel">
    import { InfoFilled } from '@element-plus/icons-vue';
    import KeyPairOptions from './_key_pair_options'
    import each from 'lodash/each'
    export default {
        name: 'FilterEditor',
        components: {
            KeyPairOptions,
            InfoFilled
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
                if(this.activeEditor.type == 'date_picker' || this.activeEditor.type == 'date_range') {
                    let columns = [];
                    each(this.columns, (column) => {
                        if(column.data_type == 'date') {
                            columns.push(column);
                        }
                    });
                    return columns;
                } else if(this.activeEditor.type == 'number_range') {
                    let columns = [];
                    each(this.columns, (column) => {
                        if(column.data_type == 'number') {
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
                return this.activeEditor.type == 'select' && this.activeEditor.select_value_type == 'manual';
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
                let isDynamic = this.activeEditor.type == 'select' && this.activeEditor.select_value_type == 'dynamic_data' || this.activeEditor.type == 'reset_filter';
                return !isDynamic;
            }
        },
        watch: {
            'activeEditor.type': function (value) {
                if(value == 'select') {
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
