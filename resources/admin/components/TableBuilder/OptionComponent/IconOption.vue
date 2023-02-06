<template>
  <div class="component-wrapper">
    <color-input
        v-if="extension"
        :label="$t('Color')"
        v-model="item.data.style.color"
    ></color-input>
    <slider
        :label="$t('Icon Size')"
        :max="150"
        :min="10"
        :step="1"
        v-model="item.data.style.fontSize"
    ></slider>
    <alignment-input
        :label="$t('Align')"
        v-model="item.data.style.alignment"
    ></alignment-input>
    <icon :item="item"></icon>
  </div>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";
import AlignmentInput from "../SettingComponent/Alignment";
import Slider from "../SettingComponent/SliderInput";
import Icon from "./SplitComponent/Icon";

export default {
  name: "IconOption",
  props: ["item"],
  components: {
    ColorInput,
    AlignmentInput,
    Slider,
    Icon
  },
  computed: {
    extension() {
      const item = this.item.data.value;
      const last4 = item.slice(-4);
      return last4.slice(-4) === '.svg' || !last4.includes('.');
    }
  }
};
</script>
