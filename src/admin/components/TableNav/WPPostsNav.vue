<template>
    <div class="fluent-form-nav">
        <el-collapse v-model="activeNames">
            <el-collapse-item title="Table Query Settings and New Column" name="1">
                <el-tabs type="border-card">
                    <el-tab-pane label="Table Info">
                       <p>This table data is created from wp-posts. Use the "Query Settings" and "Add New Column" tab to customize the columns and query.</p>
                    </el-tab-pane>
                    <el-tab-pane label="Query Settings">
                        <WPPosts
                            :hasPLainLayout="true"
                            :config="config"
                            :tableCreated="tableCreated"
                        />
                    </el-tab-pane>

                    <el-tab-pane label="Add New Column">
                        <columns-editor
                            :model="model"
                            :columns="config.columns"
                            :hasPro="hasPro"
                            :settings="config.settings"
                            :hideCancel="true"
                            dataSourceType="wp-posts"
                            @add="addNewColumn()"
                        />
                    </el-tab-pane>
                </el-tabs>
            </el-collapse-item>
        </el-collapse>
    </div>
</template>

<script>
    import WPPosts from '../DataProviders/WPPosts';
    import columnsEditor from '../Table/ColumnEditor/ColumnsEditor';

    export default {
        name: "WPPostsNav",
        components: { WPPosts, columnsEditor },
        props: {
            config: {
                type: Object
            },
            tableCreated: {
                type: Function
            },
            isEditableMessage: {
                required: true,
            },
            model: {
                type: Object,
            },
            hasPro: {
                type: Boolean
            },
        },
        data() {
            return {
                activeNames: ['1']
            }
        },
        methods: {
            addNewColumn() {
                this.$emit('add');
            }
        }
    }
</script>

<style lang="scss">
    .fluent-form-nav {
        .el-collapse-item__header,
        .el-collapse-item__wrap {
            padding: 0 15px;
        }

        .sync-settings {
            margin-top: 15px;
        }

        .el-collapse-item__content {
            padding-bottom: 15px;
        }
    }

    .el-transfer__buttons {
        padding: 0 10px !important;
    }
</style>
