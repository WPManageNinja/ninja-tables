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
                title="NinjaTable Pro Features"
                v-model="addVisible"
                destroy-on-close
            >
                <div class="p-5">
                    <ul class="list_features">
                        <li>Use Unlimited Colors in Your Tables</li>
                        <li>Add Media to Your Table Cells</li>
                        <li>Drag and Drop Table Data Sorting</li>
                        <li>Use Advanced Date Sorting</li>
                        <li>Colspan/Cell Merging Feature</li>
                        <li>Create Custom Filter UI in Table</li>
                        <li>Use Shortcode in your table cell</li>
                        <li>Use Advanced Data Filtering</li>
                        <li>Use Advanced Customization Features</li>
                        <li>Get VIP Support for any Issue</li>
                        <li>Incremental New Premium Features</li>
                        <li>And Many More feature</li>
                    </ul>
                </div>
                <div class="flex justify-end p-4">
                    <get-pro/>
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

export default {
    name: 'TableApp',
    components: { GetPro },
    setup() {
        const addVisible = ref(false)
        const integrity = ref(window.ninja_table_admin.nt_integrity)
        const admin_notices = ref(window.ninja_table_admin.admin_notices)
        const bus = useEventBus();

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
            admin_notices
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
