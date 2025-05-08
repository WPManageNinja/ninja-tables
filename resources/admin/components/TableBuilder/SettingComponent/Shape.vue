<template>
  <div class="block">
    <span>{{ label }}</span><br>
    <el-radio-group :value="value" @input="$emit('update', $event)" style="margin-top: 5px;">
      <el-radio :label="$t('Square')" value="square" />
      <el-radio :label="$t('Round')" value="round" />
    </el-radio-group>
  </div>
</template>
<script>
export default {
  name: "Shape",
  model: {
    prop: "value",
    event: "update"
  },
  props: {
    value: {
      type: String,
      default: 'square'
    },
    label: {
      type: String,
      default: ""
    }
  }
};
</script>
