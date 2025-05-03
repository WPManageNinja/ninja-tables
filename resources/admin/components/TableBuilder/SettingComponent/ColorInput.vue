<template>
  <div class="block space-between">
    <span >{{ label }}</span>
    <el-color-picker
        size="small"
        :value="value"
        @change="colorUpdate"
        @active-change="colorUpdate"
        @input="$emit('update', $event)"
        :predefine="predefineColors"
        style="margin-right: 10px"
    ></el-color-picker>
  </div>
</template>
<script>
export default {
  name: "ColorInput",
  data() {
    return {
      color: "#ff4500",
      predefineColors: [
        "#ff4500",
        "#ff8c00",
        "#ffd700",
        "#90ee90",
        "#00ced1",
        "#1e90ff",
        "#c71585",
        "#000000",
        "#dddddd",
        "#379D13"
      ]
    };
  },
  model: {
    prop: "value",
    event: "update"
  },
  props: {
    value: {
      type: String,
      default: "#ffffff"
    },
    label: {
      type: String,
      default: ""
    }
  },
  methods: {
    colorUpdate(color) {
      let cl = color;
      if (color === null) {
        cl = '';
      }
      this.$emit('update', cl)
    }
  }
};
</script>

<style>
.space-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
