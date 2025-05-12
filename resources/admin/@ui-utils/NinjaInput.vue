<template>
    <el-input
        :placeholder="placeholder"
        :model-value="modelValue"
        :disabled="disabled"
        :size="size"
        @update:model-value="handleInput"
        class="ninja-input"
    >
        <!-- Prefix Slot -->
        <template #prefix>
            <slot name="prefix">
                <img v-if="prefixIcon" :src="assetUrl(prefixIcon)" alt="Prefix Icon" class="prefix-icon" />
            </slot>
        </template>

        <!-- Suffix Slot -->
        <template #suffix>
            <slot name="suffix">
                <img v-if="suffixIcon" :src="assetUrl(suffixIcon)" alt="Suffix Icon" class="suffix-icon" />
            </slot>
        </template>
    </el-input>
</template>

<script>
import { assetUrl } from "../utils/ninjatablesadmin";

export default {
    name: "NinjaInput",
    props: {
        modelValue: {
            type: String,
            default: "",
        },
        placeholder: {
            type: String,
            default: "Enter text",
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        size: {
            type: String,
            default: "default", // Options: 'small', 'default', 'large'
        },
        prefixIcon: {
            type: String,
            default: null,
        },
        suffixIcon: {
            type: String,
            default: null,
        },
    },
    methods: {
        assetUrl,
        handleInput(value) {
            this.$emit("update:modelValue", value); // Emit the updated value to the parent
        },
    },
};
</script>

<style scoped>
.prefix-icon,
.suffix-icon {
    width: 16px;
    height: 16px;
}
</style>
