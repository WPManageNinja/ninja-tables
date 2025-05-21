<template>
    <div class="column-condition-config">
        <div v-if="hasPro" class="conditional-settings-header">
            <div class="conditional-settings-title">
                Customize your table's appearances based on the cell value. Add as many conditions as you like.
                <a class="text-[#335CFF]" target="_blank" href="https://ninjatables.com/docs/conditional-formatting/">
                    View Documentation
                </a>
            </div>

            <div
                v-if="column.conditions && column.conditions.length < 1 "
                @click="addCondition"
                class="flex items-center text-[#335CFF] cursor-pointer font-[300] my-2"
            >
                <img :src="assetUrl('icons/add-line.svg')" alt="add" /> {{ $t('Add Condition') }}
            </div>
        </div>

        <div v-else>
            <div class="nt-modal-body">
                <h3 class="nt-modal-subtitle">Conditional Formatting</h3>
                <p class="nt-modal-description">
                    Customize your table's appearances based on the cell value. Add as many conditions as you like.
                    Conditional Formatting is a pro feature which can be enabled by using Ninja Table pro Add-on. Ninja
                    Table Pro has lots of features that will help you to build any type of Tables.
                </p>
               <get-pro size="small"/>
            </div>
        </div>

        <el-row v-for="(condition, index) in column.conditions" :key="index" class="mt-4">
            <el-col :sm="2" :md="2">
                <div class="flex items-center justify-start mt-2">If Cell Value</div>
            </el-col>

            <el-col :sm="4" :md="4" class="mr-2">
                <el-select
                    v-model="condition.conditionalOperator"
                    :disabled="!hasPro"
                    size="small"
                    class="ninja-select"
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

            <el-col :sm="4" :md="4" class="mr-2">
                <NinjaInput
                    size="large"
                    :disabled="!hasPro"
                    v-model="condition.conditionalValue"
                    :placeholder="condition.conditionalOperator == 'between' ? 'Min value' : 'Enter Value'"
                />
            </el-col>

            <el-col :sm="4" :md="4" v-if="condition.conditionalOperator == 'between'" class="mr-2">
                <NinjaInput
                    placeholder="Max Value"
                    v-model="condition.conditionalValue2"
                />
            </el-col>

            <el-col :sm="1" :md="1">
                <div class="flex items-center justify-center md:mt-2 mr-2">Then</div>
            </el-col>

            <el-col :sm="4" :md="4" class="mr-2">
                <el-select
                    class="ninja-select"
                    v-model="condition.targetAction"
                    :disabled="!hasPro"
                >
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

            <el-col :sm="4" :md="4" class="mr-2">
                <NinjaInput
                    size="large"
                    placeholder="Enter Value"
                    v-model="condition.targetValue"
                    v-show="!shouldShowColorPicker(condition)"
                    :disabled="!hasPro"
                />

                <div class="conditional_color_block" v-show="shouldShowColorPicker(condition)">
                    <ninja-color-picker
                        size="small"
                        v-model="condition.targetValueColor"
                        :disabled="!hasPro"
                    ></ninja-color-picker>
                </div>
            </el-col>

            <el-col :sm="2" :md="2">
                <div class="flex justify-center items-center gap-2 w-full ml-3 mt-[2px]">
                    <div  @click="addCondition" class="min-w-[35px] h-[35px] cursor-pointer flex items-center justify-center px-2 py-[7px] bg-white border-[#D6DAE1] border-solid border rounded-[8px]">
                        <img :src="assetUrl('icons/add-01.svg')" alt="add" />
                    </div>
                    <div @click="removeCondition(index)" class="min-w-[35px] h-[35px] cursor-pointer flex items-center justify-center px-2 py-[7px] bg-white border-[#D6DAE1] border-solid border rounded-[8px]">
                        <img :src="assetUrl('icons/delete-02.svg')" alt="delete" />
                    </div>
                </div>
            </el-col>
        </el-row>

        <div class="bg-[#F9FAFB] p-4 rounded-[8px]" v-if="column.data_type === 'date'">
            <h4 class="my-2">You can use placeholder for dynamic dates to style your row/cell/column</h4>
            <p class="nt-form-modal mb-2">Example Usage</p>
            <ul>
                <li>Today's Date: <code class="nt-code">{date:{{column.dateFormat}}}</code></li>
                <li>Tomorrow's Date: <code class="nt-code">{date+1:{{column.dateFormat}}}</code></li>
                <li>Yesterday's Date: <code class="nt-code">{date-1:{{column.dateFormat}}}</code></li>
                <li>Date After 10 Days: <code class="nt-code">{date+10:{{column.dateFormat}}}</code></li>
                <li>Date Before 10 Days: <code class="nt-code">{date-10:{{column.dateFormat}}}</code></li>
            </ul>
        </div>

        <el-row v-if="!column.conditions || !column.conditions.length">
            <div class="nt-instruction w-full text-center">
                <p>{{ $t(`You haven't added any conditions for the column yet!`) }}</p>
            </div>
        </el-row>
    </div>
</template>

<script>
    import NinjaColorPicker from '../../Extras/ColorPicker';
    import GetPro from "../../Tools/GetPro";
    import {Minus} from "@element-plus/icons-vue";
    import {assetUrl} from "../../../utils/ninjatablesadmin";
    import NinjaInput from "../../../@ui-utils/NinjaInput.vue";

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
            NinjaInput,
          GetPro,
            NinjaColorPicker,
            Minus
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
            assetUrl,
            addCondition() {
                if (!this.column.conditions) {
                    this.column.conditions = [];
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
                this.column.conditions = [];
            }
        }
    }
</script>
