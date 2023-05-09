<template>
    <div v-loading="loading" class="ninja_tables_wpposts">
        <template v-if="!hasPro">
            <premium-notice highlight="Create nice tables from your existings posts/pages/custom post types"/>
        </template>
        <template v-else-if="!activated_features.wp_posts_table">
            <upgrade-notice/>
        </template>
        <div v-else>
            <template v-if="hasPLainLayout">
                <el-row>
                    <el-col :md="12" class="table-rows">
                        <el-transfer
                            :data="post_types"
                            v-model="selected_post_types"
                            :titles="['All Types', 'Selected Types']"
                            style="text-align: left; display: block"
                            @change="handlePostTypeChange">
                        </el-transfer>
                    </el-col>
                    <el-col :md="12" class="table-rows">
                        <el-transfer
                            :data="post_types_fields"
                            v-model="selected_post_types_fields"
                            :titles="['All Properties', 'Selected Properties']"
                            style="text-align: left; display: block">
                        </el-transfer>
                    </el-col>
                </el-row>

                <el-row v-if="!loading">
                    <el-collapse class="nt_conditions" v-model="conditions_section">
                        <el-collapse-item name="1">
                          <el-checkbox v-model="currentUserPosts">Logged in user posts</el-checkbox>
                            <template slot="title">
                                <h4 class="no-margin">Conditions</h4>
                            </template>
                            <wp-post-conditions
                                :config="config"
                                :selected_post_types="selected_post_types"
                                :postStatuses="postStatuses"
                                :conditions="conditions"
                                :allPostTypes="all_types"
                                :fields="query_able_post_types_fields"/>
                        </el-collapse-item>
                    </el-collapse>

                    <el-collapse class="nt_conditions" v-model="meta_query">
                        <el-collapse-item name="1">
                            <template slot="title">
                                <h4 class="no-margin">Meta Query</h4>
                            </template>

                            <wp-post-meta-query
                                :config="config"
                                :metas="metas"
                                :fields="query_able_post_types_fields"
                            />
                        </el-collapse-item>
                    </el-collapse>
                </el-row>

                <el-row>
                    <el-button
                        type="success"
                        size="small"
                        :loading="saving"
                        style="float:right;margin-top:12px;"
                        @click="save">Update
                    </el-button>
                </el-row>
            </template>
            <template v-if="!hasPLainLayout">
                <h3>
                    Construct Table from Posts / CPTs
                </h3>
                <p class="ninja_subtitle">
                    Displays website content in a searchable, sortable with Ninja Tables. It supports custom posts,
                    pages, &
                    custom post types. <a target="_blank"
                                          href="https://ninjatables.com/docs/create-custom-column-in-wp-post-cpt-woocommerce-table/">Learn more
                    about this module</a>
                </p>

                <el-steps :active="active_step" align-center>
                    <el-step title="Step 1"></el-step>
                    <el-step title="Step 2"></el-step>
                </el-steps>

                <template v-if="active_step == 0">
                    <el-row style="margin-top:20px;">
                        <el-input placeholder="Title" v-model="title"></el-input>
                    </el-row>

                    <el-row style="margin-top:20px;">
                        <div style="text-align:center">
                            <el-transfer
                                :data="post_types"
                                v-model="selected_post_types"
                                :titles="['All Types', 'Selected Types']"
                                style="text-align: left; display: block"
                                @change="handlePostTypeChange">
                            </el-transfer>
                        </div>
                    </el-row>
                </template>

                <template v-if="active_step == 1">
                    <el-row style="margin-top:20px;">
                        <div style="text-align: center">
                            <el-transfer
                                :data="post_types_fields"
                                v-model="selected_post_types_fields"
                                :titles="['All Properties', 'Selected Properties']"
                                style="text-align: left; display: block">
                            </el-transfer>
                        </div>
                    </el-row>

                    <el-row style="margin-top:20px;">
                        <div>
                            <el-collapse accordion value="conditions" v-model="conditions_section">
                                <el-collapse-item name="conditions" title="Conditions">
                                  <el-checkbox v-model="currentUserPosts">Logged in user posts</el-checkbox>
                                    <wp-post-conditions
                                        v-if="conditions_section"
                                        :postStatuses="postStatuses"
                                        :selected_post_types="selected_post_types"
                                        :conditions="conditions"
                                        :allPostTypes="all_types"
                                        :fields="query_able_post_types_fields"/>
                                </el-collapse-item>
                            </el-collapse>
                        </div>
                    </el-row>
                </template>

                <el-row>
                    <el-col :md="12">
                        <el-button type="primary" style="margin-top: 12px;" @click="nextStep">
                            {{ active_step > 0 ? 'Prev' : 'Next' }}
                        </el-button>
                    </el-col>

                    <el-col :md="12">
                        <el-button
                            v-if="active_step > 0"
                            type="success"
                            :disabled="!activated_features.wp_posts_table"
                            :loading="saving"
                            style="float:right;margin-top:12px;"
                            @click="save">Save
                        </el-button>
                    </el-col>
                </el-row>
            </template>
        </div>
    </div>
</template>

<script>
    import WPPostConditions from './WPPostConditions';
    import WpPostMetaQuery from './WPPostMetaQuery';
    import PremiumNotice from '../includes/PremiumNotice';
    import UpgradeNotice from '../includes/UpgradeNotice';

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
            'wp-post-conditions': WPPostConditions,
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

                _.forEach(data.where, condition => {
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
        .el-collapse-item.is-active {
            border: 1px solid #ebeef5;
            .el-collapse-item__wrap {
                background: #ebeef5;
            }
        }
    }
</style>
