<template>
    <div class="block">
        <span>{{ label }}</span><br>
        <el-select
            class="ninja-select w-full"
            :model-value="mainValue"
            @update:model-value="updateValue"
            placeholder="Select"
            :disabled="disableResponsive || mobileDisableBreakpoint || tabletDisableBreakpoint">
            <el-option
                v-for="item in items"
                :key="item.value"
                :label="item.label"
                :value="item.value">
            </el-option>
        </el-select>
    </div>
</template>

<script>
export default {
    name: "SelectInput",
    props: {
        modelValue: {
            type: String,
            default: ""
        },
        label: {
            type: String,
            default: ""
        },
        items: {
            type: Array,
            default: () => []
        },
        disableResponsive: {
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
        }
    },
    emits: ['update:modelValue'],
    computed: {
        mainValue() {
            return this.modelValue;
        }
    },
    methods: {
        updateValue(newVal) {
            this.$emit('update:modelValue', newVal);
        }
    }
}
</script>
