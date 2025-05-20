<template>
    <div class="ninja-input-wrapper">
        <div v-if="$slots.prefix || prefixIcon" class="ninja-input-prefix">
            <slot name="prefix">
                <img v-if="prefixIcon" :src="assetUrl(prefixIcon)" alt="Prefix Icon" class="prefix-icon" />
            </slot>
        </div>
        <input
            :placeholder="placeholder"
            :value="modelValue"
            :disabled="disabled"
            :class="['ninja-input', sizeClass]"
            @input="handleInput"
            @change="handleChange"
            v-bind="$attrs"
        />
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
            default: "default",
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

<style lang="scss" scoped>
  .ninja-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    border: 1px solid #dcdfe6;
    border-radius: 8px;
    transition: border-color 0.2s;
    background-color: #fff;
    &:hover {
      border-color: #c0c4cc;
    }
    &:focus-within {
      border-color: #335cff;
    }

    .ninja-input {
      flex: 1;
      width: 100%;
      border: none;
      outline: none;
      padding: 0 12px;
      height: 32px;
      font-size: 14px;
      color: #606266;
      background: transparent;
    }

    .ninja-input--large {
      height: 36px;
      font-size: 15px;
    }

    .ninja-input-prefix,
    .ninja-input-suffix {
      display: flex;
      align-items: center;
      padding: 0 12px;
      color: #909399;
    }

    .ninja-input-prefix {
      border-right: 1px solid #dcdfe6;
    }

    .ninja-input-suffix {
      border-left: 1px solid #dcdfe6;
    }
    .prefix-icon,
    .suffix-icon {
      width: 16px;
      height: 16px;
    }

    .ninja-input:disabled,
    .ninja-input-wrapper.disabled {
      background-color: #f5f7fa;
      cursor: not-allowed;
    }
  }
</style>