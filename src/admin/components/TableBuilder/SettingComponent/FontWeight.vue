<template>
  <div class="block">
    <span>{{ label }}</span>
    <el-checkbox-group
        :value="value"
        @input="$emit('update', $event)"
        style="display: flex; justify-content: space-around; margin-right: 10px;">
            <el-checkbox label="bold">{{ $t('Bold') }}</el-checkbox>
            <el-checkbox label="italic">{{ $t('Italic') }}</el-checkbox>
            <el-checkbox label="underline">{{ $t('Underline') }}</el-checkbox>
    </el-checkbox-group>
  </div>
</template>
<script>
export default {
  name: "FontWeight",

  model: {
    prop: "value",
    event: "update"
  },
  props: {
    value: {
      type: Array,
      default: () => []
    },
    label: {
      type: String,
      default: ""
    }
  }
};
</script>
