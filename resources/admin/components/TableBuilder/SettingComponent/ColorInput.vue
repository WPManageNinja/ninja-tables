<template>
    <div class="block space-between">
        <span>{{ label }}</span>
        <el-color-picker
            size="small"
            :model-value="modelValue"
            @update:model-value="updateColor"
            @change="colorUpdate"
            @active-change="handleActiveChange"
            :predefine="predefineColors"
            style="margin-right: 10px"
        ></el-color-picker>
    </div>
</template>

<script>
export default {
    name: "ColorInput",
    data() {
        return {
            predefineColors: [
                "#ff4500",
                "#ff8c00",
                "#ffd700",
                "#90ee90",
                "#00ced1",
                "#1e90ff",
                "#c71585",
                "#000000",
                "#dddddd",
                "#379D13"
            ]
        };
    },
    props: {
        modelValue: {
            type: String,
            default: "#ffffff"
        },
        label: {
            type: String,
            default: ""
        }
    },
    emits: ['update:modelValue'],
    methods: {
        updateColor(color) {
            this.$emit('update:modelValue', color);
        },
        colorUpdate(color) {
            let cl = color;
            if (color === null) {
                cl = '';
            }
            this.$emit('update:modelValue', cl);
        },
        handleActiveChange(color) {
            this.$emit('active-change', color);
        }
    }
};
</script>

<style>
.space-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
