<template>
    <div>
        <welcome v-if="!published_tables" @create="modalVisible = !modalVisible"/>

        <template v-else>
            <div class="row clearfix" style="margin-top: 10px">
                <h1 class="wp-heading-inline">
                    {{ $t('All Tables') }}
                </h1>

<!--                <ninja-review-dialog v-if="review_option" @hideNotification="review_option = false"/>-->

                <div style="margin-top:7px" class="pull-right">
                    <label class="form_group search_action" for="search">
                        <input v-on:keyup.enter="getData" v-model="searchString"
                               id="search" class="form-control inline"
                               placeholder="Search" type="text"
                        />

                        <i @click="getData" class="el-icon-search"></i>
                    </label>

                    <router-link :to="{ name: 'import_tables' }">
                        <el-button size="small">
                            {{ $t( 'Import Table' ) }}
                        </el-button>
                    </router-link>

                    <el-button @click="modalVisible = !modalVisible" size="small" type="primary">
                        {{ $t( 'Add Table' ) }}
                    </el-button>

                </div>
            </div>

            <hr/>

            <list-all-tables
                v-show="published_tables"
                @total_table="published_tables = true"
                :searchString="searchString"
                :searchAction="searchAction"
                @selection="makeSelection"/>
        </template>

        <!--Select your table type and create-->
        <el-dialog
            :close-on-click-modal="false"
            :title="$t('How would you like to create your table?')"
            :visible.sync="modalVisible"
            top="50px"
            width="75%"
            :append-to-body="true"
            custom-class="create-table-modal"
        >
            <add-table-modal @table_inserted="addTableAction"
                             @modal_close="modalVisible = false"
                             :hasPro="hasPro"
            />
        </el-dialog>
        <lead-modal></lead-modal>
    </div>
</template>

<script>
    import { onMounted } from 'vue';
    import { useEventBus } from '../composables/useEventBus';
    import Welcome from './Welcome';
    import ListAllTables from './_ListAllTables.vue';
    import AddTableModal from './_AddTable.vue';
    import leadModal from './Extras/lead';
    import NinjaReviewDialog from './Extras/_ReviewDialog';

    export default {
        name: 'all_tables',
        components: {
            Welcome,
            'list-all-tables': ListAllTables,
            'add-table-modal': AddTableModal,
            'lead-modal': leadModal,
            NinjaReviewDialog
        },
        props: ['hasPro'],
        setup() {
            const { on } = useEventBus();

            onMounted(() => {
                on('addedTable', () => {
                    if (!this.published_tables) {
                        window.ninja_table_admin.published_tables = 1;
                    }
                });
            });
        },
        data() {
            return {
                modalVisible: false,
                published_tables: parseInt(window.ninja_table_admin.published_tables),
                searchAction: 0,
                searchString: '',
                selected: [],
                review_option: window.ninja_table_admin.show_review_dialog
            };
        },
        methods: {
            addTableAction(tableId) {
                this.$router.push({name: 'data_items', params: {table_id: tableId}});
                this.modalVisible = false;
            },
            getData() {
                this.searchAction++;
            },
            makeSelection(ids) {
                this.selected = ids;
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
