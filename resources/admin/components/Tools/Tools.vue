<template>
    <div style="margin-top: 10px">
      <h1 class="wp-heading-inline">
        {{ $t('Tools and Settings') }}
      </h1>
      <hr>
        <el-container>
            <el-aside width="200px">
                <el-menu background-color="#545c64"
                         :default-active="active_menu"
                         text-color="#fff"
                         :router="true"
                         active-text-color="#ffd04b">
                    <el-menu-item v-for="menuItem in menuItems" v-if="menuItem.status" :key="menuItem.route" :index="menuItem.route" :route="{ name: menuItem.route }">
                        <i :class="menuItem.icon_class"></i>
                        <span>{{menuItem.title}}</span>
                    </el-menu-item>
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
