<template>
    <div class="wrap">
        <el-alert
            v-for="(notice, noticeKey) in admin_notices"
            :type="notice.type"
            :key="noticeKey"
            :closable="notice.closable"
            effect="dark">
            <div v-html="notice.message"></div>
        </el-alert>

        <router-view></router-view>
        <div class="bg-white">
            <el-dialog
                class="ninja_create-table-modal"
                :title="$t('Unlock the NinjaTable Pro Features')"
                v-model="addVisible"
                destroy-on-close
                width="650px"
                align-center
            >
                <div class="p-5">
                    <div class="grid grid-cols-2 gap-4 ">
                        <span class="flex items-center gap-2" v-for="feature in proFeatures" :key="feature">
                            <img class="w-5 h-5" :src="assetUrl('icons/check.svg')" alt="">
                            {{ $t(feature) }}
                        </span>
                    </div>

                    <div class="flex pt-5 mt-2">
                        <a v-if="!has_pro"
                           href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&amp;utm_medium=wp&amp;utm_campaign=wp_plugin&amp;utm_term=upgrade"
                           target="_blank"
                        class="w-full focus:outline-none focus:shadow-none">
                         <span class="flex items-center justify-center w-full gap-x-2  bg-[#0E121B] :hover:bg-[#222530] text-white px-4 py-2 rounded-[8px]">
                            <img :src="assetUrl('icons/get-pro.svg')" alt="">
                            <span class="ml-1">{{ $t('Get Pro') }}</span>
                         </span>
                        </a>
                    </div>
                </div>
            </el-dialog>
        </div>

        <div 
            v-if="integrity === 'nope'"
            style="margin-top: 90px;color: rgb(236, 62, 62);font-size: 16px;margin-bottom: 30px;">
            <b>Notice:</b> Ninja Tables Pro integrity is not valid. You may downloaded from other sources. 
            Please download ninja tables pro from 
            <a class="nt-link" href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?integrity=1">WPManageNinja</a>
            to keep it upto date.
        </div>
    </div>
</template>

<script>
import { onMounted, ref } from 'vue'
import GetPro from "./components/Tools/GetPro.vue"
import { useEventBus } from './eventBus'
import {assetUrl} from "./utils/ninjatablesadmin";

export default {
    name: 'TableApp',
    methods: {assetUrl},
    components: { GetPro },
    setup() {
        const addVisible = ref(false)
        const integrity = ref(window.ninja_table_admin.nt_integrity)
        const admin_notices = ref(window.ninja_table_admin.admin_notices)
        const bus = useEventBus();

        const proFeatures = ref([
            'Use Unlimited Colors in Your Tables',
            'Add Media to Your Table Cells',
            'Drag and Drop Table Data Sorting',
            'Use Advanced Data Sorting',
            'Colspan/Cell Merging Feature',
            'Create Custom Filter UI in Table',
            'Use Shortcode in your table cell',
            'Use Advanced Customization Features',
            'Use Advanced Data Filtering',
            'Get Priority Support for any Issue',
            'Incremental New Premium Features',
            'And Many More features'
        ]);

        onMounted(() => {
            bus.on('show_pro_popup', () => {
                addVisible.value = true
            })

            // Remove WordPress notices
            jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').remove()
        })

        return {
            addVisible,
            integrity,
            admin_notices,
            proFeatures
        }
    }
}
</script>

<style lang="scss">
.el-message {
    z-index: 999999 !important;
    top: 5px;
}

.pro_feature_dialog {
    .el-dialog__wrapper {
        z-index: 10000 !important;
    }
}

.list_features {
    list-style: disc;
    padding-left: 20px;
    
    li {
        margin-bottom: 8px;
    }
}
</style>
