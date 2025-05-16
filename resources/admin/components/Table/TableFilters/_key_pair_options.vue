<template>
    <el-table :data="value" border class="nt-inner-table">
        <el-table-column width="50">
            <template #header>
                <span></span>
            </template>
            <template #default>
                <img class="cursor-move handle-custom-filter" :src="assetUrl('icons/drag-drop.svg')"/>
            </template>
        </el-table-column>

        <el-table-column label="Label">
            <template #default="scope">
                <NinjaInput v-model="scope.row.label" :placeholder="$t('Enter Filter Label')"/>
            </template>
        </el-table-column>

        <el-table-column label="Filter Value">
            <template #default="scope">
                <NinjaInput v-model="scope.row.value" :placeholder="$t('Enter Filter Value')"/>
            </template>
        </el-table-column>

        <el-table-column width="100">
            <template #header>
                <span>{{ $t('Action') }}</span>
            </template>

            <template #default="scope">
                <div class="flex items-center">
                    <div v-show="value.length > 1" class="cursor-pointer" @click="deleteItem(scope.$index)">
                        <img :src="assetUrl('icons/delete-02.svg')"/>
                    </div>

                    <div v-show="(scope.$index + 1) == value.length" @click="add()"
                         class="mr-3 mt-[2px] cursor-pointer">
                        <img :src="assetUrl('icons/edit-2.svg')"/>
                    </div>
                </div>

            </template>
        </el-table-column>
    </el-table>
</template>

<script type="text/babel">
    import draggable from 'vuedraggable'
    import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
    import {assetUrl} from "../../../utils/ninjatablesadmin";
    export default {
        name: 'ninja_key_pair_options',
        components: {NinjaInput, draggable },
        props: ['value'],
        data() {
            return {
                filterArray: []
            }
        },
        methods: {
            assetUrl,
            deleteItem(index) {
                this.value.splice(index, 1);
            },
            add() {
                this.value.push({
                    label: '',
                    value: ''
                });
            }
        },
    }
</script>

<style lang="scss">
    table.ninja_filter_table {
        width: 100%;
        text-align: left;
        border-collapse: collapse;
        tr, td, th {
            border: 1px solid #eaeaea;
            padding: 2px 10px;
        }
    }
</style>
