<template>
    <div class="ninja_modal-body">
        <h3 v-if="!editing">
            Construct Table from WP Fluent Form Entries
        </h3>

        <template v-if="isFluentFormUpdated">
            <p class="ninja_subtitle" v-if="!editing">
                Prepare your table from your existing WP Fluent Forms submissions. It can be used to easily showcase
                your form submissions.
                <a target="_blank" href="https://ninjatables.com/docs/wp-fluent-forms-integration/">Click
                    here to learn more about WP Fluent From Integration</a>
            </p>

            <div class="form-group" v-if="!editing">
                <label for="name">{{ $t('Table Title') }}</label>
                <input
                        v-model="post_title"
                        type="text" id="name" class="form-control"
                        placeholder="Enter a title to identify your table"
                >
            </div>

            <div class="form-group" v-if="!editing">
                <el-select
                        v-loading="fetching"
                        filterable
                        v-model="form.id"
                        style="width:100%"
                        placeholder="Select a Form"
                        @change="handleFormSelectionChange">
                    <el-option
                            v-for="form in forms"
                            :key="form.id"
                            :label="form.id +' : '+ form.title"
                            :value="form.id">
                    </el-option>
                </el-select>
            </div>

            <div v-if="form.id" class="form-group">
                <el-table
                        :data="fields"
                        empty-text="Loading..."
                        ref="rowSelectableTable"
                        style="width:100% !important"
                        @selection-change="handleFieldsSelectionChange"
                >
                    <el-table-column type="selection"></el-table-column>
                    <el-table-column prop="label" label="Select Entry Fields"></el-table-column>
                </el-table>
            </div>

            <div class="form-group">
                <strong>Options (Optional)</strong>
                <hr>
                <el-row :gutter="20" style="margin-top:15px;">
                    <el-col :md="12">
                        <el-row>
                            <el-col :md="6" style="margin-top:10px;">
                                <strong>
                                    <el-tooltip
                                            placement="right"
                                            effect="light"
                                            content="Maximun records to show in frontend, keep empty to show all."
                                    >
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                    Max Records:
                                </strong>
                            </el-col>
                            <el-col :md="18">
                                <el-input v-model="form.entry_limit"></el-input>
                            </el-col>
                        </el-row>
                    </el-col>

                    <el-col :md="12" style="margin-top:10px;">
                        <el-row>
                            <el-col :md="6">
                                <strong>
                                    <el-tooltip
                                            placement="right"
                                            effect="light"
                                            content="Select what type of entries you want to show from fluent form."
                                    >
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                    Entry Type:
                                </strong>
                            </el-col>
                            <el-col :md="18" style="margin-top:3px;">
                                <el-radio-group v-model="form.entry_status">
                                    <el-radio label="all">All</el-radio>
                                    <el-radio label="read">Read</el-radio>
                                    <el-radio label="unread">Unread</el-radio>
                                </el-radio-group>
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>
                <template v-if="config && config.table">
                    <br />
                    <el-checkbox true-label="yes" false-label="no" v-model="config.table.current_user_entry_only">Show current user submissions only at frontend</el-checkbox>
                </template>
            </div>
            <hr>
            <div class="form-group">
                <el-button
                        size="small"
                        type="primary"
                        :loading="btnLoading"
                        style="margin-top: 12px; float: right;"
                        @click="save">{{ editing ? $t('Update') : $t('Save') }}
                </el-button>
            </div>
        </template>

        <template v-else-if="hasFluentForm">
            <el-alert title=""
                      type="warning"
                      :closable="false"
                      show-icon
                      class="premium-notice"
            >
                <p>To use this feature your WP Fluent Form need to be updated. Please update WP Fluent From from plugins
                    screen</p>
            </el-alert>

            <h4>See the form in action:</h4>
            <br/>
            <div style="position: relative;padding-bottom: 56.25%;padding-top: 25px;height: 0;">
                <iframe style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" width="700" height="394"
                        src="https://www.youtube.com/embed/XxBrmuhu6yQ" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
        </template>

        <div v-else class="fluent-form-promo">
            <p>
                <a href="https://wordpress.org/plugins/fluentform" target="_blank">WP Fluent Form</a> is a WordPress
                Contact Form plugin packed with all the premium features you would need to create
                a responsive, customizable, drag and drop form. Using this module, You can easily show your form entries
                in Ninja Tables.
            </p>
            <div>
                <el-button v-loading="installing" @click="installFluentFrom" type="success"><span v-if="installing">Installing WP Fluent From...</span><span
                        v-else>Install Fluent Form Now</span></el-button>
                <p v-if="installing">Please wait while installing WP Fluent From</p>
            </div>
            <h4>See the form in action:</h4>
            <br/>
            <div style="position: relative;padding-bottom: 56.25%;padding-top: 25px;height: 0;">
                <iframe style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" width="700" height="394"
                        src="https://www.youtube.com/embed/XxBrmuhu6yQ" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'FluentForm',
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
            }
        },
        data() {
            return {
                installing: false,
                fetching: false,
                forms: [],
                fields: [],
                btnLoading: false,
                post_title: '',
                form: {
                    id: null,
                    fields: [],
                    entry_status: 'all',
                    entry_limit: 1000,
                },
                hasFluentForm: !!window.ninja_table_admin.hasFluentForm,
                isFluentFormUpdated: !!window.ninja_table_admin.isFluentFormUpdated,
            };
        },
        methods: {
            fetchForms() {
                this.fetching = true;
                this.$get({
                    action: 'ninja_tables_get-fluentform-forms'
                })
                    .then(res => this.forms = res.data)
                    .fail(error => console.log(error))
                    .always(() => {
                        this.fetching = false;
                    });
            },
            handleFormSelectionChange(formId) {
                this.$get({
                    form_Id: formId,
                    action: 'ninja-tables_get-fluentform-fields'
                })
                    .then(res => {
                        this.fields = res.data
                        if (this.editing) {
                            this.form.entry_limit = this.config.table.entry_limit;
                            this.form.entry_status = this.config.table.entry_status;
                            this.$nextTick(() => {
                                let selected = this.config.columns.map(c => c.original_name);
                                this.fields.filter(f => selected.indexOf(f.name) != -1).forEach(row => {
                                    this.$refs.rowSelectableTable.toggleRowSelection(row);
                                });
                            });
                        }
                    })
                    .fail(error => {

                    })
                    .always(() => {

                    });
            },
            handleFieldsSelectionChange(val) {
                this.form.fields = val;
            },
            save() {
                this.btnLoading = true;

                if(this.config && this.config.table.current_user_entry_only) {
                    this.form.current_user_entry_only = this.config.table.current_user_entry_only;
                }

                this.$post({
                    action: 'ninja_tables_save_fluentform_table',
                    post_title: this.post_title,
                    form: this.form,
                    table_Id: this.config && this.config.table.ID || null
                })
                    .then(res => this.tableCreated(res.data.table_id))
                    .fail(error => {
                        let message = '';
                        let messages = error.responseJSON.data.message;
                        for (let key in messages) {
                            message += ' ' + messages[key];
                        }
                        this.$message({showClose: true, message: message, type: 'error'});
                    })
                    .always(res => this.btnLoading = false);
            },
            installFluentFrom() {
                this.installing = true;
                this.$post('fluent-forms')
                    .then(response => {
                        this.$message.success(response.data.message);
                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }
                    })
                    .catch(error => {
                        this.$message.error(error.responseJSON.message);
                    })
                this.installing = false;
            }
        },
        mounted() {
            if (this.hasFluentForm) {
                !this.editing ? this.fetchForms() : this.handleFormSelectionChange(
                    this.form.id = this.config.table.fluentFormFormId
                );
            }
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
