<template>
  <div class="component-wrapper">
    <div>
      <span>{{ $t('Progress Type') }}</span>
      <el-select v-model="item.data.style.type"
                 :placeholder="$t('Progress Bar Type')"
                  size="small"
                 style="width: 97%; margin-bottom: 7px;">
        <el-option
            v-for="type in types"
            :key="type"
            :label="type"
            :value="type">
        </el-option>
      </el-select>
    </div>
    <color-input
        :label="$t('Text Color')"
        v-model="item.data.style.color"
    ></color-input>
      <color-input
          :label="$t('Progress Bar Color')"
          v-model="item.data.style.progressColor"
      ></color-input>
    <slider
        :label="$t('Text Font Size')"
        :max="50"
        :min="10"
        :step="1"
        v-model="fontSizeValue"
    ></slider>
    <slider
        :label="$t('Progressbar Percentage')"
        :max="100"
        :min="1"
        :step="1"
        v-model="item.data.style.percentage"
    ></slider>
    <slider
        v-if="item.data.style.type !== 'line'"
        :label="$t('Progressbar Width')"
        :max="150"
        :min="50"
        :step="1"
        v-model="item.data.style.width"
    ></slider>
    <slider
        :label="$t('Progressbar Thickness')"
        :max="15"
        :min="2"
        :step="1"
        v-model="item.data.style.thickness"
    ></slider>
    <alignment
        :label="$t('Alignment')"
        v-if="item.data.style.type !== 'line'"
        v-model="item.data.style.alignment"
    ></alignment>
  </div>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";
import Slider from "../SettingComponent/SliderInput";
import Alignment from "../SettingComponent/Alignment";
import {manageDataElement} from "../Mixin/manageDataElement";

export default {
  name: "ProgressOption",
  data() {
    return {
      types:['line', 'circle', 'dashboard']
    }
  },
  props: ["item", "settings"],
  mixins: [manageDataElement],
  components: {
    ColorInput,
    Slider,
    Alignment
  }
};
</script>
