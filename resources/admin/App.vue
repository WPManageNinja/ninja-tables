<template>
    <div class="wrap">
        <el-alert
            v-for="(notice,noticeKey) in admin_notices"
            :type="notice.type"
            :key="noticeKey"
            :closable="notice.closable"
            effect="dark">
            <div v-html="notice.message"></div>
        </el-alert>

        <router-view></router-view>
        <div class="pro_feature_dialog">
            <el-dialog
                    title="NinjaTable Pro Features"
                    :visible.sync="addVisible">
                <div class="add_content">
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
                <span slot="footer" class="dialog-footer">
                    <get-pro/>
                </span>
            </el-dialog>
        </div>

        <div style="margin-top: 90px;color: rgb(236, 62, 62);font-size: 16px;margin-bottom: 30px;" v-if="integrity == 'nope'">
            <b>Notice:</b> Ninja Tables Pro integrity is not valid. You may downloaded from other sources. Please download ninja tables pro from <a href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?integrity=1">WPManageNinja</a> to keep it upto date.
        </div>
    </div>
</template>

<script type="text/babel">
    import GetPro from "./components/Tools/GetPro";
    export default {
        name: 'TableApp',
      components: {GetPro},
      data() {
            return {
                addVisible: false,
                integrity: window.ninja_table_admin.nt_integrity,
                admin_notices: window.ninja_table_admin.admin_notices
            }
        },
        mounted() {
            window.ninjaTableBus.$on('show_pro_popup', (val) => {
                this.addVisible = true;
            });

            jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').remove();

        },
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
</style>
