<template>
    <div style="margin-top: 15px;">
        <el-container>
            <el-aside width="200px">
                <el-menu background-color="#545c64"
                         :default-active="active_menu"
                         text-color="#fff"
                         active-text-color="#ffd04b">
                    <el-menu-item  @click="active_menu = 'import'" index="import">
                        <i class="el-icon-upload"></i>
                        <span>{{$t('Import Data')}}</span>
                    </el-menu-item>
                    <el-menu-item @click="active_menu = 'export'" index="export">
                        <i class="el-icon-download"></i>
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

    export default {
        name: "ExportImport",
        components: {
            'export': ExportTable,
            'import' : ImportTable
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
