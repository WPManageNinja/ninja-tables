<template>
    <div style="margin-top: 10px">
      <h1 class="wp-heading-inline">
        {{ $t('Tools and Settings') }}
      </h1>
      <hr>
        <el-container>
            <el-aside width="200px">
                <el-menu :default-active="active_menu"
                         :router="true"
                         background-color="#545c64"
                         text-color="#fff"
                         active-text-color="#ffd04b">
                    <template v-for="menuItem in menuItems" :key="menuItem.route">
                        <el-menu-item 
                            v-if="menuItem.status"
                            :index="menuItem.route" 
                            :route="{ name: menuItem.route }">
                            <i :class="menuItem.icon_class"></i>
                            <span>{{ menuItem.title }}</span>
                        </el-menu-item>
                    </template>
                </el-menu>
            </el-aside>
            <el-main>
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
                        icon_class: 'el-icon-upload',
                        status: true
                    },
                    {
                        route: 'default_table_appearance',
                        title: this.$t('Global Appearance'),
                        icon_class: 'el-icon-star-off',
                        status: true
                    },
                    {
                        route: 'permission',
                        title: this.$t('Permission'),
                        icon_class: 'el-icon-setting',
                        status: true
                    },
                    {
                        route: 'licensing',
                        title: this.$t('License'),
                        icon_class: 'ninja-tables-dashicons dashicons dashicons-shield',
                        status: this.has_pro
                    },
                    {
                        route: 'global_settings',
                        title: this.$t('Global Settings'),
                        icon_class: 'el-icon-menu',
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
