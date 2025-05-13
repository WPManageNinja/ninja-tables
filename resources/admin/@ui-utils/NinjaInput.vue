<template>
    <div class="ninja-input-wrapper">
        <!-- Prefix -->
        <div v-if="$slots.prefix || prefixIcon" class="ninja-input-prefix">
            <slot name="prefix">
                <img v-if="prefixIcon" :src="assetUrl(prefixIcon)" alt="Prefix Icon" class="prefix-icon" />
            </slot>
        </div>

        <!-- Input Element -->
        <input
            :placeholder="placeholder"
            :value="modelValue"
            :disabled="disabled"
            :class="['ninja-input', sizeClass]"
            @input="handleInput"
            @change="handleChange"
            v-bind="$attrs"
        />

        <!-- Suffix -->
        <div v-if="$slots.suffix || suffixIcon" class="ninja-input-suffix">
            <slot name="suffix">
                <img v-if="suffixIcon" :src="assetUrl(suffixIcon)" alt="Suffix Icon" class="suffix-icon" />
            </slot>
        </div>
    </div>
</template>

<script>
import { assetUrl } from "../utils/ninjatablesadmin";

export default {
    name: "NinjaInput",
    inheritAttrs: false,
    props: {
        modelValue: {
            type: [String, Number],
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
    computed: {
        sizeClass() {
            return {
                'ninja-input--small': this.size === 'small',
                'ninja-input--large': this.size === 'large',
            };
        }
    },
    methods: {
        assetUrl,
        handleInput(event) {
            const value = event.target.value;
            this.$emit("update:modelValue", value);
        },
        handleChange(event) {
            const value = event.target.value;
            this.$emit("change", value);
        },
    },
    emits: ['update:modelValue'],
};
</script>
