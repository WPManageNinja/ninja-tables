<template>
  <el-tabs v-model="activeName" @tab-click="handleClick" type="border-card" class="ninja-tables-component">
    <el-tab-pane :label="$t('Elements')" name="elements" v-if="!manageCell.active">
      <el-collapse
          accordion
          v-model="activeNames"
          @change="handleChange"
          v-for="(component, index) in initialData.components"
          :key="component.key"
          class="accordions"
      >
        <el-collapse-item v-if="component.key !== 'container'" :title="component.name" :name="index">
          <el-row :gutter="20" align="middle" justify="center">
            <draggable
                :list="component.fields"
                :group="{ name: 'people', pull: 'clone', put: false }"
                :clone="customClone"
                @end="end"
            >
              <el-col
                  class="element-style"
                  :span="12"
                  v-for="(item, index) in component.fields"
                  :key="index"
              >
                <el-badge :value="(!hasPro && item.has_pro) ? 'Pro': ''" class="item">
                  <el-button :disabled="!hasPro && item.has_pro"
                             class="button-component"
                             :class="!hasPro && item.has_pro ? 'pro-component' : ''"
                             size="small"
                             type="default" plain
                             :icon="item.icon !== null ? item.icon : 'el-icon-s-grid'">{{ item.name }}
                  </el-button>
                </el-badge>
              </el-col>
            </draggable>
          </el-row>
        </el-collapse-item>
      </el-collapse>
    </el-tab-pane>
    <el-tab-pane :label="$t('Options')"
                 name="options"
                 v-if="item && item.data && item.data.type && !manageCell.active && showOptions"
    >
      <el-collapse v-model="activeOption" accordion @change="accorDianChange" class="accordions">
        <el-collapse-item :title="$t(ucWords(item.data.type)+ ' Options')" name="1">
          <text-option
              v-if="item.data.type === 'text'"
              :item="item"
          ></text-option>
          <button-option
              v-else-if="item.data.type === 'button'"
              :item="item"
          ></button-option>
          <star-rating
              v-else-if="item.data.type === 'star_rating'"
              :item="item"
          ></star-rating>
          <icon-option
              v-else-if="item.data.type === 'icon'"
              :item="item"
          ></icon-option>
          <progress-option
              v-else-if="item.data.type === 'progress'"
              :item="item"
          ></progress-option>
          <list-option
              v-else-if="item.data.type === 'list'"
              :item="item"
              :settings="initialData.settings"
          ></list-option>
          <custom-html-option
              v-else-if="item.data.type === 'custom_html'"
              :item="item"
          ></custom-html-option>
          <shortcode-option
              v-else-if="item.data.type === 'shortcode'"
              :item="item"
          ></shortcode-option>
          <stylist-list-option
              v-else-if="item.data.type === 'stylist_list'"
              :item="item"
              :settings="initialData.settings"
          ></stylist-list-option>
          <image-option
              v-else-if="item.data.type === 'image'"
              :item="item"
          ></image-option>
          <text-icon-option
              v-else-if="item.data.type === 'text_icon'"
              :item="item"
          ></text-icon-option>
          <ribbon-option
              v-else-if="item.data.type === 'ribbon'"
              :item="item"
              :maxWidth="maxWidth"
              :settings="initialData.settings"
          ></ribbon-option>
        </el-collapse-item>
        <el-collapse-item :title="$t('Spacing')" name="2" v-if="item.data.type !== 'ribbon'">
          <spacing-input :types="['margin', 'padding']" :item="item"></spacing-input>
        </el-collapse-item>
      </el-collapse>
    </el-tab-pane>
    <el-tab-pane :label="$t('Settings')" name="settings" v-if="!manageCell.active">
      <el-collapse
          accordion
          v-model="activeNames"
          @change="handleChange"
          v-for="(setting, key) in initialData.settings"
          :key="key"
          class="accordions"
      >
        <el-collapse-item :name="key">
          <template slot="title">
            {{ setting.name }}
            <el-tooltip v-if="setting.name === 'Global Style' || setting.name === 'Sticky'" placement="top-start" effect="light">

              <template slot="content">
                <h3>{{ setting.name }}</h3>

                <p v-if="setting.name === 'Sticky'">
                  This is a Pro feature.
                  <get-pro/>
                </p>

                <p v-else>The global style will be applied if the <br> component individual style is not applied.</p>
              </template>

              <i style="margin-left: 2px" class="el-icon-info el-text-info"></i>
            </el-tooltip>
          </template>
          <div class="component-spacing" v-for="(item, tabKey, index) in setting.options" :key="tabKey">
            <all-input-element  :disableResponsive="getBoolean(!hasPro && setting.has_pro)" :item="item"></all-input-element>
            <template v-if="item.childs && (getBoolean(item.value))">
              <template v-for="(singleItem, childKey, childIndex) in item.childs">
                 <all-input-element
                     :key="childKey"
                     :item="singleItem"
                     class="component-spacing"
                 >
                </all-input-element>
              </template>
            </template>
          </div>
        </el-collapse-item>
      </el-collapse>
      <el-collapse
          v-model="activeNames"
          class="accordions"
          @change="handleChange"
      >
        <el-collapse-item :title="$t('Export Table')" class="export">
          <select-input :items="exports.items" v-model="exports.format" :label="$t('Select Format')"></select-input>
          <el-button @click="exportTable" type="primary" size="mini">Export</el-button>
        </el-collapse-item>
      </el-collapse>
    </el-tab-pane>

    <el-tab-pane :label="$t('Responsiveness')" name="responsiveness" v-if="!manageCell.active">
      <el-collapse
          accordion
          v-model="activeNames"
          @change="handleChangeResponsive"
          v-for="(responsiveItem, key) in initialData.responsive"
          :key="key"
          class="accordions"
      >
        <el-collapse-item :title="responsiveItem.name" :name="key">
          <div class="component-spacing" v-for="(item, index) in responsiveItem.options" :key="index">
             <div v-if="'devices' === index">
                <el-tabs v-model="deviceActiveName" @tab-click="handleDeviceClick">
                  <el-tab-pane v-for="(devices, index) in item" :key="index" :label="devices.name" :name="devices.key">
                    <div v-for="(device, index, key) in devices" :key="index" v-if="('name' != index) && ('key' != index)">
                      <all-input-element
                          :initialData="initialData"
                          :item="device"
                          :disableResponsive="!enableResponsive"
                          :mobileDisableBreakpoint="devices.key === 'mobile' && isDisableMobileBreakpoint && device.key !== 'disable_breakpoint'"
                          :tabletDisableBreakpoint="devices.key === 'tablet' && isDisableTabletBreakpoint && device.key !== 'disable_breakpoint'"
                          :deviceName="devices.key"
                          class="component-spacing"
                      ></all-input-element>
                       </div>
                    </el-tab-pane>
                </el-tabs>
            </div>
            <div v-else>
              <all-input-element :item="item"></all-input-element>
            </div>
          </div>
        </el-collapse-item>
      </el-collapse>
    </el-tab-pane>
    <el-tab-pane :label="$t('Manage cell')" name="cells" v-if="manageCell.activeTab === 'cells'">
      <cell-setting :manageCell="manageCell" :setting="initialData.settings"></cell-setting>
      <el-button @click="closeCellEditing" type="primary" class="ntb-manage-button"> {{ $t('Close') }}</el-button>
    </el-tab-pane>
    <el-tab-pane :label="$t('Background')" name="background" v-if="manageCell.activeTab === 'background'">
      <background-color :manageCell="manageCell"></background-color>
      <el-button @click="closeCellEditing" type="primary" class="ntb-manage-button"> {{ $t('Close') }}</el-button>
    </el-tab-pane>
  </el-tabs>
</template>

<script>
import draggable from "vuedraggable";
import AllInputElement from "../SettingComponent/AllInputElement";
import TextOption from "../OptionComponent/TextOption";
import ButtonOption from "../OptionComponent/ButtonOption";
import StarRating from "../OptionComponent/StarRatingOption";
import IconOption from "../OptionComponent/IconOption";
import ProgressOption from "../OptionComponent/ProgressOption";
import ListOption from "../OptionComponent/ListOption";
import CustomHtmlOption from "../OptionComponent/CustomHTML";
import ShortcodeOption from "../OptionComponent/ShortcodeOption";
import StylistListOption from "../OptionComponent/StylistListOption";
import ImageOption from "../OptionComponent/ImageOption";
import SpacingInput from "../SettingComponent/SpacingInput";
import TextIconOption from "../OptionComponent/TextIconOption";
import RibbonOption from "../OptionComponent/RibbonOption";
import ColorInput from "../SettingComponent/ColorInput";
import BackgroundColor from "./_Background";
import CellSetting from "./_CellSetting";
import SelectInput from "../SettingComponent/SelectInput";
import {helpers} from "../Mixin/helpers";
import GetPro from "../../Tools/GetPro";

export default {
  name: "LeftSideBar",
  props: ["initialData", 'singleItem', 'selectedDevice'],
  mixins: [helpers],
  components: {
    GetPro,
    SelectInput,
    CellSetting,
    BackgroundColor,
    ColorInput,
    RibbonOption,
    TextIconOption,
    SpacingInput,
    ButtonOption,
    AllInputElement,
    draggable,
    TextOption,
    StarRating,
    IconOption,
    ProgressOption,
    ListOption,
    CustomHtmlOption,
    ShortcodeOption,
    StylistListOption,
    ImageOption
  },
  data() {
    return {
      activeName: "elements",
      activeNames: ["general"],
      deviceActiveName: 'desktop',
      deviceLastSelected: '',
      activeOption: 1,
      item: {},
      manageCell: {},
      maxWidth: {},
      exports: {
        items: [
          {label: 'CSV', value: 'csv'},
          {label: 'JSON', value: 'json'}
        ],
        format: 'csv'
      }
    };
  },
  methods: {
    exportTable() {
      location.href = this.downloadLink(this.exports.format);
    },
    downloadLink(format = 'csv') {
      const data = {
        action: 'ninja_tables_builder_ajax_actions',
        target_action: 'export-table',
        table_id: this.$route.params.table_id,
        format: format,
        ninja_table_admin_nonce: window.ninja_table_admin.ninja_table_admin_nonce
      };

      return ajaxurl + '?' + jQuery.param(data)
    },
    maximumWidth(tdId) {
      let width = jQuery('td#' + tdId).attr('style').split(';')[1];
      return width.split(' ')[2].split('px')[0];
    },
    end($event) {
      if ($event && $event.to.id) {
        this.maxWidth = this.maximumWidth($event.to.id)
      }
    },
    customClone($event) {
      if (!this.hasPro && $event.has_pro) {
        this.upgradeMessage();
      } else {
        const item = {
          id: this.id(),
          data: this.deepClone($event)
        }
        return item;
      }
    },

    handleClick(tab, event) {
      const responsiveTabPress = tab.$options.propsData.name === 'responsiveness';
      if (responsiveTabPress) {
        this.deviceLastSelected === '' ? this.$emit('deviceSelected', '') : this.$emit('deviceSelected', this.deviceLastSelected);
      } else {
        this.$emit('deviceSelected', '');
      }

      if (tab.$options.propsData.name !== 'options') {
        window.ninjaTableBus.$emit('manageCell');
      }
    },
    handleChange(val) {
      if (this.deviceLastSelected !== '') {
        this.deviceLastSelected = '';
        this.$emit('deviceSelected', '');
      }
    },
    handleDeviceClick(tab, event) {
      const data = tab.$options.propsData.name;
      this.deviceActiveName = data;
      this.deviceLastSelected = data;
      this.$emit('deviceSelected', data);
    },
    handleChangeResponsive(val) {
      if(val === 'responsive_settings' && this.deviceActiveName === 'desktop'){
        this.deviceActiveName = 'tablet';
      }
      this.deviceLastSelected = this.deviceActiveName;
      this.$emit('deviceSelected', this.deviceActiveName);
    },
    accorDianChange(val) {
      this.activeOption = val
    },
    manageRowColumns() {
      window.ninjaTableBus.$on("manage-cell", (items) => {
        // received event from layout component
        if (items.active) {
          this.manageCell = items
          this.activeName = items.activeTab
        } else {
          this.closeCellEditing();
        }
      });
    },
    closeCellEditing() {
      this.manageCell = false
      this.activeName = "elements";
      window.ninjaTableBus.$emit('manageCell');
    }
  },
  created() {
    this.manageRowColumns();

    window.ninjaTableBus.$on("closeManageCell", () => {
      this.closeCellEditing();
    });

    window.ninjaTableBus.$on('singleTdId', (tdId) => {
      this.maxWidth = this.maximumWidth(tdId)
    });
  },
  computed: {
    hasPro() {
      return !!window.ninja_table_admin.hasPro;
    },
    enableResponsive() {
      const responsiveEnableStatus = this.initialData.responsive.general.options.enable_responsive_table.value;
      return this.getBoolean(responsiveEnableStatus);
    },
    showOptions() {
      // note: when admin is in the options tab then we can only show options tab, otherwise don't.
      return this.activeName === 'options';
    },
    responsiveDevice() {
      return this.initialData.responsive.mode_options.options.devices;
    },
    isDisableMobileBreakpoint() {
      let isDisableMobileBreakpoint = this.responsiveDevice.mobile.disable_breakpoint.value;
      return this.getBoolean(isDisableMobileBreakpoint);
    },
    isDisableTabletBreakpoint() {
      let isDisableTabletBreakpoint = this.responsiveDevice.tablet.disable_breakpoint.value;
      return this.getBoolean(isDisableTabletBreakpoint);
    },
  },
  watch: {
    singleItem: {
      handler(newValue, oldValue) {
        if (newValue) {
          this.activeOption = this.activeOption === '2' ? "2" : "1"
          this.activeName = "options";
          this.item = newValue.item
        } else {
          this.activeName = "elements";
        }
      },
      deep: true
    }
  }
};
</script>
<style lang="scss">
.ninja-tables-component {

  .el-collapse-item__content {
    padding-bottom: 6px;
  }
  .accordions {
    .export {
      .el-select {
        width: 50%;
      }

      .el-button {
        width: 100%;
        margin-top: 10px;
      }
    }

    > .is-active {
      max-height: 410px;
      overflow-y: scroll;

      &::-webkit-scrollbar {
        width: .2em;
      }

      &::-webkit-scrollbar-thumb {
        background-color: #409EFF;
        outline: 1px solid #409EFF;
      }

      .el-collapse-item__arrow.is-active {
        margin-right: 6px;
      }
    }

    .el-tabs__item {
      &.is-active {
        display: initial;
      }
    }
  }

  .el-slider__runway {
    margin-left: 12px;
  }

  .el-slider {
    margin-left: 10px;
  }

  .element-style {
    padding: 10px 0px;
    display: flex;
    align-items: center;
    justify-content: center;

    .button-component {
      width: 120px;
      padding: 15px 15px;

      i {
        font-size: 15px;
      }

      &:hover {
        cursor: move;
      }
    }

    .pro-component {
      &:hover {
        cursor: not-allowed;
      }
    }

    .item {
      .el-badge__content {
        right: 33px;
      }
    }
  }

  .ntb-manage-button {
    display: block;
    margin: 10px auto;
    padding: 10px 20px
  }

  .component-spacing, .component-wrapper > * {
    padding: 6px 0px;
  }
}

</style>
