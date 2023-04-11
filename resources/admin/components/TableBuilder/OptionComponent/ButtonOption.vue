<template>
  <el-tabs v-model="activeName">
    <el-tab-pane :label="$t('General')" name="general" class="component-wrapper">
      <el-input
          size="mini"
          type="text"
          :placeholder="$t('Enter here')"
          v-model="item.data.value"
          style="width: 96%; margin-bottom: 3px;"
      ></el-input>
      <color-input
          :label="$t('Background color')"
          v-model="item.data.style.backgroundColor"
      ></color-input>
      <color-input
          :label="$t('Border color')"
          v-model="item.data.style.borderColor"
      ></color-input>
      <slider-input
          :label="$t('Border Size')"
          v-model="item.data.style.borderSize"
          :max="10"
          :min="0"
          :step="1"
      >
      </slider-input>
      <slider-input
          :label="$t('Border Radius')"
          v-model="item.data.style.borderRadius"
          :max="30"
          :min="0"
          :step="1"
      >
      </slider-input>
      <switch-input
          :label="$t('Full Width')"
          v-model="item.data.style.fullWidth"
      ></switch-input>
        <radio-button
            v-if="item.data.style.fullWidth === false"
            :label="$t('Button Size')"
            v-model="item.data.style.buttonSize"
            :options="buttonSize">
        </radio-button>
      <alignment
          v-if="item.data.style.fullWidth === false"
          :label="$t('Alignment')"
          v-model="item.data.style.alignment"
      ></alignment>
      <text-input :label="$t('URL')" v-model="item.data.style.url"></text-input>
      <switch-input
          style="margin-top:12px"
          :label="$t('Open in another tab')"
          v-model="item.data.style.newTab"
      >
      </switch-input>
    </el-tab-pane>
    <el-tab-pane :label="$t('Text')" name="text" class="component-wrapper">
      <color-input
          :label="$t('Font color')"
          v-model="item.data.style.color"
      ></color-input>
      <slider-input
          v-model="item.data.style.fontSize"
          :label="$t('Font Size')"
          :max="50"
          :min="10"
          :step="1"
      ></slider-input>
      <font-weight
          :label="$t('Font Style')"
          v-model="item.data.style.fontWeight"
      >
      </font-weight>
    </el-tab-pane>
    <el-tab-pane :label="$t('Icon')" name="icon">
      <template v-if="!hasPro">
        <span class="text-warning">{{$t('You are using free version of ninja-tables')}}</span>
      </template>
      <switch-input
          v-if="hasPro"
          :label="$t('Enable Icon')"
          v-model="item.data.style.enableIcon"
          class="component-spacing"
      ></switch-input>

      <div v-if="item.data.style.enableIcon && hasPro" class="component-wrapper">
        <color-input
            v-if="extension"
            :label="$t('Icon Color')"
            v-model="item.data.style.iconColor"
        ></color-input>
        <slider-input
            :label="$t('Icon Size')"
            :max="50"
            :min="10"
            :step="1"
            v-model="item.data.style.iconFontSize"
        ></slider-input>
        <slider-input
            :label="$t('Item Spacing')"
            :max="100"
            :min="0"
            :step="1"
            v-model="item.data.style.itemSpacing"
        ></slider-input>
        <icon :item="item" setValue="iconName"></icon>
        <div>
          <p>{{ $t('Icon Position') }}</p>
          <el-select size="mini" v-model="item.data.style.iconPosition" placeholder="Select">
            <el-option
                v-for="item in ['left', 'right']"
                :key="item"
                :label="item"
                :value="item">
            </el-option>
          </el-select>
        </div>
      </div>
    </el-tab-pane>

    <el-tab-pane :label="$t('Hover')" name="hover" class="component-wrapper">
      <switch-input
          :label="$t('Button Hover')"
          v-model="item.data.style.isHover"
      ></switch-input>
      <color-input
          :label="$t('Background Color')"
          v-model="item.data.style.hoverBackgroundColor"
          v-if="item.data.style.isHover"
      ></color-input>
      <color-input
          :label="$t('Text Color')"
          v-model="item.data.style.hoverColor"
          v-if="item.data.style.isHover"
      ></color-input>
      <color-input
          :label="$t('Icon Color')"
          v-model="item.data.style.hoverIconColor"
          v-if="item.data.style.isHover && item.data.style.enableIcon && hasPro && extension"
      ></color-input>
      <color-input
          :label="$t('Border color')"
          v-model="item.data.style.hoverBorderColor"
          v-if="item.data.style.isHover"
      ></color-input>
      <slider-input
          :label="$t('Border Size')"
          v-model="item.data.style.hoverBorderSize"
          v-if="item.data.style.isHover"
          :max="10"
          :min="0"
          :step="1"
      >
      </slider-input>
      <slider-input
          :label="$t('Scale')"
          :max="3"
          :min="1"
          :step="0.1"
          v-model="item.data.style.transition"
          v-if="item.data.style.isHover"
      ></slider-input>
    </el-tab-pane>
  </el-tabs>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";
import SliderInput from "../SettingComponent/SliderInput";
import SwitchInput from "../SettingComponent/SwitchInput";
import TextInput from "../SettingComponent/TextInput";
import Alignment from "../SettingComponent/Alignment.vue";
import Icon from "./SplitComponent/Icon";
import FontWeight from "../SettingComponent/FontWeight"
import RadioButton from "../SettingComponent/RadioButton"

export default {
  data() {
    return {
      activeName: "general",
        buttonSize: [
            {
                value: "small",
                label: "S"
            },
            {
                value: "medium",
                label: "M"
            },
            {
                value: "large",
                label: "L"
            }
        ]
    };
  },
  name: "ButtonOption",
  props: ["item"],
  components: {
    ColorInput,
    SliderInput,
    SwitchInput,
    TextInput,
    Icon,
    Alignment,
    FontWeight,
    RadioButton
  },
  computed: {
    hasPro() {
      return !!window.ninja_table_admin.hasPro;
    },
    extension() {
      const item = this.item.data.style.iconName;
      const last4 = item.slice(-4);
      return last4.slice(-4) === '.svg' || !last4.includes('.');
    }
  }
};
</script>
