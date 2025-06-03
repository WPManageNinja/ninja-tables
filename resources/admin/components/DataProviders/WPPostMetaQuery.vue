<template>
    <div class="wp-post-conditions mt-5">
        <div class="conditional-settings-header">
            <div class="conditional-settings-title">
                {{$t('You can add additional meta query here.')}}
            </div>

            <div
                v-if="metas && metas.length < 1 "
                @click="addCondition($event)"
                class="flex items-center text-[#335CFF] cursor-pointer font-[300] mt-1"
            >
                <img :src="assetUrl('icons/add-line.svg')" alt="add"/> {{ $t('Add Meta Query') }}
            </div>
        </div>


        <div v-for="(condition, i) in metas" class="mt-2">
            <el-row :gutter="20">
                <el-col :md="7" :sm="7">
                     <NinjaInput
                         :placeholder="$t('Meta Key')"
                         v-model="condition.field"
                         @change="setOperators($event, condition)"
                     />
                </el-col>

                <el-col :md="7" :sm="7">
                    <el-select
                        size="small"
                        class="ninja-select"
                        v-model="condition.operator"
                        :placeholder="$t('Select')"
                    >
                        <el-option
                            v-for="operator in condition.operators"
                            :key="operator.key"
                            :label="operator.value"
                            :value="operator.key">
                        </el-option>
                    </el-select>
                </el-col>

                <el-col :md="7" :sm="7">
                    <NinjaInput
                        :placeholder="$t('Value')"
                        v-model="condition.value"
                    />

                    <template v-if="shouldShowInfo(condition.operator)">
                        <el-alert
                            title="enter comma separated value"
                            type="info"
                            :closable="false"
                        />
                    </template>
                </el-col>

                <el-col :md="3" :sm="3" style="text-align:right;">
                    <div class="flex justify-center items-center gap-2 w-full">
                        <div  @click="addCondition($event)" class="min-w-[35px] h-[35px] cursor-pointer flex items-center justify-center px-2 py-[7px] bg-white border-[#D6DAE1] border-solid border rounded-[8px]">
                            <img :src="assetUrl('icons/add-01.svg')" alt="add" />
                        </div>
                        <div @click="removeCondition(i, $event)" class="min-w-[35px] h-[35px] cursor-pointer flex items-center justify-center px-2 py-[7px] bg-white border-[#D6DAE1] border-solid border rounded-[8px]">
                            <img :src="assetUrl('icons/delete-02.svg')" alt="delete" />
                        </div>
                    </div>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<script>
import {Delete, Plus} from "@element-plus/icons-vue";
import NinjaInput from "../../@ui-utils/NinjaInput.vue";
import {assetUrl} from "../../utils/ninjatablesadmin";
    export default {
        name: 'WPPostMetaQuery',
        props: ['metas'],
        components: {
            NinjaInput,
            Delete,
            Plus
        },
        data() {
            return {
                default_condition: {
                    field: null,
                    operator: null,
                    value: null,
                    operators: [],
                    selectableOptions: [],
                    is_selectable: 'false',
                },
                operators: [
                    { key: '=', value: 'Equal To' },
                    { key: '!=', value: 'Not Equal To' },
                    { key: 'IN', value: 'In' },
                    { key: 'NOT IN', value: 'Not In' },
                    { key: '>', value: 'Greater Than' },
                    { key: '>=', value: 'Greater Than Or Equal To' },
                    { key: '<', value: 'Less Than' },
                    { key: '<=', value: 'Less Than Or Equal To' }
                ]
            };
        },
        methods: {
            assetUrl,
            addCondition(event) {
                this.metas.push({ ...this.default_condition });
            },
            removeCondition(i, $event) {
                this.metas.splice(i, 1);
            },
            setOperators(column, condition) {
                condition.operators = [...this.operators];

                this.setValueField(column, condition);
            },
            setValueField(column, condition) {
                condition.value = null;
                condition.is_selectable = 'false';
                condition.selectableOptions = [];
            },
            shouldShowInfo(operator) {
                return ['IN', 'NOT IN'].indexOf(operator) !== -1;
            }
        }
    };
</script>
