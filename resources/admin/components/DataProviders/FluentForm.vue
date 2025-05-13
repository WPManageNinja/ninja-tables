<template>
    <div class="ninja_modal-body">
        <h3 v-if="!editing" class="ninja_modal_title">
            {{ $t('Construct Table from WP Fluent Form Entries') }}
        </h3>

        <template v-if="isFluentFormUpdated">
            <p class="ninja_modal_subtitle" v-if="!editing">
                {{ $t('Prepare your table from your existing WP Fluent Forms submissions. It can be used to easily showcase your form submissions.') }}
                <a target="_blank" href="https://ninjatables.com/docs/wp-fluent-forms-integration/">
                   {{ $t('Click here to learn more about WP Fluent From Integration') }}
                </a>
            </p>

            <div class="my-[30px]">
                <div class="ninja-form-group" v-if="!editing">
                    <label for="name" class="ninja-form-label">{{ $t('Table Title') }}</label>
                    <NinjaInput
                        v-model="post_title"
                        :placeholder="$t('Enter a title to identify your table')"
                    />
                </div>

                <div class="ninja-form-group" v-if="!editing">
                    <label for="name" class="ninja-form-label">{{ $t('Choose Form') }}</label>
                    <el-select
                        class="ninja-select"
                        v-loading="fetching"
                        v-model="form.id"
                        style="width:100%"
                        :placeholder="$t('Select a Form')"
                        @change="handleFormSelectionChange">
                        <el-option
                            v-for="form in forms"
                            :key="form.id"
                            :label="form.id +' : '+ form.title"
                            :value="form.id">
                        </el-option>
                    </el-select>
                </div>

                <div v-if="fields.length > 0 && form.id" class="ninja-checkbox-group-wrapper">
                    <div class="ninja-checkbox-group-header"
                         style="border-bottom: 1px solid #E1E4EA">
                        <div>{{ $t('Select Form Fields') }}</div>
                        <div>
                            <el-checkbox
                                v-model="checkAll"
                                :indeterminate="isIndeterminate"
                                @change="handleCheckAllChange"
                            >
                                {{ $t('Select all') }}
                            </el-checkbox>
                        </div>
                    </div>
                    <div class="p-4">
                        <el-checkbox-group
                            v-model="selectedFields"
                            @change="handleFieldsSelectionChange"
                        >
                            <el-checkbox v-for="fld in fields" :key="fld.name" :value="fld.name">
                                {{ fld.label }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </div>

                <div class="my-4">
                    <p class="mb-2"><strong>Options (Optional)</strong></p>
                    <hr>
                </div>

                <div class="ninja-form-group flex justify-start items-center gap-8">
                    <div>
                        <el-tooltip
                            placement="right"
                            effect="light"
                            content="Maximum records to show in frontend, keep empty to show all."
                        >
                            <el-icon class="tooltip-icon-color">
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                        <label class="ninja-form-label">{{ $t('Max Records:') }}</label>
                        <NinjaInput v-model="form.entry_limit" />
                    </div>

                    <div>
                        <el-tooltip
                            placement="right"
                            effect="light"
                            content="Select what type of entries you want to show from fluent form."
                        >
                            <el-icon class="tooltip-icon-color">
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                        <label class="ninja-form-label">{{ $t('Entry Type:') }}</label>
                        <el-radio-group v-model="form.entry_status" class="ninja_tables_radio_group">
                            <el-radio border value="all" :label="$t('All')" class="mr-2" />
                            <el-radio border value="read" :label="$t('Read')" class="mr-2" />
                            <el-radio border value="unread" :label="$t('Unread')" class="mr-2" />
                        </el-radio-group>
                    </div>
                </div>

                <div class="ninja-form-group">
                    <template v-if="config && config.table">
                        <el-checkbox
                            :true-value="'yes'"
                            :false-value="'no'"
                            v-model="config.table.current_user_entry_only"
                        >
                           {{ $t('Show current user submissions only at frontend') }}
                        </el-checkbox>
                    </template>
                </div>

                <div class="modal-footer">
                    <NinjaButton type="secondary" @click="closeModal" :btnText="$t('Cancel')" />
                    <NinjaButton v-if="editing"  @click="save" :btnText="$t('Update')"/>
                    <NinjaButton v-else  @click="save" :btnText="$t('Save')" />
                </div>

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
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import {InfoFilled} from "@element-plus/icons-vue";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'FluentForm',
        components: {NinjaButton, InfoFilled, NinjaInput},
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
                isIndeterminate: false,
                checkAll: false,
                hasFluentForm: !!window.ninja_table_admin.hasFluentForm,
                isFluentFormUpdated: !!window.ninja_table_admin.isFluentFormUpdated,
                selectedFields: [],
            };
        },
        methods: {
            fetchForms() {
                this.fetching = true;
                this.$get('fluent-forms')
                    .then(res => this.forms = res.data)
                    .catch(error => console.log(error))
               this.fetching = false;
            },
            handleFormSelectionChange(formId) {
                this.$get('fluent-forms/' + formId)
                    .then(res => {
                        this.fields = res.data;
                        this.selectedFields = [];

                        if (this.editing) {
                            this.form.entry_limit = this.config.table.entry_limit;
                            this.form.entry_status = this.config.table.entry_status;

                            // Set selected fields based on config
                            if (this.config.columns) {
                                this.selectedFields = this.config.columns
                                    .map(c => c.original_name)
                                    .filter(name => this.fields.some(f => f.name === name));
                                this.updateCheckAllState();
                            }
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            },
            handleFieldsSelectionChange(value) {
                this.form.fields = this.fields.filter(field => value.includes(field.name));
                this.updateCheckAllState();
            },
            handleCheckAllChange(val) {
                this.selectedFields = val ? this.fields.map(field => field.name) : [];
                this.updateCheckAllState();
            },
            updateCheckAllState() {
                const fieldsCount = this.fields.length;
                const selectedCount = this.selectedFields.length;
                this.checkAll = selectedCount === fieldsCount;
                this.isIndeterminate = selectedCount > 0 && selectedCount < fieldsCount;
            },
            save() {
                this.btnLoading = true;

                if(this.config && this.config.table && this.config.table.current_user_entry_only) {
                    this.form.current_user_entry_only = this.config.table.current_user_entry_only;
                }

                // Make sure form.fields is properly set from selectedFields
                this.form.fields = this.fields.filter(field => this.selectedFields.includes(field.name));

                let data = {
                  post_title: this.post_title,
                  form: this.form,
                  table_Id: this.config && this.config.table.ID || null
                }

                this.$post('fluent-forms/save', data)
                    .then(res => this.tableCreated(res.data.table_id))
                    .catch(error => {
                        let message = '';
                        let messages = error.responseJSON.data.message;
                        for (let key in messages) {
                            message += ' ' + messages[key];
                        }
                        this.$message({showClose: true, message: message, type: 'error'});
                    })
                this.btnLoading = false
            },

            closeModal() {
                this.$emit('modalClose');
            },
            installFluentFrom() {
                this.installing = true;
                this.$post('install/fluent-forms')
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
