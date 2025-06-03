<template>
  <div class="block">
    <span style="float:left;">{{label}}</span>
    <el-input size="small" style="width: 96%;margin-bottom: 2px;" :value="value" @input="$emit('update', $event)"></el-input>
  </div>
</template>
<script>
export default {
  name: "TextInput",

  model: {
    prop: "value",
    event: "update"
  },
  props: {
    value: {
      type: String,
      default: "https://google.com"
    },
    label: {
      type: String,
      default: ""
    }
  }
};
</script>
