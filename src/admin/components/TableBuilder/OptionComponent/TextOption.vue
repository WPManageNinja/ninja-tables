<template>
  <div class="component-wrapper">
    <el-input
        type="textarea"
        :autosize="{ minRows: 3 }"
        :placeholder="$t('Enter here')"
        v-model="item.data.value"
        style="width: 96%; margin-bottom: 3px;"
    >
    </el-input>
    <color-input :label="$t('Font color')" v-model="item.data.style.color"></color-input>
    <slider-input :label="$t('Font Size')" v-model="item.data.style.fontSize" :min="10" :max="45"
                  :step="1"></slider-input>
    <alignment :label="$t('Alignment')" v-model="item.data.style.alignment"></alignment>
    <font-weight :label="$t('Font Weight')" v-model="item.data.style.fontWeight"></font-weight>
  </div>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";
import SliderInput from "../SettingComponent/SliderInput";
import Alignment from "../SettingComponent/Alignment";
import FontWeight from '../SettingComponent/FontWeight'

export default {

  name: "TextOption",
  props: ["item"],
  components: {
    ColorInput,
    SliderInput,
    Alignment,
    FontWeight
  }
}
</script>

