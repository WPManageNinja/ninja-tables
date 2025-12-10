<template>
    <div class="block">
        <span class="float-left">{{ label }}</span>
        <el-input
            class="mb-[2px]"
            v-model="value"
            @update:modelValue="handleInput"
        ></el-input>
    </div>
</template>

<script>
export default {
    name: "TextInput",
    props: {
        modelValue: {
            type: String,
            default: "https://google.com"
        },
        label: {
            type: String,
            default: ""
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            value: this.modelValue
        };
    },
    watch: {
        modelValue(newVal) {
            this.value = newVal;
        }
    },
    methods: {
        handleInput(newValue) {
            this.$emit('update:modelValue', newValue);
        }
    }
};
</script>
