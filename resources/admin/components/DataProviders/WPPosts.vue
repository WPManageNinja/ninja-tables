<template>
    <div v-loading="loading" class="ninja_modal-body ninja_tables_wpposts">
        <template v-if="!hasPro">
            <PremiumNotice title="WP Posts Table">
            <template #default>
                <p class="text-[14px] font-[400] text-[#525866]">This is a Premium feature. Create responsive and customizable <a class="nt-link" target="_blank" href="https://ninjatables.com/docs/create-wp-posts-table/">WP posts tables</a> from your existing posts/pages/custom post types with Ninja Tables pro.</p>
            </template>
            </PremiumNotice>
        </template>
        <template v-else-if="!activated_features.wp_posts_table">
            <upgrade-notice/>
        </template>
        <div v-else>

            <div v-if="!hasPLainLayout">
                <h3 class="nt-modal-title">
                   {{ $t('Construct Table from Posts / CPTs') }}
                </h3>
                <p class="nt-modal-description">
                    {{ $t('This is a Premium feature. Create responsive and customizable WP posts tables from your existing posts/pages/custom post types with Ninja Tables pro. Enjoy unlimited customizations, data filters, professional design and more.') }}
                    <a class="nt-link" target="_blank" href="https://ninjatables.com/docs/create-wp-posts-table/">
                       {{ $t('Learn more about this module') }}
                    </a>
                    {{ $t('or') }}
                    <a class="nt-link" target="_blank" href="https://youtu.be/icQs-GwuG28?si=Q1k3ZmpM3_YiJ3rU/">
                        {{ $t('Watch Tutorial Here') }}
                    </a>
                </p>

                <div class="my-[30px]">
                    <div class="nt-form-group">
                        <el-row>
                            <label class="nt-form-label">{{ $t('Table Title') }} <span class="text-[#FB3748]">*</span></label>
                            <NinjaInput v-model="title" placeholder="Title"/>
                        </el-row>
                    </div>

                    <div class=" bg-[#F9FAFB] p-3 mt-5 rounded-[8px] border border-solid border-[#E1E4EA]">
                        <div class="mb-4">
                            <el-transfer
                                filterable
                                class="my-3 flex justify-between items-center"
                                :data="post_types"
                                :titles="['All Types', 'Selected Types']"
                                v-model="selected_post_types"
                                @change="handlePostTypeChange"
                            >
                            </el-transfer>
                        </div>
                        <div>
                            <el-transfer
                                filterable
                                class="flex justify-between items-center"
                                :data="post_types_fields"
                                :titles="['All Properties', 'Selected Properties']"
                                v-model="selected_post_types_fields"
                            />
                        </div>
                    </div>

                    <div class="my-[20px]">
                            <el-collapse accordion class="ninja-tables_rendering_accordion" value="conditions" v-model="conditions_section">
                                <el-collapse-item name="conditions" title="Conditions">
                                    <el-checkbox v-model="currentUserPosts" class="mt-2">{{ $t('Logged in user posts') }}</el-checkbox>

                                    <WPPostConditions
                                        v-if="conditions_section"
                                        :postStatuses="postStatuses"
                                        :selected_post_types="selected_post_types"
                                        :conditions="conditions"
                                        :allPostTypes="all_types"
                                        :fields="query_able_post_types_fields"/>
                                </el-collapse-item>
                            </el-collapse>
                    </div>
                </div>

                <div class="nt-modal-footer">
                    <NinjaButton
                        type="secondary"
                        :btnText="$t('Cancel')"
                        @click="$emit('modalClose')"
                    />

                    <NinjaButton
                        :btnText="$t('Save')"
                        @click="save"
                        :loading="saving"
                        :disabled="!activated_features.wp_posts_table"
                    />
                </div>
            </div>

            <div v-if="hasPLainLayout">
                <div class="w-full flex flex-wrap gap-4 justify-center ">
                    <div class="text-center">
                        <el-transfer
                            filterable
                            :data="post_types"
                            v-model="selected_post_types"
                            :titles="['All Types', 'Selected Types']"
                            @change="handlePostTypeChange">
                        </el-transfer>
                    </div>

                    <div class="text-center">
                        <el-transfer
                            filterable
                            :data="post_types_fields"
                            v-model="selected_post_types_fields"
                            :titles="['All Properties', 'Selected Properties']"
                            >
                        </el-transfer>
                    </div>
                </div>

                <div v-if="!loading" class="my-5">
                    <el-collapse class="nt-post-edit-accordion" v-model="conditions_section">
                        <el-collapse-item name="1">
                          <el-checkbox v-model="currentUserPosts" class="mt-5">{{$t('Logged in user posts')}}</el-checkbox>
                            <template #title>
                                <h4 class="no-margin">{{$t('Conditions')}}</h4>
                            </template>
                            <WPPostConditions
                                :config="config"
                                :selected_post_types="selected_post_types"
                                :postStatuses="postStatuses"
                                :conditions="conditions"
                                :allPostTypes="all_types"
                                :fields="query_able_post_types_fields"/>
                        </el-collapse-item>
                    </el-collapse>

                    <el-collapse class="nt-post-edit-accordion mt-4" v-model="meta_query">
                        <el-collapse-item name="1">
                            <template #title>
                                <h4 class="no-margin">{{$t('Meta Query')}}</h4>
                            </template>

                            <WpPostMetaQuery
                                :config="config"
                                :metas="metas"
                                :fields="query_able_post_types_fields"
                            />
                        </el-collapse-item>
                    </el-collapse>
                </div>

                <div class="flex justify-end">
                    <NinjaButton
                        :loading="saving"
                        @click="save"
                        :btn-text="$t('Update')"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import WPPostConditions from './WPPostConditions';
    import WpPostMetaQuery from './WPPostMetaQuery';
    import PremiumNotice from '../includes/PremiumNotice';
    import UpgradeNotice from '../includes/UpgradeNotice';
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'WP-Posts',
        props: {
            config: {
                type: Object
            },
            tableCreated: {
                type: Function
            },
            hasPLainLayout: {
                type: Boolean,
                default: false
            }
        },
        components: {
            NinjaButton,
            NinjaInput,
            WPPostConditions,
            PremiumNotice,
            UpgradeNotice,
            WpPostMetaQuery
        },
        data() {
            return {
                loading: false,
                saving: false,
                title: null,
                tableId: null,
                postStatuses: [],
                all_types: [],
                all_fields: [],
                post_types: [],
                selected_post_types: [],
                post_types_fields: [],
                selected_post_types_fields: [],
                conditions_section: 'conditions',
                conditions: [],
                meta_query: 'meta',
                metas: [],
                active_step: 0,
                query_extra: {},
                activated_features: window.ninja_table_admin.activated_features,
                hasPro: !!window.ninja_table_admin.hasPro,
                queryable_fields: [
                    'ID',
                    'post_author',
                    'comment_count',
                    'post_date',
                    'post_modified',
                    'post_status',
                ],
                currentUserPosts: false
            };
        },
        computed: {
            query_able_post_types_fields() {
                return this.post_types_fields.filter(field => {
                    return (
                        this.queryable_fields.indexOf(field.key) != -1 || field.key.indexOf('.') != -1
                    );
                });
            }
        },
        methods: {
            nextStep() {
                let message = '';
                if (!this.title) {
                    message += ' The title field is required.';
                }
                if (!this.selected_post_types.length) {
                    message += ' At least select one post type.';
                }

                if ((message = jQuery.trim(message))) {
                    this.active_step = 0;
                    this.$message({showClose: true, message: message, type: 'error'});
                    return;
                }

                if (this.active_step++ >= 1) {
                    this.active_step = 0;
                }
            },
            handlePostTypeChange(value, direction, movedKeys) {
                let selectedPostFields = [];
                this.selected_post_types.forEach(type => {
                    if (this.all_types[type]) {
                        this.all_types[type].fields.forEach(field => {
                          selectedPostFields.push({key: field, label: field});
                        });
                    }
                });

                this.post_types_fields = this.all_fields.concat(selectedPostFields);

                this._updateSelectedFields();
            },
            _updateSelectedFields() {
                if (!this.selected_post_types.length) {
                    this.post_types_fields = [];
                    this.selected_post_types_fields = [];
                    return;
                }

                this.selected_post_types_fields.filter(f => !!f).forEach((field, i) => {
                    let parts = field.split('.');
                    if (parts.length > 1 && this.selected_post_types.indexOf(parts[0]) == -1) {
                        this.selected_post_types_fields.splice(i, 1);
                    }
                });
            },
            save() {
                this.saving = true;
                const data = {
                    post_types: this.selected_post_types,
                    columns: this.selected_post_types_fields,
                    where: this.conditions.map(condition => Object.assign({}, condition)),
                    metas: this.metas.map(meta => Object.assign({}, meta)),
                    query_extra: (this.config && this.config.table) ? this.config.table.query_extra : false,
                    current_user_posts: this.currentUserPosts
                }

                data.where.forEach(condition => {
                    delete condition['selectableOptions'];
                })

                this.$post({
                    action: 'ninja_table_wp-posts_create_table',
                    post_title: this.title,
                    tableId: this.tableId,
                    data
                })
                    .then(res => {
                        this.$message({
                            showClose: true,
                            message: res.data.message,
                            type: 'success'
                        });
                        this.tableCreated(res.data.table_id);
                    })
                    .fail(error => {
                        let message = '';
                        let messages = error.responseJSON.data.message;
                        for (let key in messages) {
                            message += ' ' + messages[key];
                        }
                        this.$message({showClose: true, message: message, type: 'error'});
                    })
                    .always(res => this.saving = false);
            },
            getPostTypes() {
                this.loading = true;

                this.$get('wp-posts')
                    .then(res => {
                        this.all_types = res.data.post_types;
                        this.postStatuses = res.data.postStatuses;

                        jQuery.each(this.all_types, (type, post_type) => {
                            let status = '';
                            if (post_type.status === 'private') {
                                status = ' (private)';
                            }
                            this.post_types.push({key: type, label: type + status});
                        });

                        this.all_fields = res.data.post_fields.map(field => {
                            return {key: field, label: field};
                        });

                        // For editing
                        if (this.config) {
                            this.tableId = this.config.table.ID;
                            this.conditions = this.config.table.whereConditions || [];
                            this.metas = this.config.table.metaQuery || [];
                            this.selected_post_types = this.config.table.post_types;
                            this.selected_post_types_fields = this.config.columns.map(c => c.original_name);
                            this.currentUserPosts = this.config.table.query_extra.current_user_posts === 'true';
                            this.handlePostTypeChange();
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    })
               this.loading = false;
            }
        },
        mounted() {
            this.getPostTypes();
        },
    };
</script>

<style lang="scss">
    .ninja_tables_wpposts .el-checkbox-group {
        overflow: scroll !important;
    }

    .ninja_tables_wpposts .el-transfer-panel {
        width: 230px !important;
    }

    .ninja_tables_wpposts .table-rows .el-transfer-panel {
        width: 230px !important;
    }

    .ninja_tables_wpposts .el-transfer-panel__item {
        display: block !important;
    }

    .no-margin {
        margin: 0;
    }

    .nt_conditions {
        width: 100%;
        
        .el-collapse-item.is-active {
            border: 1px solid #ebeef5;
            .el-collapse-item__wrap {
                background: #ebeef5;
            }
        }
    }
</style>
