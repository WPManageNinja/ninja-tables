<template>
    <div class="fluent-form-nav">
        <el-collapse v-model="activeNames" class="nt_accordion_content_white">
            <el-collapse-item :title="$t('Table Query Settings and New Column')" name="1">
                <div class="nt-instruction my-5">
                    {{$t('This table data is created from wp-posts. Use the "Query Settings" and "Add New Column" tab to customize the columns and query.')}}
                </div>
                <div class="flex rounded-[8px] bg-[#F5F7FA] w-fit h-[36px] p-2 my-3 gap-3 items-center">
                    <div @click="activeTab = 'query_settings'" :class="`px-5 h-[26px] cursor-pointer ${activeTab==='query_settings' ? 'bg-white rounded-[8px] shadow-md shadow-gray-300 text-[#0E121B]': 'text-[#99A0AE]'}`">{{$t('Query Settings')}}</div>
                    <div @click="activeTab ='add_new_column'" :class="`px-5 h-[26px] cursor-pointer ${activeTab==='add_new_column' ? 'bg-white rounded-[8px] shadow-md shadow-gray-300 text-[#0E121B]':'text-[#99A0AE]'}`">{{$t('Add New Column')}}</div>
                </div>

                <div v-if="activeTab === 'query_settings'">
                    <WPPosts
                        :hasPLainLayout="true"
                        :config="config"
                        :tableCreated="tableCreated"
                    />
                </div>

                <div v-else>
                    <columns-editor
                        :model="model"
                        :columns="config.columns"
                        :hasPro="hasPro"
                        :settings="config.settings"
                        :hideCancel="true"
                        dataSourceType="wp-posts"
                        @add="addNewColumn()"
                    />
                </div>


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
                activeNames: '',
                activeTab: 'query_settings'
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
