<template>
  <div>
    <h3 class="font-semibold">{{ $t('General Color Options') }}</h3>
    <div class="component-wrapper">
      <color-input :label="$t('Header Background')" v-model="headerBackground"></color-input>
      <color-input :label="$t('Even Row Background')" v-model="manageCell.data.setting.background.options.even_row_background.value"></color-input>
      <color-input :label="$t('Odd Row Background')" v-model="manageCell.data.setting.background.options.odd_row_background.value"></color-input>
    </div>
    <div>

      <div v-if="manageCell.data.tdIds.length === 1">
        <br>
        <h3 class="font-semibold">{{ $t('Selected Color Options') }}</h3>
        <div class="component-wrapper">
          <div class="block space-between">
            <span>{{ $t('Selected Cell Row Background') }}</span>
            <color-input
                v-model="trBackground"
                :predefine="predefineColors"
            ></color-input>
          </div>
          <div class="block space-between">
            <span>{{ $t('Selected Cell Column Background') }}</span>
            <color-input
                v-model="cellBackground"
                @active-change="changeTdBackground"
                @change="changeTdBackground"
                :predefine="predefineColors"
            ></color-input>
          </div>
        </div>
      </div>
      <div class="block component-spacing space-between" v-if="manageCell.data.tdIds.length >= 1">
        <span>{{ $t('Selected Cell Background') }}</span>
        <color-input
            v-model="cellBackground"
            @active-change="changeSelectedCellBackground"
            @change="changeSelectedCellBackground"
            :predefine="predefineColors"
        ></color-input>
      </div>

      <h5 v-if="manageCell.data.tdIds.length <= 0">
        <b>{{
            $t('Select a row/column/cell to change their background properties. Hold "SHIFT" and click on cells to select multiple cells.')
          }}</b>
      </h5>
    </div>
  </div>
</template>

<script>

import ColorInput from "../SettingComponent/ColorInput";

export default {
  name: "BackgroundColor",
  components: {ColorInput},
  props: ['manageCell'],
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
  methods: {
    changeTdBackground(color) {
      let cl = color;
      if (color === null) {
        cl = '';
      }
      let table = this.manageCell.data.tableData;
      table.forEach(rows => {
        rows.rows[this.manageCell.data.headers[this.manageCell.data.table.columnIndex]].style.backgroundColor = cl
      })
    },
    changeSelectedCellBackground(color) {
      let cl = color;
      if (color === null) {
        cl = '';
      }
      if (this.manageCell.data.tdIds.length > 0) {
        let table = this.manageCell.data.tableData;
        table.forEach(rows => {
          Object.values(rows.rows).forEach(column => {
            if (this.manageCell.data.tdIds.includes(column.style.tdId)) {
              column.style.backgroundColor = cl
            }
          })
        })
      }
    }
  },
  computed: {
    headerBackground: {
      get() {
        return this.manageCell.data.setting.background.options.header_background.value;
      },
      set(newValue) {
        let backgroundColor = this.manageCell.data.setting.background;
        newValue == '' && backgroundColor ? backgroundColor.options.header_background.value = '#fff' : backgroundColor.options.header_background.value = newValue;
      }
    },
    cellBackground: {
      get() {
        if (this.manageCell.data.table.column.style) {
          return this.manageCell.data.table.column.style.backgroundColor;
        }
      },
      set(newValue) {
        this.manageCell.data.table.column.style.backgroundColor = newValue;
      }
    },
    trBackground: {
      get() {
        if (this.manageCell.data.table.row.style) {
          return this.manageCell.data.table.row.style.backgroundColor;
        }
      },
      set(newValue) {
        this.manageCell.data.table.row.style.backgroundColor = newValue;
      }
    }
  }
}
</script>

<style scoped>
.color-picker-margin {
  margin-right: 10px;
}
</style>
