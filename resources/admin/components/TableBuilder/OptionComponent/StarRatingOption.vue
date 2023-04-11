<template>
  <div class="component-wrapper">
    <slider-input
        :label="$t('Star Size')"
        :max="50"
        :min="10"
        :step="1"
        v-model="item.data.style.ratingSize"
    ></slider-input>
    <alignment-input
        :label="$t('Align')"
        v-model="item.data.style.alignment"
    ></alignment-input>
    <color-input
        :label="$t('Color')"
        v-model="item.data.style.color"
    ></color-input>
    <switch-input
        :label="$t('Show Rating Value')"
        v-model="item.data.style.showRatingValue"
    ></switch-input>
    <slider-input
        :label="$t('Maximum Star')"
        :max="10"
        :min="3"
        :step="1"
        v-model="item.data.style.maxStar"
    ></slider-input>
  </div>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";
import AlignmentInput from "../SettingComponent/Alignment";
import SwitchInput from "../SettingComponent/SwitchInput";
import SliderInput from "../SettingComponent/SliderInput";

export default {
  name: "StarRatingOption",
  props: ["item"],
  components: {
    SliderInput,
    ColorInput,
    AlignmentInput,
    SwitchInput
  }
};
</script>

