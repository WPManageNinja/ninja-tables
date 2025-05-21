<template>
  <div>
    <el-tabs v-model="activeName" class="nt_tab_design_drag">
      <i @click="lock = !lock"
         style="font-size: 25px; text-align: right; display: block"
         :class="`el-icon-${lock === false ? 'unlock' : 'lock'}`">
      </i>
      <el-tab-pane
          :label="$t(type === 'margin' ? 'Margin' : 'Padding')"
          :name="type"
          :key="type"
          v-for="type in types"
      >
        <slider-input
            :label="$t('Top')"
            v-model="top"
            :max="100"
            :min="0"
        >
        </slider-input>
        <slider-input
            :label="$t('Bottom')"
            v-model="bottom"
            :max="100"
            :min="0"
        >
        </slider-input>
        <slider-input
            :label="$t('Left')"
            v-model="left"
            :max="100"
            :min="0"
        >
        </slider-input>
        <slider-input
            :label="$t('Right')"
            v-model="right"
            :max="100"
            :min="0"
        >
        </slider-input>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>
<script>
import SliderInput from "./SliderInput";

export default {
  name: "SpacingInput",
  data() {
    return {
      lock: false,
      activeName: "margin",
    };
  },
  props: ["item", "types"],
  components: {
    SliderInput
  },
  methods: {
    marginPadding(newVal, position) {
      if (this.lock) {
        this.item.data.style[this.activeName].top = newVal
        this.item.data.style[this.activeName].bottom = newVal
        this.item.data.style[this.activeName].left = newVal
        this.item.data.style[this.activeName].right = newVal
      } else {
        this.item.data.style[this.activeName][position] = newVal
      }
    }
  },
  computed: {
    top: {
      get() {
        return this.item.data.style[this.activeName].top
      },
      set(newVal) {
        this.marginPadding(newVal, 'top')
      }
    },
    bottom: {
      get() {
        return this.item.data.style[this.activeName].bottom
      },
      set(newVal) {
        this.marginPadding(newVal, 'bottom')
      }
    },
    left: {
      get() {
        return this.item.data.style[this.activeName].left
      },
      set(newVal) {
        this.marginPadding(newVal, 'left')
      }
    },
    right: {
      get() {
        return this.item.data.style[this.activeName].right
      },
      set(newVal) {
        this.marginPadding(newVal, 'right')
      }
    }
  }
};
</script>
