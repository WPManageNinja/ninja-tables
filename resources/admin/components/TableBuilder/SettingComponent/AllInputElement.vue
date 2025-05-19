<template>
  <div>
    <slider-input v-if="item.type === 'slider'"
                  v-model="item.value"
                  :label="item.label"
                  :max="maxValue"
                  :min="item.min"
                  :disableSlider="showSlider"
                  :disableResponsive="disableResponsive"
                  :enableResponsiveNClickDesktop="this.deviceName === 'desktop'"
                  :mobileDisableBreakpoint="mobileDisableBreakpoint"
                  :tabletDisableBreakpoint="tabletDisableBreakpoint"
    ></slider-input>
    <switch-input v-else-if="item.type === 'switch'"
                  v-model="item.value"
                  :label="$t(item.label)"
                  :disableResponsive="disableResponsive"
                  :enableResponsiveNClickDesktop="this.deviceName === 'desktop'"
                  :mobileDisableBreakpoint="mobileDisableBreakpoint"
                  :tabletDisableBreakpoint="tabletDisableBreakpoint"
    ></switch-input>
    <alignment :label="$t(item.label)"
               v-else-if="item.type === 'alignment'"
               v-model="item.value"
               :disableResponsive="disableResponsive"
               :mobileDisableBreakpoint="mobileDisableBreakpoint"
               :tabletDisableBreakpoint="tabletDisableBreakpoint"
    ></alignment>
    <color-input v-else-if="item.type === 'color'" v-model="item.value" :label="$t(item.label)"></color-input>
    <select-input :label="$t(item.label)"
                  v-else-if="item.type === 'select'"
                  v-model="item.value"
                  :items="item.items"
                  :disableResponsive="disableResponsive"
                  :mobileDisableBreakpoint="mobileDisableBreakpoint"
                  :tabletDisableBreakpoint="tabletDisableBreakpoint"
    ></select-input>
  </div>
</template>
<script>
import SliderInput from "./SliderInput.vue";
import SwitchInput from "./SwitchInput.vue";
import Alignment from "./Alignment.vue";
import ColorInput from "./ColorInput.vue";
import SelectInput from "./SelectInput.vue";

export default {
  name: "AllInputElement",
  props: ["initialData", "item", "disableResponsive", "mobileDisableBreakpoint", "tabletDisableBreakpoint", "deviceName"],
  components: {
    SliderInput,
    SwitchInput,
    Alignment,
    ColorInput,
    SelectInput,
  },
  computed: {
    enableResponsive() {
      return this.initialData ? Boolean(this.initialData.responsive.general.options.enable_responsive_table.value) : false;
    },
    tableRow() {
      return this.initialData ? Number(this.initialData.table_data.table.tr) : null;
    },
    tableCol() {
      return this.initialData ? Number(this.initialData.table_data.table.tc) : null;
    },
    showHeaderMobile() {
      return this.initialData ? Boolean(this.initialData.responsive.mode_options.options.devices.mobile.top_row_as_header.value) : null;
    },
    showHeaderTablet() {
      return this.initialData ? Boolean(this.initialData.responsive.mode_options.options.devices.tablet.top_row_as_header.value) : null;
    },
    separateRowColumn() {
      return this.initialData ? Boolean(this.initialData.settings.general.options.columns_rows_separate.value) : null
    },
    maxValue() {
      if (this.item.key === 'items_per_header' && this.initialData) {
        if (this.tableRow == 1) {
          return this.tableCol;
        } else {
          if ((!this.showHeaderTablet && this.deviceName === 'tablet') || (!this.showHeaderMobile && this.deviceName === 'mobile')) {
            return this.tableRow * this.tableCol;
          }
          return this.tableRow - 1;
        }
      } else {
        return this.item.max;
      }
    },
    showSlider() {
      const _itemsPerHeader = this.initialData ? Number(this.initialData.responsive.mode_options.options.devices[this.deviceName].items_per_row.value) : null;
      if (!this.enableResponsive) {
        return false;
      } else if (this.tableRow === 1 && this.item.key == 'items_per_header' && ((this.deviceName === 'mobile' && this.showHeaderMobile) || (this.deviceName === 'tablet' && this.showHeaderTablet))) {
        return true;
      } else if (this.tableRow === 1 && (this.item.key == 'mobile_cell_border' || this.item.key == 'tablet_cell_border')) {
        return true;
      } else if ((this.item.key == 'mobile_cell_border' && !this.showHeaderMobile) || (this.item.key == 'tablet_cell_border' && !this.showHeaderTablet)) {
        return true;
      } else if (this.separateRowColumn && (this.item.key === 'mobile_cell_border' || this.item.key === 'tablet_cell_border')) {
        return true;
      } else if (_itemsPerHeader === this.tableRow - 1 && ((this.item.key === 'mobile_cell_border' && this.showHeaderMobile) || (this.item.key === 'tablet_cell_border' && this.showHeaderTablet))) {
        return true;
      } else {
        return false;
      }
    }
  }
};
</script>
