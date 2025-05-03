<template>
    <div class="ninja_modal-body">
        <h3>
            Construct Table from Custom SQL Query
        </h3>
        <hr />
        <template v-if="!hasPro">
            <premium-notice highlight="SQL module where you can write custom SQL to build your table with "/>
        </template>

        <template v-else-if="!has_sql_permission">
            <el-alert title=""
                      type="error"
                      :closable="false"
            >
                <p>
                    For security reason, Custom SQL module is enabled only for Administrator user role. Please ask your site administrator to enable SQL module for other user roles.
                </p>
            </el-alert>
        </template>

        <template v-else-if="isActivated">
            <div class="ninja_modal-body">
                <div class="form-group">
                    <label for="name">{{ $t('Table Title') }}</label>
                    <input v-model="post_title"
                           type="text" id="name" class="form-control"
                           placeholder="Enter a title to identify your table"
                    >
                </div>

                <div class="form-group">
                    <label>{{ $t('Custom SQL Query') }}</label>
                    <my-sql-editor mode="mysql" editor_id="ninja_mysql_editor" v-model="sql" />
                    <p>Please write valid SQL query. Your written SQL query will be passed to <code>$wpdb->get_results()</code> function</p>
                </div>

                <div v-if="error_html" class="form-group">
                    <el-alert
                        title="SQL Error"
                        @close="error_html = ''"
                        type="error">
                        <div v-html="error_html"></div>
                    </el-alert>
                </div>

                <div class="form-group">
                    <label>{{ $t('SQL COnnection Type') }}</label>
                    <el-radio-group v-model="connection_type">
                        <el-radio label="local">Default WP SQL Table</el-radio>
                        <el-radio label="external">Remote/External SQL</el-radio>
                    </el-radio-group>
                </div>

                <div v-if="connection_type == 'external'" class="form-group">
                    <label>{{ $t('MYSQL Connection Details') }}</label>
                    <remote-sql-connection :connection="remote_connection" />
                </div>

            </div>

            <div class="modal-footer">
                <el-button type="primary" size="small" @click="save">
                    <span>{{ $t('Add') }}</span>
                    <i v-if="btnLoading" class="fooicon fooicon-spin fooicon-circle-o-notch"></i>
                </el-button>
            </div>
        </template>
        <template v-else>
            <el-alert title=""
                      type="warning"
                      :closable="false"
                      show-icon
                      class="update-notice"
            >
                <p>
                    Please update Ninja Tables Pro Plugin to latest version. Required Ninja Table Version: 3.3 or later
                </p>
            </el-alert>
        </template>
    </div>
</template>

<script>
    import MySqlEditor from '../../../common/_ace_editor_sql';
    import PremiumNotice from '../includes/PremiumNotice';
    import RemoteSqlConnection from '../TableNav/_RemoteSQLConnection'
    export default {
        name: 'CustomSQLQuery',
        components: {
            MySqlEditor,
            PremiumNotice,
            RemoteSqlConnection
        },
        props: {
            tableCreated: {
                type: Function,
                required: true
            },
            editing: {
                type: Boolean
            },
            config: {
                type: Object
            },
            activated_features: {
                type: Object
            }
        },
        data() {
            return {
                installing: false,
                fetching: false,
                btnLoading: false,
                post_title: '',
                connection_type: 'local',
                remote_connection: {
                    db_name: '',
                    db_username: '',
                    db_userpassword: '',
                    db_host: 'localhost',
                    db_host_port: 3306
                },
                sql: '',
                error_html: '',
                isActivated: this.activated_features?.raw_sql_query,
                hasPro: !!ninja_table_admin.hasPro,
                has_sql_permission: window.ninja_table_admin.has_sql_permission == 'yes'
            };
        },
        methods: {
            save() {
                this.btnLoading = true;
                this.error_html = '';
                this.$post({
                    action: 'ninja_tables_save_raw_sql_table',
                    post_title: this.post_title,
                    sql: this.sql,
                    connection_details: this.remote_connection,
                    connection_type: this.connection_type,
                    table_Id: this.config && this.config.table.ID || null
                })
                    .then((res) => {

                        console.log(res);

                        if(typeof res == 'string') {
                            this.error_html = res;
                            return;
                        }
                        if(res && res.data && res.data.table_id) {
                            this.tableCreated(res.data.table_id)
                        } else {
                            this.error_html = res;
                        }
                    })
                    .fail(error => {
                        console.log(error);
                        if(typeof error == 'string') {
                            this.error_html = res;
                            return;
                        }
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
                    .always(res => this.btnLoading = false);
            }
        },
        mounted() {

        }
    };
</script>

<style lang="scss">
    .fluent-form-promo {
        p {
            font-size: initial;
        }
    }
</style>
