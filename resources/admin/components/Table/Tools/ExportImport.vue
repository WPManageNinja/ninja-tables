<template>
    <div class="nt-export-import-wrapper">
        <div>
            <div class="flex justify-between rounded-[8px] bg-[#F5F7FA] w-[280px] px-2 py-2 gap-3">
                <div @click="active_menu ='export'"
                     :class="`${active_menu ==='export' && 'bg-white rounded-[8px] shadow shadow-md shadow-gray-300'} px-5 py-1 cursor-pointer`">
                    {{ $t('Export Table') }}
                </div>
                <div @click="active_menu ='import'"
                     :class="`${active_menu ==='import' && 'bg-white rounded-[8px] shadow shadow-md shadow-gray-300'} px-5 py-1 cursor-pointer`">
                    {{ $t('Import Table') }}
                </div>
            </div>
        </div>

        <div v-if="active_menu === 'export'" class="export-table-panel">
            <ExportTable :config="config" :tableId="tableId"/>
        </div>
        <div v-if="active_menu === 'import'" class="import-table-panel">
            <ImportTable :config="config" :tableId="tableId"/>
        </div>
    </div>
</template>

<script>
import ExportTable from './_ExportTable.vue';
import ImportTable from './Import';

export default {
    name: "ExportImport",
    components: {
        ExportTable, ImportTable
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
