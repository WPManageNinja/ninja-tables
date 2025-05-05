<template>
  <div class="block">

    <span>{{ label }}</span><br>
    <el-radio-group :value="value"
                    @input="$emit('update', $event)"
                    :disabled="disableResponsive || mobileDisableBreakpoint || tabletDisableBreakpoint"
                    style="display: flex; justify-content: space-around; margin-right: 10px;"
    >
      <el-radio :label="$t('Left')" value="left" />
      <el-radio :label="$t('Center')" value="center" />
      <el-radio :label="$t('Right')" valueright />
    </el-radio-group>
  </div>
</template>
<script>
export default {
  name: "Alignment",
  model: {
    prop: "value",
    event: "update"
  },
  props: {
    value: {
      type: String,
      default: 'left'
    },
    label: {
      type: String,
      default: ""
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
  }
};
</script>
