<template>
    <button
        :disabled="disabled"
        :loading="loading"
        @click="handleClick"
        class="ninja-button flex items-center"
        :class="{
            'ninja-button--primary': type === 'primary',
            'ninja-button--secondary': type === 'secondary',
            'ninja-button--danger': type === 'danger',
            'ninja-button--info': type === 'info',
        }"
    >
        <img v-if="icon" :src="icon" class="mr-1" />
        {{ btnText }}
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
            default: "default",
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
        btnText: {
            type: String,
            default: 'Submit',
        }
    },
    methods: {
        handleClick(event) {
            this.$emit("click", event);
        },
    },
};
</script>
