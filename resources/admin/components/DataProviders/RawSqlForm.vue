<template>
    <template v-if="!hasPro">
        <PremiumNotice title="Custom SQL Query" highlight="SQL module where you can write custom SQL to build your table with "/>
    </template>

    <div v-else class="ninja_modal-body">
        <h3 class="nt-modal-title">
           {{$t('Construct Table from Custom SQL Query')}}
        </h3>
        <p class="nt-modal-description">
            {{$t('Construct Table from Custom SQL Query. Create a table by generating a sql query to any custom SQL database.')}}
            <a class="nt-link" target="_blank"
               href="https://ninjatables.com/docs/create-table-from-custom-sql/">
                {{$t('View documentation of this feature.')}}
            </a>
        </p>

        <template v-if="!has_sql_permission">
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
            <div class="my-[30px]">
                <div class="nt-form-group">
                    <label class="nt-form-label">{{ $t('Table Title') }}</label>
                    <NinjaInput
                        v-model="post_title"
                        :placeholder="$t('Enter a title to identify your table')"
                    />
                </div>

                <div class="nt-form-group">
                    <label class="nt-form-label">{{ $t('Custom SQL Query') }}</label>
                    <ace_code_editor editor_id="ninja_mysql_editor" mode="mysql" v-model="sql"></ace_code_editor>
                    <p class="nt-form-description mt-2">Please write valid SQL query. Your written SQL query will be passed to <code>$wpdb->get_results()</code> function</p>
                </div>

                <div v-if="error_html" class="nt-form-group">
                    <el-alert
                        title="SQL Error"
                        @close="error_html = ''"
                        type="error">
                        <div v-html="error_html"></div>
                    </el-alert>
                </div>

                <div class="nt-form-group mt-4 flex flex-col">
                    <label class="nt-form-label mb-2">{{ $t('SQL Connection Type:') }}</label>
                    <el-radio-group v-model="connection_type" class="ninja_tables_radio_group">
                        <el-radio border value="local" label="Default WP SQL Table" class="mr-2"/>
                        <el-radio border value="external" label="Remote/External SQL" />
                    </el-radio-group>
                </div>

                <div v-if="connection_type === 'external'" class="nt-form-group mt-4">
                    <label class="nt-form-label">{{ $t('MYSQL Connection Details') }}</label>
                    <remote-sql-connection :connection="remote_connection" />
                </div>

            </div>

            <div class="nt-modal-footer">
                <NinjaButton
                    type="secondary"
                    @click="$emit('modalClose')"
                    :btn-text="$t('Cancel')"
                />
                <NinjaButton
                    @click="save"
                    :btn-text="$t('Add')"
                />
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
    import PremiumNotice from '../includes/PremiumNotice';
    import RemoteSqlConnection from '../TableNav/_RemoteSQLConnection'
    import ace_code_editor from "../../../common/_ace_editor.vue";
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'CustomSQLQuery',
        components: {
            NinjaButton,
            NinjaInput,
            ace_code_editor,
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
