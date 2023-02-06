<template>
  <div class="block space-between">
    <span>{{ label }}</span>
    <el-switch :value="mainValue"
               @input="$emit('update', $event)"
               :disabled="disableResponsive || enableResponsiveNClickDesktop || mobileDisableBreakpoint || tabletDisableBreakpoint"
                style="margin-right: 10px; margin-top:1px">
    ></el-switch>
  </div>
</template>
<script>
export default {
  name: "SwitchInput",
  model: {
    prop: "value",
    event: "update"
  },
  props: {
    value: {
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
  computed: {
    mainValue() {
     return (this.value === 'true' || this.value === true)
    },
  }
};
</script>
