<template>
    <div class="wp-post-conditions">
        <el-row>
            <el-col :md="21">
                <el-alert
                    type="info"
                    title=""
                    :closable="false"
                    description="You can add additional meta query here."
                    show-icon>
                </el-alert>
            </el-col>

            <el-col :md="3" style="text-align:right;">
                <el-button type="primary" @click="addCondition($event)">
                    <el-icon><Plus /></el-icon>
                </el-button>
            </el-col>
        </el-row>

        <template v-for="(condition, i) in metas">
            <el-row :gutter="20">
                <el-col :md="7" :sm="7">
                    <el-input
                        placeholder="Meta Key"
                        v-model="condition.field"
                        @change="setOperators($event, condition)"
                    ></el-input>
                </el-col>

                <el-col :md="7" :sm="7">
                    <el-select v-model="condition.operator" placeholder="Select">
                        <el-option
                            v-for="operator in condition.operators"
                            :key="operator.key"
                            :label="operator.value"
                            :value="operator.key">
                        </el-option>
                    </el-select>
                </el-col>

                <el-col :md="7" :sm="7">
                    <el-input
                        placeholder="Value"
                        v-model="condition.value"
                    ></el-input>

                    <template v-if="shouldShowInfo(condition.operator)">
                        <el-alert
                            title="enter comma separated value"
                            type="info"
                            :closable="false"
                            class="compact"
                        />
                    </template>
                </el-col>

                <el-col :md="3" :sm="3" style="text-align:right;">
                    <el-button type="danger" @click="removeCondition(i, $event)">
                        <el-icon><Delete /></el-icon>
                    </el-button>
                </el-col>
            </el-row>
        </template>
    </div>
</template>

<script>
import {Delete, Plus} from "@element-plus/icons-vue";
    export default {
        name: 'WPPostMetaQuery',
        props: ['metas'],
        components: {
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

<style lang="scss">
    .el-row {
        margin-bottom: 20px;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .wp-post-conditions-el-picker {
        z-index: 9999 !important;
    }

    .compact {
        padding: 0 !important;
        margin-top: 3px !important;
    }
</style>
