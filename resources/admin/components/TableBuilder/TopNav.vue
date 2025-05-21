<template>
    <el-row align="middle" justify="space-between" class="top-nav-row">
      <el-col :span="6">
        <div>
          <NinjaInput v-if="initialData.table_data" class="plugin-name" :placeholder="$t('Enter table name here....')"
                    v-model="initialData.table_data.table_name"/>
        </div>
      </el-col>
      <el-col :span="14">
        <div class="flex justify-end items-center gap-2">
        <div class="copy_shortcode">
            <el-tooltip effect="dark" content="Click to copy shortcode" title="Click to copy shortcode"
                placement="top">
                <code class="copy flex p-[8px] rounded-[8px] border border-[#E1E4EA]"
                     :data-clipboard-text='`[ninja_table_builder id="${id}"]`'>
                    <img :src="assetUrl('icons/copy-02.svg')" class="mr-2" alt="copy" />
                    [ninja_table_builder id="{{ id }}"]
                </code>
            </el-tooltip>
        </div>
        <a :href="previewURL" v-if="idExist" target="_blank">
            <NinjaButton type="secondary" :icon="assetUrl('icons/view.svg')" :btnText="$t('Preview')" />
        </a>
        <NinjaButton @click="saveTableData" type="primary">{{ $t('Save Table') }}</NinjaButton>
        <NinjaButton
            type="info"
            @click="fullScreenEnableDisable"
            :icon="assetUrl('icons/full-screen.svg')"
          ></NinjaButton>
        </div>
      </el-col>
    </el-row>
</template>
<script>
import {useEventBus} from '../../../admin/eventBus';
import NinjaButton from '../../@ui-utils/NinjaButton.vue';
import NinjaInput from '../../@ui-utils/NinjaInput.vue';
import { assetUrl } from '../../utils/ninjatablesadmin';
export default {
  name: "TopNav",
  props: ["initialData", "tableId", "selectedDevice"],
  components: {
    NinjaInput,
    NinjaButton
  },
  data() {
    return {
      bus : useEventBus(),
      id: '',
    };
  },
  methods: {
    assetUrl,
    saveTableData() {
      this.bus.emit('closeManageCell');
      this.bus.emit('saveData');

      if (this.selectedDevice !== '') {
        this.$emit('deviceSelected', '');
      }

      this.$nextTick(() => {
        const tableHtml = document.getElementById("ninja_tables_builder_id");
        const innerHTML = tableHtml.innerHTML.replace(/<!--[\s\S]*?-->/g, '')
            .replace(/ contenteditable="[^"]*"/g, '')
            .replace(/ data-gramm="[^"]*"/g, '')
            .replace(/<div[^>]*class="[^"]*remove-elements[^"]*"[^>]*>[\s\S]*?<\/div>/g, '');

        if (this.initialData.table_data.table_name === '') {
          this.$message({
            showClose: true,
            message: this.$t('Table name field is required'),
            type: 'error'
          });
          return false;
        }
        this.$patch(`table-builder/${this.id}`, {
          data: JSON.stringify(this.initialData),
          table_html: innerHTML,
          table_id: this.id
        })
            .then(response => {
              this.$message({
                showClose: true,
                message: this.$t('Table data saved successfully.'),
                type: 'success'
              });
              this.id = response.data.id
            })
            .catch(error => {
              this.$message({
                showClose: true,
                message: this.$t('Something went wrong, please try again.'),
                type: 'warning'
              });
            })
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
      return this.initialData.table_data && this.initialData.table_data.preview_url;
    }
  }
};
</script>

<style scoped>
.top-nav-row {
  width: 100%;
  min-height: 62px;
  display: flex;
  align-items: center;
}
.text-right {
  text-align: right;
}
</style>
