<template>
    <div>
        <welcome v-if="!published_tables" @create="openCreateModal" />

        <template v-else>
            <div class="all-tables-wrapper">
                <div class="all-tables-header-left">
                    <h1 class="heading-text">
                        {{ $t('All Tables') }}
                    </h1>
<!--                    <p class="caption-text">-->
<!--                        {{ $t('Display all the tables and essential.') }}-->
<!--                    </p>-->
                </div>

                <div class="all-tables-header-right">
                    <router-link :to="{ name: 'import_tables' }">
                        <NinjaButton type="secondary" :icon="assetUrl('icons/download-02.svg')"
                            :btnText="$t('Import')" />
                    </router-link>

                    <NinjaButton @click="openCreateModal" :icon="assetUrl('icons/add.svg')"
                        :btnText="$t('Create New Table')" />

                </div>
            </div>

            <div class="all_tables_card">
                <div class="flex justify-between items-center px-4">
                    <p class="caption-text">Total Tables : {{ totalTables }}</p>
                    <div class="w-[300px]">
                        <NinjaInput v-model="searchString" placeholder="Search" prefix-icon="icons/search.svg"
                            @keyup.enter="getData" size="small" />
                    </div>

                </div>

                <ListAllTables v-show="published_tables" @total_table="handleTotalTables" :searchString="searchString"
                    :searchAction="searchAction" @selection="makeSelection" />
            </div>
        </template>

        <!--Select your table type and create-->
        <el-dialog id="data-tables-app" :close-on-click-modal="false"
            :title="$t('How would you like to create your table?')" v-model="modalVisible" top="50px" width="75%"
            :append-to-body="true" custom-class="create-table-modal" class="ninja_create-table-modal">
            <AddTableModal @table_inserted="addTableAction" @modal_close="modalVisible = false" :hasPro="hasPro" />
        </el-dialog>
        <leadModal></leadModal>
    </div>
</template>

<script>
import Welcome from './Welcome.vue';
import ListAllTables from './_ListAllTables.vue';
import AddTableModal from './_AddTable.vue';
import leadModal from './Extras/lead';
import NinjaReviewDialog from './Extras/_ReviewDialog';
import { useEventBus } from './../eventBus';
import NinjaButton from "../@ui-utils/NinjaButton.vue";
import { assetUrl } from "../utils/ninjatablesadmin";
import NinjaInput from "../@ui-utils/NinjaInput.vue";

export default {
    name: 'all_tables',
    components: {
        NinjaInput,
        NinjaButton,
        Welcome,
        ListAllTables,
        AddTableModal,
        leadModal,
        NinjaReviewDialog
    },
    props: ['hasPro'],
    data() {
        return {
            modalVisible: false,
            published_tables: window.ninja_table_admin.published_tables ? Boolean(parseInt(window.ninja_table_admin.published_tables)) : false,
            searchAction: 0,
            searchString: '',
            selected: [],
            review_option: window.ninja_table_admin.show_review_dialog,
            totalTables: 0,
        };
    },
    mounted() {
        const bus = useEventBus();

        bus.on('addedTable', () => {
            if (!this.published_tables) {
                window.ninja_table_admin.published_tables = 1;
                this.published_tables = true;
            }
        });
    },
    methods: {
        assetUrl,
        openCreateModal() {
            this.modalVisible = true;
        },
        addTableAction(tableId) {
            this.$router.push({ name: 'data_items', params: { table_id: tableId } });
            this.modalVisible = false;
        },
        getData() {
            this.searchAction++;
        },
        makeSelection(ids) {
            this.selected = ids;
        },
        handleTotalTables(total) {
            this.totalTables = total;
            this.published_tables = true
        },
        handleBulkActions(event) {
            if (event === 'delete') {
                this.deleteTables();
            }
        },
        deleteTables() {
            if (this.selected.length) {
                this.$confirm(
                    this.$t('This will permanently delete the selected tables. Continue?'),
                    'Warning',
                    {
                        confirmButtonText: this.$t('Yes, Delete'),
                        cancelButtonText: this.$t('Cancel'),
                        type: 'warning'
                    }
                ).then(() => {

                }).catch(() => {

                });
            }
        }
    }
};
</script>

<style lang="scss">
label.form_group.search_action {
    padding-top: 0;
    margin-bottom: 0;
}

.create-table-modal {
    z-index: 9999 !important;

    .el-dialog__body {
        padding: 20px;
    }
}
</style>
