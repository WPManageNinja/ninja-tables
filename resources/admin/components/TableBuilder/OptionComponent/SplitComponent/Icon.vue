<template>
  <div>
    <el-tooltip effect="dark" placement="top-end"
                content="If you choose svg you can customize the color otherwise can't">
      <NinjaButton size="small" type="secondary" @click.prevent="newIcon" round :icon="assetUrl('/icons/upload-02.svg')">
        {{ $t('Choose Icon') }}
      </NinjaButton>
    </el-tooltip>
    <el-input size="small" :placeholder="$t('Search Icon')" v-model="keyword" style="margin: 10px 0; width: 96%;"></el-input>
    <div class="flex flex-wrap" style="height: 100px;width: 100%;overflow-y: auto;">
        <span v-for="(icon, index) in iconList" :key="index">
            <img @click="chooseIcon(`${icon}`)" :src="getAsset(icon)" width="30px" height="30px">
        </span>
    </div>
  </div>
</template>

<script>
import NinjaButton from "../../../../@ui-utils/NinjaButton.vue";
import { assetUrl } from "../../../../utils/ninjatablesadmin";
import {icons} from "../../libs/icons";

export default {
  name: "Icon",
  props: ['item', 'setValue'],
  data() {
    return {
      icons: icons,
      keyword: ''
    };
  },
  components: { NinjaButton },
  methods: {
    assetUrl,
    newIcon() {
      const upload = wp
          .media({
            title: "Choose Icon", //Title for Media Box
            multiple: false, //For limiting multiple image
          })
          .on("select", () => {
            const select = upload.state().get("selection");
            const attach = select.first().toJSON()
            if (this.item.data.type === 'icon') {
              this.item.data.value = attach.url;
            } else {
              this.item.data.style[this.setValue] = attach.url;
            }
          })
          .open();
    },
    chooseIcon(newIcon) {
      if (this.item.data.type === 'icon') {
        this.item.data.value = newIcon;
      } else {
        this.item.data.style[this.setValue] = newIcon;
      }
    },
    getAsset(path) {
      return ninja_table_admin.ninja_tables_pro_url + '/assets/libs/icons/' + path + '.svg'
    },
  },
  computed: {
    iconList() {
      return this.icons.filter((icon) => {
        return this.keyword.toLowerCase().split(' ').every(v => icon.toLowerCase().includes(v));
      });
    }
  }
}
</script>
