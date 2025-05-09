<template>
    <button
        :icon="icon"
        :disabled="disabled"
        :loading="loading"
        @click="handleClick"
        class="ninja-button"
        :class="{
            'ninja-button--primary': type === 'primary',
            'ninja-button--secondary': type === 'secondary',
            'ninja-button--danger': type === 'danger',
            'ninja-button--info': type === 'info',
        }"
    >
        <slot />
    </button>
</template>

<script>
export default {
    name: "NinjaButton",
    props: {
        type: {
            type: String,
            default: "primary",
        },
        size: {
            type: String,
            default: "medium",
        },
        icon: {
            type: String,
            default: null,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        handleClick(event) {
            this.$emit("click", event);
        },
    },
};
</script>