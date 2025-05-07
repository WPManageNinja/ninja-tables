<template>
    <div class="form_group">
        <el-color-picker
            show-alpha
            v-model="colorValue"
            :disabled="disabled"
        ></el-color-picker>
        <label v-if="label">{{ label }}</label>
    </div>
</template>

<script>
import { defineComponent, ref, watch } from 'vue'

export default defineComponent({
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
    setup(props, { emit }) {
        const colorValue = ref(props.modelValue)

        watch(() => props.modelValue, (newValue) => {
            colorValue.value = newValue
        })

        watch(colorValue, (newValue) => {
            emit('update:modelValue', newValue)
        })

        return {
            colorValue
        }
    }
})
</script>

