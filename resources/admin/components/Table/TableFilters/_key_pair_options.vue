<template>
    <el-table
        :data="value"
        border
        class="nt-inner-table"
        row-class-name="handle-custom-filter"
        :row-key="getRowKey"
    >
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
                <div class="flex items-center gap-1">
                    <div v-show="value.length > 1" class="cursor-pointer" @click="deleteItem(scope.$index)">
                        <img :src="assetUrl('icons/delete-02.svg')"/>
                    </div>

                    <div v-show="(scope.$index + 1) == value.length" @click="add()"
                         class="mr-3 mt-[2px] cursor-pointer">
                        <img :src="assetUrl('icons/add-01.svg')"/>
                    </div>
                </div>
            </template>
        </el-table-column>
    </el-table>
</template>

<script>
import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
import { assetUrl } from "../../../utils/ninjatablesadmin";
import Sortable from 'sortablejs';

export default {
    name: 'ninja_key_pair_options',
    components: { NinjaInput },
    props: ['value'],
    data() {
        return {
            sortableInstance: null,
            rowKeys: [],
            nextKey: 1
        }
    },
    methods: {
        assetUrl,
        getRowKey(row) {
            const index = this.value.indexOf(row);

            if (!this.rowKeys[index]) {
                this.rowKeys[index] = `key-${this.nextKey++}`;
            }
            return this.rowKeys[index];
        },
        deleteItem(index) {
            this.value.splice(index, 1);
            this.rowKeys.splice(index, 1);
            this.$nextTick(() => {
                this.initSortable();
            });
        },
        add() {
            this.value.push({
                label: '',
                value: ''
            });
            this.rowKeys.push(`key-${this.nextKey++}`); // Add a new key
            this.$nextTick(() => {
                this.initSortable();
            });
        },
        initSortable() {
            if (this.sortableInstance) {
                this.sortableInstance.destroy();
                this.sortableInstance = null;
            }

            const tableBody = this.$el.querySelector('.nt-inner-table tbody');
            if (!tableBody) {
                console.warn('Table body not found for sortable initialization');
                return;
            }

            this.sortableInstance = Sortable.create(tableBody, {
                handle: '.handle-custom-filter',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: ({ newIndex, oldIndex }) => {
                    if (newIndex === oldIndex) return;
                    // Update value array
                    const movedItem = this.value.splice(oldIndex, 1)[0];
                    this.value.splice(newIndex, 0, movedItem);
                    // Update rowKeys array to keep keys in sync
                    const movedKey = this.rowKeys.splice(oldIndex, 1)[0];
                    this.rowKeys.splice(newIndex, 0, movedKey);
                }
            });
        }
    },
    mounted() {
        this.rowKeys = this.value.map(() => `key-${this.nextKey++}`);
        this.$nextTick(() => {
            this.initSortable();
        });
    },
    beforeUnmount() {
        if (this.sortableInstance) {
            this.sortableInstance.destroy();
            this.sortableInstance = null;
        }
    }
}
</script>
