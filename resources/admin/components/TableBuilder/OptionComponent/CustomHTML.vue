<template>
  <div>
    <el-input
        type="textarea"
        :autosize="{ minRows: 2 }"
        :placeholder="$t('write html')"
        v-model="item.data.value"
    >
    </el-input>
  </div>
</template>

<script>

export default {
  name: "CustomHTML",
  props: ["item"]
};
</script>
