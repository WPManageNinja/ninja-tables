<template>
  <div class="block">
    <span>{{ label }}</span><br>
    <el-select :value="mainValue" @change="$emit('update', $event)" placeholder="Select" size="mini"
               :disabled="disableResponsive || mobileDisableBreakpoint || tabletDisableBreakpoint">
      <el-option
          v-for="item in items"
          :key="item.value"
          :label="item.label"
          :value="item.value">
      </el-option>
    </el-select>
  </div>
</template>

<script>
export default {
  name: "SelectInput",
  data() {
    return {};
  },
  model: {
    prop: "value",
    event: "update"
  },
  computed: {
    mainValue: {
      get() {
        return this.value;
      },
      set(newVal) {
        this.value = newVal
      }
    }
  },
  props: {
    value: {
      type: String,
      default: ""
    },
    label: {
      type: String,
      default: ""
    },
    items: {
      type: Array,
      default: ""
    },
    disableResponsive: {
      type: Boolean,
      default: false
    },
    mobileDisableBreakpoint: {
      type: Boolean,
      default: false
    },
    tabletDisableBreakpoint: {
      type: Boolean,
      default: false
    }
  },

}
</script>







