<template>
    <div class="form_group">
        <el-color-picker
            show-alpha
            :model-value="modelValue"
            @update:model-value="updateValue"
            :disabled="disabled"
        ></el-color-picker>
        <label v-if="label">{{ label }}</label>
    </div>
</template>

<script>
export default {
    name: 'ninja-color-picker',
    props: {
        label: {
            type: String,
            default: null
        },
        modelValue: {
            type: String,
            default: null
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    emits: ['update:modelValue'],
    methods: {
        updateValue(newValue) {
            this.$emit('update:modelValue', newValue);
        }
    }
}
</script>
