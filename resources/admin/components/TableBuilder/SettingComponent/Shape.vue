<template>
  <div class="block">
    <span>{{ label }}</span><br>
    <el-radio-group
        :model-value="modelValue"
        @update:model-value="updateValue"
        style="margin-top: 5px;">
      <el-radio :label="$t('Square')" value="square" />
      <el-radio :label="$t('Round')" value="round" />
    </el-radio-group>
  </div>
</template>
<script>
export default {
  name: "Shape",

    props: {
        modelValue: {
            type: String,
            default: 'square'
        },
        label: {
            type: String,
            default: ""
        }
    },
    emits: ['update:modelValue'],
    methods: {
        updateValue(newValue) {
            this.$emit('update:modelValue', newValue);
        }
    }
};
</script>
