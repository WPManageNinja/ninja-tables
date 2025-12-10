<template>
    <div class="radioButton">
        <span>{{ label }}</span><br>
        <el-radio-group
            :model-value="modelValue"
            size="small"
            @update:model-value="$emit('update:modelValue', $event)"
        >
            <el-radio-button
                v-for="option in options"
                :key="option.value"
                :label="option.label"
                :value="option.label"
            />
        </el-radio-group>
    </div>
</template>

<script>
export default {
    name: "RadioButton",

    props: {
        value: {
            type: String,
            default: 'M'
        },
        modelValue: {
            type: String,
            default: 'M'
        },
        options: {
            type: Array,
            default: () => []
        },
        event: {
            type: String,
            default: 'update'
        },
        label: {
            type: String,
            default: ""
        }
    }
}
</script>

<style scoped>
.radioButton {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-right: 10px;
}
</style>
