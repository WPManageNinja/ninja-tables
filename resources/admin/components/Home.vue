<template>
  <div>
    <div class="ninja_main_nav">
           <span class="plugin-name">
                <img style="height: 36px;" :src="imageUrl('ninja_table.svg')" alt="ninja-tables">
                <span class="pro" v-if="has_pro">{{ $t('Pro') }}</span>
          </span>
      <el-menu
          style="float: right"
          :default-active="activeMenu"
          :router="true"
          mode="horizontal"
      >
        <el-menu-item
            v-for="menuItem in topMenus"
            :key="menuItem.route"
            :index="menuItem.route"
            :route="{ name: menuItem.route }"
        >
          <i :class="menuItem.icon_class"/>
          <span>
                        {{ menuItem.title }}
                    </span>
        </el-menu-item>

        <el-menu-item index="buy-pro" v-if="!has_pro">
          <get-pro size="small"/>
        </el-menu-item>
      </el-menu>
    </div>

    <router-view :has-pro="has_pro"></router-view>
  </div>
</template>

<script type="text/babel">
    import GetPro from "./Tools/GetPro";
    export default {
        name: 'home',
      components: {GetPro},
      data() {
            return {
                has_pro: window.ninja_table_admin.hasPro,
                topMenus: [],
                img_url: window.ninja_table_admin.img_url
            }
        },
        computed: {
            activeMenu() {
                return ['home', 'help'].indexOf(this.$route.name) != -1 ? this.$route.name : 'import_tables'
            }
        },
        methods: {
            setTopMenu() {
                this.topMenus = this.applyFilters('ninja_table_top_menus', [
                    {
                        route: 'home',
                        title: 'All Tables'
                    },
                    {
                        route: 'import_tables',
                        title: 'Tools and Settings'
                    },
                    {
                        route: 'help',
                        title: 'Help & Documentation'
                    }
                ]);
            },
          imageUrl(imageName) {
            return this.img_url+imageName;
          }
        },
        mounted() {
            this.setTopMenu();
        }
    }
</script>

<style>
    .el-menu.el-menu--horizontal {
        border-bottom: none;
    }
</style>
