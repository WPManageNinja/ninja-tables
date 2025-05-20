<template>
    <div class="px-32" style="margin-top: 20px">
      <!-- <h1 class="wp-heading-inline">
        {{ $t('Tools and Settings') }}
      </h1>
      <hr> -->
        <el-container class="ninja-table-aside">
            <el-aside width="300px">
                <el-menu :default-active="active_menu"
                         :router="true"
                         background-color="white"
                         text-color="#fff"
                         active-text-color="#ffd04b">
                    <template v-for="menuItem in menuItems" :key="menuItem.route">
                        <el-menu-item 
                            v-if="menuItem.status"
                            :index="menuItem.route" 
                            :route="{ name: menuItem.route }">
                            <el-icon><component :is="menuItem.icon_class" /></el-icon>
                            <span>{{ menuItem.title }}</span>
                        </el-menu-item>
                    </template>
                </el-menu>
            </el-aside>
            <el-main class="ml-10">
                <router-view></router-view>
            </el-main>
        </el-container>
    </div>
</template>

<script>
    export default {
        name: 'Tools',
        data() {
            return {
                has_pro: !!window.ninja_table_admin.hasPro,
                menuItems: []
            }
        },
        computed: {
            active_menu() {
                return this.$route.name;
            }
        },
        methods: {
            setUpMenuItems() {
                this.menuItems = this.applyFilters('ninja_table_settings_tools', [
                    {
                        route: 'import_tables',
                        title: this.$t('Import'),
                        icon_class: 'UploadFilled',
                        status: true
                    },
                    {
                        route: 'default_table_appearance',
                        title: this.$t('Global Appearance'),
                        icon_class: 'Star',
                        status: true
                    },
                    {
                        route: 'permission',
                        title: this.$t('Permission'),
                        icon_class: 'Setting',
                        status: true
                    },
                    {
                        route: 'licensing',
                        title: this.$t('License'),
                        icon_class: 'SetUp',
                        status: this.has_pro
                    },
                    {
                        route: 'global_settings',
                        title: this.$t('Global Settings'),
                        icon_class: 'Menu',
                        status: true
                    },
                ]);
            }
        },
        mounted() {
            this.setUpMenuItems();
        }
    }
</script>
