<template>
    <div class="column-condition-config">
        <div v-if="hasPro" class="conditional-settings-header">
            <div class="conditional-settings-title">
                Customize your table's appearances based on the cell value. Add as many conditions as you like. <a
                target="_blank" href="https://ninjatables.com/docs/conditional-formatting/">View
                Documentation</a>
            </div>
            <el-button size="small" type="info" @click="addCondition" :disabled="!hasPro">
                Add Condition
            </el-button>
        </div>

        <div v-else class="conditional-settings-header ninja_table_inline_upgrade">
            <div class="conditional-settings-title">
                <H3>Conditional Formatting</H3>
                <p>
                    Customize your table's appearances based on the cell value. Add as many conditions as you like.
                    Conditional Formatting is a pro feature which can be enabled by using Ninja Table pro Add-on. Ninja
                    Table Pro has lots of features that will help you to build any type of Tables.
                </p>
               <get-pro size="small"/>
            </div>
        </div>

        <hr>
        <el-row v-for="(condition, index) in column.conditions" :key="index">
            <el-col :sm="2" :md="2">
                <div class="if-cell-value">If Cell Value</div>
            </el-col>

            <el-col :sm="5" :md="5">
                <el-select v-model="condition.conditionalOperator"
                           :disabled="!hasPro"
                           size="small"
                           style="width: 100%"
                >

                    <el-option label="Equal" value="equal"></el-option>
                    <el-option label="Not Equal" value="not-equal"></el-option>

                    <el-option
                        v-if="['number', 'date'].indexOf(column.data_type) == -1"
                        label="Contains"
                        value="contains"
                    ></el-option>

                    <el-option
                        v-if="['number', 'date'].indexOf(column.data_type) == -1"
                        label="Does Not Contain"
                        value="does-not-contain"
                    ></el-option>

                    <el-option
                        v-if="['number', 'date'].indexOf(column.data_type) != -1"
                        label="Less Than"
                        value="less-than"
                    ></el-option>

                    <el-option
                        v-if="['number', 'date'].indexOf(column.data_type) != -1"
                        label="Greater Than"
                        value="greater-than"
                    ></el-option>

                    <el-option
                        v-if="['number', 'date'].indexOf(column.data_type) != -1"
                        label="Less Than Or Equal To"
                        value="less-than-or-equal-to"
                    ></el-option>

                    <el-option
                        v-if="['number', 'date'].indexOf(column.data_type) != -1"
                        label="Greater Than Or Equal To"
                        value="greater-than-or-equal-to"
                    ></el-option>

                    <el-option
                        v-if="['number', 'date'].indexOf(column.data_type) != -1"
                        label="Between (Min & Max Values)"
                        value="between"
                    ></el-option>

                </el-select>
            </el-col>

            <el-col :sm="5" :md="5">
                <el-input
                    size="small"
                    :placeholder="condition.conditionalOperator == 'between' ? 'Min value' : 'Enter Value'"
                    v-model="condition.conditionalValue"
                    :disabled="!hasPro"
                ></el-input>
            </el-col>

            <el-col :sm="4" :md="4" v-if="condition.conditionalOperator == 'between'">
                <el-input
                    size="small"
                    placeholder="Max Value"
                    v-model="condition.conditionalValue2"
                ></el-input>
            </el-col>

            <el-col :sm="1" :md="1">
                <div class="if-cell-value text-center">Then</div>
            </el-col>

            <el-col :sm="5" :md="5">
                <el-select v-model="condition.targetAction"
                           :disabled="!hasPro"
                           filterable
                           size="small"
                           style="width: 100%">
                    <el-option-group key="cell_options" label="Cell Options">
                        <el-option label="Set cell color" value="set-cell-color"></el-option>
                        <el-option label="Set cell background color" value="set-cell-bg-color"></el-option>
                        <el-option label="Set cell content" value="set-cell-content"></el-option>
                        <el-option label="Set cell CSS class" value="set-cell-css-class"></el-option>
                        <el-option label="Reset cell color to default" value="reset-cell-color-to-default"></el-option>
                        <el-option label="Reset cell background color to default"
                                   value="reset-cell-bg-color-to-default"></el-option>
                        <el-option label="Remove cell CSS class" value="remove-cell-css-class"></el-option>
                    </el-option-group>
                    <el-option-group key="row_options" label="Row Options">
                        <el-option label="Set row color" value="set-row-color"></el-option>
                        <el-option label="Set row background color" value="set-row-bg-color"></el-option>
                        <el-option label="Set row CSS class" value="set-row-css-class"></el-option>
                        <el-option label="Reset row color to default" value="reset-row-color-to-default"></el-option>
                        <el-option label="Reset row background color to default" value="reset-row-bg-color"></el-option>
                        <el-option label="Remove row CSS class" value="remove-row-css-class"></el-option>
                    </el-option-group>
                    <el-option-group key="column_options" label="Column Options">
                        <el-option label="Set column color" value="set-column-color"></el-option>
                        <el-option label="Set column background color" value="set-column-bg-color"></el-option>
                        <el-option label="Add column CSS class" value="add-column-css-class"></el-option>
                        <el-option label="Remove column CSS class" value="remove-column-css-class"></el-option>
                    </el-option-group>
                </el-select>
            </el-col>

            <el-col :sm="4" :md="4">
                <el-input
                    size="small"
                    placeholder="Enter Value"
                    v-model="condition.targetValue"
                    v-show="!shouldShowColorPicker(condition)"
                    :disabled="!hasPro"
                ></el-input>

                <div class="conditional_color_block" v-show="shouldShowColorPicker(condition)">
                    <ninja-color-picker
                        size="small"
                        v-model="condition.targetValueColor"
                        :disabled="!hasPro"
                    ></ninja-color-picker>
                </div>
            </el-col>

            <el-col :sm="1" :md="1">
                <el-button
                    size="small"
                    type="danger"
                    icon="el-icon-minus"
                    @click="removeCondition(index)"
                    :disabled="!hasPro"
                ></el-button>
            </el-col>
        </el-row>

        <div class="nt_suggest" v-if="column.data_type == 'date'">
            <h4>You can use placeholder for dynamic dates to style your row/cell/column</h4>
            <p>Example Usage</p>
            <ul>
                <li>Today's Date: <code>{date:{{column.dateFormat}}}</code></li>
                <li>Tomorrow's Date: <code>{date+1:{{column.dateFormat}}}</code></li>
                <li>Yesterday's Date: <code>{date-1:{{column.dateFormat}}}</code></li>
                <li>Date After 10 Days: <code>{date+10:{{column.dateFormat}}}</code></li>
                <li>Date Before 10 Days: <code>{date-10:{{column.dateFormat}}}</code></li>
            </ul>
        </div>

        <el-row v-if="!column.conditions || !column.conditions.length">
            <el-alert
                center
                :closable=false
                title="You haven't added any conditions for the column yet!"
                type="info"
            ></el-alert>
        </el-row>
    </div>
</template>

<script>
    import NinjaColorPicker from '../../Extras/ColorPicker';
    import GetPro from "../../Tools/GetPro";

    export default {
        name: "Conditional",
        props: {
            hasPro: {
                type: Boolean,
                default: false
            },
            column: {
                type: Object,
                default: () => ({})
            }
        },
        components: {
          GetPro,
            NinjaColorPicker,
        },
        data() {
            return {
                defaultCondition: {
                    conditionalOperator: null,
                    conditionalValue: null,
                    conditionalValue2: null,
                    targetAction: null,
                    targetValue: null,
                    targetValueColor: null,
                },
            };
        },
        methods: {
            addCondition() {
                if (!this.column.conditions) {
                    this.$set(this.column, 'conditions', []);
                }
                this.column.conditions.push({...this.defaultCondition});
            },
            removeCondition(index) {
                this.column.conditions.splice(index, 1);
            },
            shouldShowColorPicker(condition) {
                return [
                    'set-cell-color',
                    'set-cell-bg-color',
                    'set-row-color',
                    'set-row-bg-color',
                    'set-column-color',
                    'set-column-bg-color',
                ].indexOf(condition.targetAction) != -1;
            },
        },
        mounted() {
            if (this.column && !this.column.conditions) {
                this.$set(this.column, 'conditions', []);
            }
        }
    }
</script>

<style lang="scss">
    .column-condition-config {
        .el-row {
            display: flex;
            margin-bottom: 5px;
        }
        .el-col {
            margin: 0 5px;
            display: flex;
            .conditional_color_block {
                width: 100%;
                .el-color-picker__trigger {
                    width: 100%;
                    height: 33px;
                }
            }
        }
        .el-col:first-child > .if-cell-value {
            white-space: nowrap;
            /*text-transform: uppercase;*/
        }
        .if-cell-value {
            margin-top: 10px;
            font-weight: 400;
        }
        .form_group {
            margin: 0;
            height: 35px;
        }
        .el-color-picker,
        .el-color-picker__mask {
            width: 100% !important;
        }

        .el-button--mini {
            padding: 5px 13px;
        }

        .conditional-settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    }
</style>
