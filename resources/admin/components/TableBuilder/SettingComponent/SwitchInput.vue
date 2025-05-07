<template>
    <div class="block space-between">
        <span>{{ label }}</span>
        <el-switch
            :model-value="mainValue"
            @update:model-value="updateValue"
            :disabled="disableResponsive || enableResponsiveNClickDesktop || mobileDisableBreakpoint || tabletDisableBreakpoint"
            style="margin-right: 10px; margin-top:1px">
        </el-switch>
    </div>
</template>

<script>
export default {
    name: "SwitchInput",
    props: {
        modelValue: {
            type: [Boolean, String],
            default: false
        },
        label: {
            type: String,
            default: ""
        },
        disableResponsive: {
            type: Boolean,
            default: false
        },
        enableResponsiveNClickDesktop: {
            type: Boolean,
            default: false
        },
        mobileDisableBreakpoint: {
            type: Boolean,
            default: false
        },
        tabletDisableBreakpoint: {
            type: Boolean,
            default: false
        },
    },
    emits: ['update:modelValue'],
    computed: {
        mainValue() {
            return (this.modelValue === 'true' || this.modelValue === true);
        }
    },
    methods: {
        updateValue(newValue) {
            this.$emit('update:modelValue', newValue);
        }
    }
};
</script>
