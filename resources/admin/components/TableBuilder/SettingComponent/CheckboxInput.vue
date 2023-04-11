<template>
    <div class="block">
        <span>{{ label }}</span>
        <el-checkbox-group
            size="mini"
            :value="value"
            @input="$emit('update', $event)">
            <el-checkbox v-for="option in options" :key="option.value" :label="option.value">{{ option.value }}</el-checkbox>
        </el-checkbox-group>
    </div>
</template>
<script>
export default {
    name: "CheckboxInput",
    
    model: {
        prop: "value",
        event: "update"
    },
    props: {
        value: {
            type: Array,
            default: () => []
        },
        label: {
            type: String,
            default: ""
        },
        options: {
            type: Array,
            default: () => []
        }
    }
};
</script>

<style>
.el-checkbox-group {
    display: grid;
}
</style>
