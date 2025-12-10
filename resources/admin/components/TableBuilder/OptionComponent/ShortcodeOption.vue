<template>
  <div class="component-wrapper">
    <el-input
        type="textarea"
        :autosize="{ minRows: 2 }"
        placeholder="write html"
        v-model="item.data.value"
    >
    </el-input>
    <alignment-input
        :label="$t('Align')"
        v-model="item.data.style.alignment"
    ></alignment-input>
  </div>
</template>

<script>
import AlignmentInput from "../SettingComponent/Alignment";

export default {
  name: "ShortcodeOption",
  props: ["item"],
  components: {
    AlignmentInput
  },
};
</script>
