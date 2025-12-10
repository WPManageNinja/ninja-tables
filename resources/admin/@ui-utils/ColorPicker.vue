<template>
    <div class="flex items-center gap-3 rounded-[8px]">
        <p v-if="labelShow" class="text-base text-black dark:text-neutral-100">{{ label }}</p>
        <el-color-picker
            class="ml-2 ninja-data-tables_color_picker"
            popper-class="ninja-data-tables_color_picker"
            :model-value="modelValue"
            :predefine="predefineColors"
            @change="activeColorChange"
            @active-change="activeColorChange"
            :disabled="disabled"
            show-alpha
            color-format="hex"
        />
            <span class="font-[500]" v-if="showValue">{{ modelValue }}</span>
    </div>
</template>

<script>
export default {
    name: 'ColorPicker',
    props: {
        label: String,
        modelValue: String,
        labelShow: {
            type: Boolean,
            default: true
        },
        showValue: {
            type: Boolean,
            default: true
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            predefineColors: [
                '#ff4500',
                '#ff8c00',
                '#ffd700',
                '#90ee90',
                '#00ced1',
                '#1e90ff',
                '#7A2CEE',
                '#FFFFFF',
                '#000000',
            ]
        };
    },
    methods: {
        activeColorChange(color) {
            this.$emit('update:modelValue', color);
        }
    }
}
</script>
