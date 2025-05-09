<template>
    <div v-if="appReady" style="max-width: 90%" class="maybe_nt_multi_select">
        <template v-if="column.isMultiple == 'yes'">
            <el-select
                style="width: 100%"
                v-model="newColumn[column.key]"
                :filterable
                multiple
                allow-create
                :size="size"
                @remove-tag="$emit('blur')"
                @change="$emit('blur')"
                default-first-option
                :placeholder="column.placeholder || 'Select'">
                <el-option
                    v-for="item in getFromSelectionStr(column.selections)"
                    :key="item"
                    :label="item"
                    :value="item">
                </el-option>
            </el-select>
        </template>
        <template v-else>
            <el-select
                :size="size"
                style="width: 100%"
                v-model="newColumn[column.key]"
                filterable
                @change="$emit('blur')"
                allow-create
                default-first-option
                :placeholder="column.placeholder || 'Select'">
                <el-option
                    v-for="item in getFromSelectionStr(column.selections)"
                    :key="item"
                    :label="item"
                    :value="item">
                </el-option>
            </el-select>
        </template>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'maybe_multi_select',
        props: ['newColumn', 'column', 'size'],
        data() {
            return {
                appReady: false
            }
        },
        methods: {
            getFromSelectionStr(str) {
                if (str) {
                    return str.split(/\r?\n/);
                }
                return [];
            },
            maybeBlur(status) {
                console.log(status);
                if(!status) {
                    this.$emit('blur');
                }
            }
        },
        mounted() {
            if (this.column.isMultiple == 'yes') {
                let oldValue = this.newColumn[this.column.key];
                if (!oldValue) {
                    this.newColumn[this.column.key] = [];
                } else if (typeof oldValue == 'string') {
                    this.newColumn[this.column.key] = [oldValue];
                }
            }
            this.appReady = true;
        }
    }
</script>
