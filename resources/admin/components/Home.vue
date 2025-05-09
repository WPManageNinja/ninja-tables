<template>
  <div>
    <div class="ninja_main_nav">
       <div class="plugin-name">
            <div class="logo">
                <img style="height: 36px;" :src="imageUrl('ninja_table.svg')" alt="ninja-tables">
            </div>
            <div class="pro-text" v-if="has_pro">
                <span class="pro">{{ $t('Pro') }}</span>
            </div>
      </div>

      <div class="ninja_main_nav_right">
          <el-menu
              style="float: right"
              :default-active="activeMenu"
              :router="true"
              mode="horizontal"
              :ellipsis="false"
          >
              <el-menu-item
                  v-for="menuItem in topMenus"
                  :key="menuItem.route"
                  :index="menuItem.route"
                  :route="{ name: menuItem.route }"
              >
                 {{ menuItem.title }}
              </el-menu-item>

              <el-menu-item  class="get_pro" index="buy-pro" v-if="!has_pro">
                  <get-pro size="small"/>
              </el-menu-item>
          </el-menu>
      </div>
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
                return ['home', 'import_tables', 'help'].indexOf(this.$route.name) != -1 ? this.$route.name : 'home'
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
