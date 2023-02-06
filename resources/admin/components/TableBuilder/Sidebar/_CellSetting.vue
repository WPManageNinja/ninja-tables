<template>
  <div>
    <div
        v-if="manageCell.data.tdIds.length !== 1">
      <p>
        {{ $t('Click on a cell to get cell-specific options. Selected cells will be highlighted.') }} <br><br>
        {{
          $t('Hold "SHIFT" and click on cells to select multiple cells for merge cells.')
        }}
      </p>
    </div>
    <div v-else-if="manageCell.data.tdIds.length === 1">
      <!--      Note: Here hc = Highlighted Column (used this prefix in v-model & onChange)-->
      <div class="block">
        <div class="component-spacing">
          <span>{{ $t('Highlight Column') }}</span>
          <el-tooltip placement="top-start" effect="light">
            <template slot="content">
              <h3>{{ $t('Highlight Column') }}</h3>
              <p>This is a Pro feature.
                <get-pro></get-pro>
              </p>
            </template>
            <i style="margin-left: 2px" class="el-icon-info el-text-info"></i>
          </el-tooltip>
          <el-switch style="float: right;" @change="changeHcActive" @input="changeHcActive" v-model="hcActive" :disabled="!hasPro && highlightedIsPro"></el-switch>
        </div>
        <div v-if="hcActive" class="component-wrapper">
          <div>
            <span>{{ $t('Highlighted Column Height') }}</span><br>
            <el-slider input-size="mini" v-model="manageCell.data.table.column.style.highlighted.height" @input="changeHcHeight" :max="40" :min="1" :step="1" show-input></el-slider>
          </div>
          <div class="space-between">
            <span>{{ $t('Shadow Color') }}</span>
            <el-color-picker size="mini" v-model="manageCell.data.table.column.style.highlighted.shadowColor" @active-change="changeHcShadowColor" @change="changeHcShadowColor" :predefine="predefineColors"></el-color-picker>
          </div>
          <div>
            <span>{{ $t('Shadow Effect (X-Axis)') }}</span><br>
            <el-slider input-size="mini" v-model="manageCell.data.table.column.style.highlighted.offset_x" @input="changeHcOffsetX" :max="30" :min="-30" :step="1" show-input></el-slider>
          </div>
          <div>
            <span>{{ $t('Shadow Effect (Y-Axis)') }}</span><br>
            <el-slider input-size="mini" v-model="manageCell.data.table.column.style.highlighted.offset_y" @input="changeHcOffsetY" :max="30" :min="1" :step="1" show-input></el-slider>
          </div>
          <div>
            <span>{{ $t('Shadow Effect (Blur Radius)') }}</span><br>
            <el-slider input-size="mini" v-model="manageCell.data.table.column.style.highlighted.blur_radius" @input="changeHcBlurRadius" :max="30" :min="10" :step="1" show-input></el-slider>
          </div>
        </div>
      </div>
      <div class="block">
        <span>{{ $t('Row height') }}</span>
        <el-slider input-size="mini" v-model="rowHeight" :max="500" :min="50" :step="1" show-input></el-slider>
      </div>
      <div class="block">
        <span>{{ $t('Column Width') }}</span>
        <el-slider input-size="mini" @change="changeColumnWidth" v-model="columnWidth" :max="500" :min="100" :step="1" show-input></el-slider>
      </div>
    </div>
  </div>
</template>

<script>
import GetPro from "../../Tools/GetPro";
export default {
  name: "CellSetting",
  data() {
    return {
      predefineColors: [
        "#ff4500",
        "#ff8c00",
        "#ffd700",
        "#90ee90",
        "#00ced1",
        "#1e90ff",
        "#c71585",
        "#000000",
        "#dddddd",
        "#379D13"
      ]
    }
  },
  components: {
    GetPro
  },
  props: ['manageCell', 'setting'],
  methods: {
    updateChangesValues(key, value, highlighted = false) {
      let table = this.manageCell.data.tableData;
      let header = this.manageCell.data.headers[this.manageCell.data.table.columnIndex];
      table.forEach(rows => {
        if (highlighted) {
          rows.rows[header].style.highlighted[key] = value;
        } else {
          rows.rows[header].style[key] = value;
        }
      })
    },
    changeColumnWidth() {
      this.updateChangesValues('columnWidth', this.columnWidth);
    },
    changeHcActive() {
      this.updateChangesValues('active', this.hcActive, true);
    },
    changeHcHeight(val) {
      this.updateChangesValues('height', val, true);
      this.changeMarginTopBottom();
    },
    changeHcShadowColor(color) {
      let cl = color;
      if (color === null) {
        cl = '';
      }
      this.updateChangesValues('shadowColor', cl, true);
    },
    changeHcOffsetX(val) {
      this.updateChangesValues('offset_x', val, true);
    },
    changeHcOffsetY(val) {
      this.updateChangesValues('offset_y', val, true);
    },
    changeHcBlurRadius(val) {
      this.updateChangesValues('blur_radius', val, true);
      this.changeMarginTopBottom();
    },
    changeMarginTopBottom() {
      let table = this.manageCell.data.tableData;
      let rowIndex = this.manageCell.data.table.rowIndex;
      let margin_tb_Value = 0;
      for (const [key, value] of Object.entries(table[rowIndex].rows)) {
        let count = 0;
        count += value.style.highlighted.active ? value.style.highlighted.height : 0;
        count += value.style.highlighted.active ? value.style.highlighted.blur_radius : 0;
        count > margin_tb_Value ? margin_tb_Value = count : '';
      }
      this.setting.global_styling.options.margin_top.value = margin_tb_Value;
      this.setting.global_styling.options.margin_bottom.value = margin_tb_Value + 30;
    }
  },
  computed: {
    hasPro() {
      return !!window.ninja_table_admin.hasPro;
    },
    highlightedIsPro() {
      return Boolean(this.manageCell.data.table.column.style.highlighted.has_pro);
    },
    hcActive: {
      get() {
        return this.manageCell.data.table.column.style.highlighted.active;
      },
      set(newValue) {
        this.manageCell.data.table.column.style.highlighted.active = newValue;
        this.changeMarginTopBottom();
      }
    },
    rowHeight: {
      get() {
        return Number(this.manageCell.data.table.row.style.rowHeight);
      },
      set(newValue) {
        this.manageCell.data.table.row.style.rowHeight = newValue;
      }
    },
    columnWidth: {
      get() {
        let minAutoWidth = this.manageCell.data.setting.general.options.cell_min_auto_width.value;
        let colWidth = this.manageCell.data.table.column.style.columnWidth;
        return colWidth ? Number(colWidth) : Number(minAutoWidth);
      },
      set(newValue) {
        this.manageCell.data.table.column.style.columnWidth = newValue;
      }
    }
  }
}
</script>
