<template>
    <div class="fluent-form-nav">
        <template v-if="!has_sql_permission">
            <el-alert title=""
                      type="error"
                      :closable="false"
            >
                <p>
                    For security reason, Custom SQL module is enabled only for Administrator user role. Please ask your site administrator to enable SQL module for other user roles.<br/>
                    You can only see the data and update other settings but can't alter the original SQL.
                </p>
                <b>Current SQL Query: </b>
                <p><code>{{config.table.sql}}</code></p>
            </el-alert>
        </template>

        <el-collapse
            v-else
            v-model="active"
            :class="config.table.connection_type === 'external'? 'nt_accordion_group_content_white' : 'nt_accordion_content_white' "
        >
            <el-collapse-item name="1">
                <template #title>
                    <i class="header-icon el-icon-info el-text-info"></i>
                    <p class="nt-form-label"> {{ $t('Edit:') }} {{ $t(isEditableMessage) }}</p>
                </template>

                <div class="form-group my-5">
                    <label class="nt-form-label">{{ $t('Custom SQL Query') }}</label>
                    <ace-code-editor mode="mysql" editor_id="ninja_mysql_editor" v-model="config.table.sql" />
                    <p class="nt-form-description mt-2">Please write valid SQL query. Your written SQL query will be passed to <code class="nt-code">$wpdb->get_results()</code> function</p>
                    <p class="nt-form-description my-1">Check the documentation for advanced use cases: <a class="nt-link" target="_blank" rel="noopener" href="https://ninjatables.com/docs/create-table-from-custom-sql/">Ninja Tables SQL Integration Documentation</a></p>
                    <p class="nt-form-description">Available Dynamic Placeholders: <code class="nt-code">{current_user_id}</code> <code class="nt-code">{current_date}</code> <code class="nt-code">{current_date_time}</code> <code class="nt-code">{current_post_id}</code> <code class="nt-code">{current_post_title}</code> <code class="nt-code">{prefix}</code></p>
                </div>

                <div v-if="error_html" class="form-group mt-2">
                    <el-alert
                        title="SQL Error"
                        @close="error_html = ''"
                        type="error">
                        <div v-html="error_html"></div>
                    </el-alert>
                </div>

                <div class="flex justify-end">
                    <NinjaButton
                        :btn-text="$t('Update SQL Query')"
                        v-loading="loading"
                        @click="updateSql()"
                    />
                </div>

            </el-collapse-item>

            <el-collapse-item v-if="config.table.connection_type === 'external'" name="2">
                <template #title>
                    <p class="nt-form-label">SQL Connection Details</p>
                </template>

                <div class="py-5">
                    <remote-sql-connection :connection="connection_details" />
                </div>

                <div v-if="error_html" class="form-group">
                    <el-alert
                        title="SQL Error"
                        @close="error_html = ''"
                        type="error">
                        <div v-html="error_html"></div>
                    </el-alert>
                </div>

                <div class="form-group flex justify-end">
                    <NinjaButton
                        :btn-text="$t('Update SQL Connection')"
                        v-loading="loading"
                        @click="updateSql()"
                    />
                </div>
            </el-collapse-item>
        </el-collapse>
    </div>
</template>

<script>
    import AceCodeEditor from '../../../common/_ace_editor';
    import RemoteSqlConnection from './_RemoteSQLConnection';
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: "RowSQLNav",
        components: {
            NinjaButton,
            AceCodeEditor,
            RemoteSqlConnection
        },
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
            column_count: {
                type: Number
            }
        },
        data() {
            return {
                error_html: '',
                loading: false,
                active: '',
                connection_details: {},
                has_sql_permission: window.ninja_table_admin.has_sql_permission == 'yes'
            }
        },
        methods: {
            updateSql(param = false) {
                this.loading = true;
                this.error_html = '';

                let data = {
                    action: 'ninja_table_raw_sql_update_sql',
                    table_id: this.config.table.ID,
                    sql: this.config.table.sql
                };

                if(this.config.table.connection_type == 'external') {
                    data.connection_details = this.connection_details;
                }

                this.$post(data)
                    .then((res) => {

                        if(typeof res == 'string') {
                            this.error_html = res;
                            return;
                        }

                        this.tableCreated();
                        if(!param) {
                          this.$message({showClose: true, message: res.data.message, type: 'success'});
                        }
                    })
                    .fail((error) => {
                        let message = '';
                        let messages = error.responseJSON.data.message;
                        for (let key in messages) {
                            message += ' ' + messages[key];
                        }
                        this.$message({showClose: true, message: message, type: 'error'});

                        if(error.responseJSON.data.error) {
                            this.error_html = error.responseJSON.data.error;
                        }
                    })
                    .always(() => {
                        this.loading = false;
                    });
            },
            getRemoteConnectionDetails() {
                this.$get({
                    action: 'ninja_table_raw_sql_remote_connection_details',
                    table_id: this.config.table.ID,
                })
                    .then(response => {
                        this.connection_details = response.data.connection_details;
                    })
                    .fail(error => {
                        console.log(error);
                    });
            }
        },
        mounted() {
           /*  this.updateSql is call to get table updated data based on sql query
               Note: When we change external source or remote database then we need to click manually Update SQL Query button to get updated data.
               To solve this issue we call this.updateSql from mounted so that it will call automatically, when we click Table Rows Tab.
           */
            this.updateSql(true);

            if(!this.column_count) {
                this.active = '1';
            }

            if(this.config.table.connection_type == 'external') {
                this.getRemoteConnectionDetails();
            }
        }
    }
</script>

<style lang="scss">
    .fluent-form-nav {
        .el-collapse-item__header,
        .el-collapse-item__wrap {
            padding: 0 15px 15px 15px;
        }

        .sync-settings {
            margin-top: 15px;
        }

        .el-collapse-item__content {
            padding-bottom: 15px;
        }
    }
</style>
