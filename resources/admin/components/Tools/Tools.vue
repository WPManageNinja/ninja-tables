<template>
    <div class="px-5 lg:px-32" style="margin-top: 20px">
      <!-- <h1 class="wp-heading-inline">
        {{ $t('Tools and Settings') }}
      </h1>
      <hr> -->
        <el-container class="ninja-table-aside">
            <el-aside class="!w-[80px] lg:!w-[300px] !p-[5px]">
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
                            <el-tooltip :content="menuItem.title" placement="right">
                                <img :src="assetUrl(menuItem.iconSrc)" class="w-[20px] h-[20px] lg:hidden">
                            </el-tooltip>
                            <!-- <el-icon><component class="hidden lg:block" :is="menuItem.icon_class" /></el-icon> -->
                            <img :src="assetUrl(menuItem.iconSrc)" class="w-[20px] h-[20px] hidden lg:block">
                            <span class="hidden lg:block">
                                {{ menuItem.title }}
                            </span>
                            <img v-if="!has_pro && menuItem.route === 'permission'" class="h-4 w-4 ml-1 !grayscale-0" :src="assetUrl('icons/get-pro.svg')" alt="">
                        </el-menu-item>
                    </template>
                </el-menu>
            </el-aside>
            <el-main class="ml-5 lg:ml-10">
                <router-view></router-view>
            </el-main>
        </el-container>
    </div>
</template>

<script>
import { assetUrl } from '../../utils/ninjatablesadmin';

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
            assetUrl,
            setUpMenuItems() {
                this.menuItems = this.applyFilters('ninja_table_settings_tools', [
                    {
                        route: 'import_tables',
                        title: this.$t('Import'),
                        iconSrc: '/icons/import.svg',
                        status: true
                    },
                    {
                        route: 'default_table_appearance',
                        title: this.$t('Global Appearance'),
                        iconSrc: '/icons/global-appear.svg',
                        status: true
                    },
                    {
                        route: 'permission',
                        title: this.$t('Permission'),
                        iconSrc: '/icons/permission_user.svg',
                        status: true
                    },
                    {
                        route: 'licensing',
                        title: this.$t('License'),
                        iconSrc: '/icons/license.svg',
                        status: this.has_pro
                    },
                    {
                        route: 'global_settings',
                        title: this.$t('Global Settings'),
                        iconSrc: '/icons/global_setting.svg',
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
