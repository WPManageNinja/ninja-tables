<template>
  <div :style="[itemId === item.id ? margin : '']" class="ntb-elements-wrapper">
    <el-input
        v-if="itemId === item.id && dataTypes(item.data.type)"
        v-model="item.data.value"
        v-focus
        :style="`color:#ff0000`"
        @mouseleave.native="$emit('child-data', item.data)"
        @keyup.enter.native="$emit('child-data', 'clickEnter')"
    ></el-input>
    <el-rate
        v-else-if="itemId === item.id && item.data.type === 'star_rating'"
        v-model="item.data.value"
        :colors="starRatingStyling"
        :max="item.data.style.maxStar"
        :score-template="scoreTemplate"
        :show-score="showRatingScore"
        :style="[padding, ratingStyle, textAlign]"
        :text-color="`${item.data.style.color === '' ? setting.global_styling.options.color.value : item.data.style.color}`"
        allow-half
        class="ntb-rating"
        @mouseleave.native="$emit('child-data')"
        @keyup.enter="$emit('child-data')"
    >
    </el-rate>
    <span
        v-else-if="item.data.type === 'icon' && itemId === item.id"
        :style="[padding, displayBlock, textAlign]"
        @mouseleave="$emit('child-data')">
        <span :style="[iconStyle, textAlign, {'margin-top': '3px'}]"
              class="svgIcon">
        </span>
    </span>

    <span
        v-else-if="item.data.type === 'progress' && itemId === item.id"
        :style="[padding, displayBlock, textAlign]"
        class="ntb-progress"
        @mouseleave="$emit('child-data')"
    >
     <el-progress
         v-if="Number(item.data.style.percentage)"
         :color="[color]"
         :percentage="Number(item.data.style.percentage)"
         :stroke-width="Number(item.data.style.thickness)"
         :style="[progressBarTextStyle, {'margin-top': '3px'}]"
         :type="item.data.style.type"
         :width="Number(item.data.style.width)"
     ></el-progress>
    </span>

    <span
        v-else-if="item.data.type === 'image' && itemId === item.id"
        @mouseleave="$emit('child-data')">
      <a
          :href="`${item.data.style.link}`"
          :style="[displayFlex, justifyContent]"
          @click.prevent
      >
        <img :alt="`${item.data.style.alt}`" :src="item.data.value"
             :style="[padding, borderRadius, {'width': this.item.data.style.size+'%'}]">
      </a>
    </span>

    <span v-else-if="(item.data.type === 'list' || item.data.type === 'stylist_list') && itemId === item.id"
          :style="[textAlign]"
          @mouseleave="$emit('child-data')">
         <component :is="item.data.style.listType"
                    :style="[listStyle, padding]"
                    class="ntb-list-style">
      <li v-for="(val, index) in item.data.value" :key="index"
          :style="[color, fontSize, lineHeight]"
      >
           <span v-if="item.data.type === 'stylist_list'"
                 :style="[iconWithOtherComponent, textAlign, {'vertical-align': 'middle'}]"
                 class="svgIcon">
           </span>
           <span :style="[{'margin-left': item.data.style.itemSpacing +'px', 'vertical-align': 'middle'}]" v-html="val"></span>
      </li>
      <el-input
          v-model="item.data.value[listItem]"
          v-focus
          @mouseleave="$emit('child-data')"
          @keyup.enter.native="$emit('child-data', {item: item})"
      >
      </el-input>
    </component>
    </span>
    <span
        v-else-if="item.data.type === 'ribbon' && itemId === item.id"
        style="position:relative;margin:0;padding:0;width:100%;"
        @mouseleave="$emit('child-data')"
    >
     <div class="ribbon-wrapper"
          :style="[ { top: yAxisRibbon, left: xAxisRibbon }]">
        <div :class="[item.data.style.ribbonType, item.data.style.ribbonType === 'bookmark' ? 'up' : '']">
          <div :class="['content', item.data.style.ribbonPosition === 'left' ? 'left' : 'right']"
               :style="[ribbonSize, backgroundColor,{'text-align':'center', padding: item.data.style.ribbonType === 'corner' ? item.data.style.height+'px 0px' : ''}]">
            <p :style="[fontSize, color, fontWeight, {
              'margin-top': item.data.style.textYAxis+'px',
              'margin-left': item.data.style.textXAxis+'px'}]"
            >{{ item.data.value }}</p>
          </div>
        </div>
      </div>
    </span>
  </div>
</template>
<script>

import {manageDataElement} from "../Mixin/manageDataElement";

export default {
  name: "Elements",
  mixins: [manageDataElement],
  data() {
    return {
      icon: "",
      url: "",
      colors: {
        5: {value: "#F7BA2A"},
      },
    };
  },
  watch: {
    item: {
      handler(newVal, oldVal) {
        if (newVal.data.style.maxStar < newVal.data.value) {
          newVal.data.value = newVal.data.style.maxStar;
        }
      },
      deep: true
    }
  },
  methods: {
    getAsset(path) {
      if (path.includes(window.location.origin)) {
        return path;
      } else {
        return ninja_table_admin.ninja_tables_pro_url + '/assets/libs/icons/' + path + '.svg'
      }
    },
    dataTypes(type) {
      let types = ["text", "custom_html", "shortcode", "button", 'text_icon'];
      if (types.includes(type)) {
        return true;
      }
      return false;
    }
  },
  props: {
    item: {
      type: Object,
      default: null,
    },
    itemId: {
      type: [Number, String],
      default: 0,
    },
    listItem: {
      type: Number,
      default: null
    },
    setting: {
      type: Object,
      default: {}
    }
  },
  directives: {
    focus: {
      inserted(el) {
        el.querySelector('input').focus()
      }
    }
  }
};
</script>
<style lang="scss">
.ntb-elements-wrapper {
  &:hover {
    border: 1px solid #3f9eff;
  }
}

.svgIcon {
  display: inline-block;
  mask-size: cover;
  -webkit-mask-size: cover;
}

.ntb-progress {
  .el-progress {
    .el-progress__text {
      color: var(--progress-bar-text-color);
      font-size: var(--progress-bar-font-size) !important;
    }
  }
}

.remove-elements {
  .el-input {
    .el-input__inner {
      height: auto;
    }
  }
}
</style>


