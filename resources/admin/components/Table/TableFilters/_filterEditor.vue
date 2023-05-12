<template>
    <el-form ref="form" :model="activeEditor" label-width="250px" class="form-wrapper">
        <el-form-item>
            <template slot="label">
                {{ $t('Filter Title') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <div slot="content">
                        <h3>Filter Title</h3>
                        <p>Just a Name to identify your Filter</p>
                    </div>
                    <i class="el-icon-info el-text-info"></i>
                </el-tooltip>
            </template>
            <el-input size="small" v-model="activeEditor.title" />
        </el-form-item>

        <el-form-item v-if="activeEditor.type != 'reset_filter'">
            <template slot="label">
                {{ $t('Filter Label') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <div slot="content">
                        <h3>Prefix</h3>
                        <p>This will show on your Table Filter</p>
                    </div>
                    <i class="el-icon-info el-text-info"></i>
                </el-tooltip>
            </template>
            <el-input size="small" v-model="activeEditor.filter_prefix" />
            <small>Keep it blank if you don't need any filter instruction at the frontend</small>
        </el-form-item>

        <el-form-item>
            <template slot="label">
                {{ $t('Filter UI Type') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <div slot="content">
                        <h3>Filter UI</h3>
                        <p>Select the filter type that you want to show the filter in the frontend</p>
                    </div>
                    <i class="el-icon-info el-text-info"></i>
                </el-tooltip>
            </template>
            <el-radio-group class="spaced" v-model="activeEditor.type">
                <el-radio label="select">Select Dropdown</el-radio>
                <el-radio label="radio">Radio</el-radio>
                <el-radio label="checkbox">Checkbox</el-radio>
                <el-radio label="date_picker">Date Picker</el-radio>
                <el-radio label="date_range">Date Range</el-radio>
                <el-radio label="text_input">Text Input</el-radio>
                <el-radio label="number_range">Number Range</el-radio>
                <el-radio label="reset_filter">Reset Filter Button</el-radio>
            </el-radio-group>
        </el-form-item>

        <el-form-item v-if="need_placeholder">
            <template slot="label">
                {{ $t('Placeholder') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <div slot="content">
                      <h3>Default Placeholder</h3>
                        <p>This will show on as default placeholder to reset the label ( Ex: All )</p>
                    </div>
                    <i class="el-icon-info el-text-info"></i>
                </el-tooltip>
            </template>
            <el-input size="small" v-model="activeEditor.placeholder"></el-input>
        </el-form-item>

        <template v-if="activeEditor.type == 'select'">
            <el-form-item>
                <template slot="label">
                    {{ $t('Value Type') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <div slot="content">
                          <h3>Value</h3>
                            <p>Select How the value will be populated to the select dropdown</p>
                        </div>
                        <i class="el-icon-info el-text-info"></i>
                    </el-tooltip>
                </template>
                <el-radio-group size="mini" v-model="activeEditor.select_value_type">
                    <el-radio-button label="manual">Manual Data</el-radio-button>
                    <el-radio-button label="dynamic_data">Dynamic Data from Table Column</el-radio-button>
                </el-radio-group>
            </el-form-item>

            <template v-if="!is_manual_select_options && activeEditor.select_value_type">
                <el-form-item>
                    <template slot="label">
                        {{ $t('Target Column') }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                              <h3>Column</h3>
                                <p>Select Column That you want to populate data</p>
                            </div>
                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>
                    <el-radio-group class="spaced" v-model="activeEditor.dynamic_select_column">
                        <el-radio v-for="column in current_columns" :key="column.key" :label="column.key">{{column.name}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item>
                    <el-checkbox true-label="yes" false-label="no" v-model="activeEditor.parse_comma_separated"> Parse Comma Separated Words</el-checkbox>
                </el-form-item>
                <el-form-item>
                    <el-checkbox true-label="yes" false-label="no" v-model="activeEditor.disable_auto_sorting"> Disable automatic filter value sorting</el-checkbox>
                </el-form-item>

                <template v-if="activeEditor.disable_auto_sorting != 'yes'">
                    <el-form-item label="Sort Dynamic Value as: ">
                        <el-radio-group v-model="activeEditor.sorting_type">
                            <el-radio label="asc">Ascending Way</el-radio>
                            <el-radio label="desc">Descending Way</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="Sort Algorithm">
                        <el-radio-group v-model="activeEditor.sorting_method">
                            <el-radio label="text">As Text Basis</el-radio>
                            <el-radio label="numeric">As Numeric Basis</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </template>
            </template>

            <el-form-item>
                <el-checkbox true-label="yes" false-label="no" v-model="activeEditor.is_multi_select">Enable Multi-Select</el-checkbox>
            </el-form-item>

        </template>
        <template v-if="has_filter_option || is_manual_select_options">
            <el-form-item>
                <template slot="label">
                    {{ $t('Filter Options') }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <div slot="content">
                            <h3>Options</h3>
                            <p>Provide the values that you want to show on the frontend. Your values should match your table cell data</p>
                        </div>
                        <i class="el-icon-info el-text-info"></i>
                    </el-tooltip>
                </template>
                <key-pair-options v-model="activeEditor.options"></key-pair-options>
            </el-form-item>
        </template>

        <template v-if="activeEditor.type == 'date_picker'">
            <el-form-item >
                <template slot="label">
                    {{ $t('Date Filter Operator') }}
                </template>
                <el-radio-group v-model="activeEditor.filter_operator">
                    <el-radio label="less">Less Than Equal</el-radio>
                    <el-radio label="greater">Greater Than Equal</el-radio>
                    <el-radio label="equal">Equal</el-radio>
                </el-radio-group>
            </el-form-item>
        </template>

        <template v-else-if="activeEditor.type == 'date_range' || activeEditor.type == 'number_range'">
            <el-form-item >
                <template slot="label">
                    {{ $t('From Placeholder') }}
                </template>
                <el-input size="small" placeholder="From Placeholder" v-model="activeEditor.from_placeholder" />
            </el-form-item>
            <el-form-item>
                <template slot="label">
                    {{ $t('To Placeholder') }}
                </template>
                <el-input size="small" placeholder="To Placeholder" v-model="activeEditor.to_placeholder" />
            </el-form-item>
        </template>

        <el-form-item v-if="activeEditor.type == 'text_input'">
            <template slot="label">
                {{ $t('Filter Prefix') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <div slot="content">
                        <h3>Filter Prefix</h3>
                        <p>You can use filter prefix to append the value of user inputed value. It will join with the value and perform the search</p>
                    </div>
                    <i class="el-icon-info el-text-info"></i>
                </el-tooltip>
            </template>
            <el-input size="small" placeholder="Filter Prefix" v-model="activeEditor.filter_value_prefix" />
        </el-form-item>

        <el-form-item v-if="need_filter_columns">
            <template slot="label">
                {{ $t('Filter Columns') }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <div slot="content">
                        <h3>Columns</h3>
                        <p>Select the columns that you want to apply this filter</p>
                    </div>
                    <i class="el-icon-info el-text-info"></i>
                </el-tooltip>
            </template>
            <el-checkbox-group v-if="current_columns.length" v-model="activeEditor.columns">
                <el-checkbox v-for="column in current_columns" :key="column.key" :label="column.key">{{column.name}}</el-checkbox>
            </el-checkbox-group>
            <div v-else>
                Sorry, No corresponding columns found based on your selection and column's data type
            </div>
        </el-form-item>

        <el-form-item v-if="activeEditor.type == 'reset_filter'">
            <template slot="label">
                {{ $t('Button Text') }}
            </template>
            <el-input size="mini" v-model="activeEditor.placeholder" />
        </el-form-item>

        <el-form-item>
            <el-checkbox true-label="yes" false-label="no" v-model="activeEditor.strict">Enable Strict Mode (If Enable, Ninja Table will try to match exact value)</el-checkbox>
        </el-form-item>
    </el-form>
</template>

<script type="text/babel">
    import KeyPairOptions from './_key_pair_options'
    import each from 'lodash/each'
    export default {
        name: 'FilterEditor',
        components: {
            KeyPairOptions
        },
        props: ['activeEditor', 'columnKeyPairs', 'columns'],
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
                    this.$set(this.activeEditor, 'select_value_type', 'manual');
                }
                if(!Array.isArray(this.activeEditor.columns)) {
                    this.$set(this.activeEditor, 'columns', []);
                }
            }
        },
        mounted() {
            if(!Array.isArray(this.activeEditor.columns)) {
                this.$set(this.activeEditor, 'columns', []);
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
