<template>
    <div class="external-source-nav">
        <el-collapse>
            <el-collapse-item name="1">
                <template slot="title">
                    <i class="header-icon el-icon-info el-text-info"></i>
                    <strong>Edit:</strong> {{isEditableMessage}}
                </template>

                <external-data-source
                    :type="config.table.dataSourceType"
                    :tableCreated="tableCreated"
                    :columns="config.columns"
                    :table="config.table"
                    :hasPro="hasPro"
                    :editing="true"
                    :activated_features="activated_features"
                />
            </el-collapse-item>
        </el-collapse>
    </div>
</template>

<script>
    import ExternalDataSource from '../DataProviders/ExternalDataSource';

    export default {
        name: "External",
        components: {
            'external-data-source': ExternalDataSource,
        },
        props: {
            tableCreated: {
                type: Function
            },
            config: {
                type: Object
            },
            isEditableMessage: {
                required: true,
            },
            hasPro: {
                required: true
            }
        },
        data() {
            return {
                state: false,
                url: this.value,
                activated_features: window.ninja_table_admin.activated_features,
            }
        }
    }
</script>

<style lang="scss">
    .external-source-nav {
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
</style>
