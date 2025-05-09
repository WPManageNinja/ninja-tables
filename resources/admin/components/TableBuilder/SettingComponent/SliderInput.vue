<template>
    <div class="block">
        <span>{{ label }}</span>
        <el-tooltip placement="top-start" v-if="label === 'Table margin top'" effect="light">
            <template #content>
                <p>Use margin top only for<br>horizontal ribbon positioning.</p>
            </template>
            <el-icon class="tooltip-icon-color">
                <InfoFilled/>
            </el-icon>
        </el-tooltip>
        <el-slider
            :model-value="mainValue"
            :max="maxValue"
            :min="minValue"
            :step="stepValue"
            @update:model-value="updateValue"
            show-input
            input-size="small"
            :disabled="disableResponsive || enableResponsiveNClickDesktop || mobileDisableBreakpoint || tabletDisableBreakpoint || disableSlider"
            style="margin-right: 10px">
        </el-slider>
    </div>
</template>

<script>

export default {
    name: "SliderInput",
    props: {
        modelValue: {
            type: [Number, String],
            default: 12
        },
        max: {
            type: [Number, String],
            default: 50
        },
        min: {
            type: [Number, String],
            default: 0
        },
        step: {
            type: [Number, String],
            default: 1
        },
        label: {
            type: String,
            default: ""
        },
        disableResponsive: {
            type: Boolean,
            default: false
        },
        disableSlider: {
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
            return Number(this.modelValue);
        },
        maxValue() {
            return Number(this.max);
        },
        minValue() {
            return Number(this.min);
        },
        stepValue() {
            return Number(this.step);
        }
    },
    methods: {
        updateValue(newValue) {
            this.$emit('update:modelValue', newValue);
        }
    }
};
</script>

<style>
.ninja-tables-component .el-slider {
    margin-left: 0 !important;
}
</style>
