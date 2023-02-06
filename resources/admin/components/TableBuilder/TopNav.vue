<template>
  <el-menu style="transform: translateY(-12px)">
    <el-row type="flex" align="middle">
      <el-col :span="6">
        <div>
          <el-input v-if="initialData.table_data" class="plugin-name" :placeholder="$t('Enter table name here....')"
                    v-model="initialData.table_data.table_name"></el-input>
        </div>
      </el-col>
      <el-col :span="18" style="text-align:right">
        <div>
          <el-button
              v-if="idExist"
              size="small"
              class="copy"
              type="info"
              :data-clipboard-text='`[ninja_table_builder id="${id}"]`'
              icon="el-icon-document-copy"
          >[ninja_table_builder id="{{ id }}"]
          </el-button>
          <a :href="previewURL" v-if="idExist" target="_blank">
            <el-button size="small">{{ $t('Preview') }}</el-button>
          </a>
          <el-button @click="saveTableData" size="small" type="primary">{{ $t('Save Table') }}</el-button>
          <el-button
              type="default"
              @click="fullScreenEnableDisable"
              size="small"
              icon="el-icon-full-screen"
              circle
          ></el-button>
        </div>
      </el-col>
    </el-row>
  </el-menu>
</template>
<script>
export default {
  name: "TopNav",
  props: ["initialData", "tableId", "selectedDevice"],
  data() {
    return {
      id: '',
    };
  },
  methods: {
    saveTableData() {
      window.ninjaTableBus.$emit('closeManageCell');
      window.ninjaTableBus.$emit('saveData');
      if (this.selectedDevice !== '') {
        this.$emit('deviceSelected', '');
      }
      this.$nextTick(() => {
        const tableHtml = document.getElementById("ninja_tables_builder_id");
        if (this.initialData.table_data.table_name === '') {
          this.$message({
            showClose: true,
            message: this.$t('Table name field is required'),
            type: 'error'
          });
          return false;
        }
        let data = {
          action: "ninja_tables_builder_ajax_actions",
          target_action: "update-table",
          data: JSON.stringify(this.initialData),
          table_html: tableHtml.innerHTML,
          table_id: this.id
        };
        this.$post(data)
            .then(response => {
              this.$message({
                showClose: true,
                message: this.$t('Table data saved successfully.'),
                type: 'success'
              });
              this.id = response.data.id
            })
            .fail(error => {
              this.$message({
                showClose: true,
                message: this.$t('Something went wrong, please try again.'),
                type: 'warning'
              });
            })
            .always(() => {

            });
      })
    },
    fullScreenEnableDisable() {
      const $body = jQuery("body");
      $body.toggleClass("folded");
    }
  },
  computed: {
    idExist() {
      if (this.tableId) {
        this.id = this.tableId
        return this.id;
      } else if (this.id) {
        return this.id;
      }
      return false
    },
    previewURL() {
      return window.location.origin + "/?ninjatable_builder_preview=" + this.id
    }
  }
};
</script>
