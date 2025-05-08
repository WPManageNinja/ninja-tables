<template>
    <div style="margin-top: 15px;">
        <el-container>
            <el-aside width="200px">
                <el-menu background-color="#545c64"
                         :default-active="active_menu"
                         text-color="#fff"
                         active-text-color="#ffd04b">
                    <el-menu-item  @click="active_menu = 'import'" index="import">
                        <el-icon><UploadFilled /></el-icon>
                        <span>{{$t('Import Data')}}</span>
                    </el-menu-item>
                    <el-menu-item @click="active_menu = 'export'" index="export">
                        <el-icon><Download /></el-icon>
                        <span>{{ $t('Export Data') }}</span>
                    </el-menu-item>
                </el-menu>
            </el-aside>
            <el-main>
                <component :is="active_menu" :config="config" :tableId="tableId"></component>
            </el-main>
        </el-container>
    </div>
</template>

<script>
    import ExportTable from './_ExportTable.vue';
    import ImportTable from './Import';
    import {Download, UploadFilled} from '@element-plus/icons-vue';

    export default {
        name: "ExportImport",
        components: {
            'export': ExportTable,
            'import' : ImportTable,
            Download,
            UploadFilled
        },
        props: ['config'],
        data() {
            return {
                active_menu: 'import',
                tableId: this.$route.params.table_id,
                activeNames: ['1']
            }
        }
    }
</script>
