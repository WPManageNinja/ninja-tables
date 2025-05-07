<template>
    <div class="block nt-checkbox-group">
        <span>{{ label }}</span>
        <el-checkbox-group
            size="small"
            :model-value="modelValue"
            @update:model-value="$emit('update:modelValue', $event)"
            >

            <el-checkbox
                v-for="option in options"
                :key="option.value"
                :label="option.value"
                :value="option.value"
            />

        </el-checkbox-group>
    </div>
</template>
<script>
export default {
    name: "CheckboxInput",
    emits: ['update:modelValue'],
    props: {
        modelValue: {
            type: Array,
            default: () => []
        },
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

<style lang="scss">
.nt-checkbox-group {
    .el-checkbox-group {
        display: grid;
    }
}

</style>
