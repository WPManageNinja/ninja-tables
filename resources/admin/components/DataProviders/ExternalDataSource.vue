<template>
    <div>
        <div class="ninja_modal-body" v-if="!editing">
            <template v-if="active_step == 0">
                <template v-if="type == 'google-csv'">
                    <h3 class="nt-modal-title">
                        {{ $t('Construct Table from Google Sheets') }}
                    </h3>
                    <p class="nt-modal-description">
                        {{`Whenever your Google Sheets data changes it will be automatically reflected here.
                            You won't have to do a thing. Please provide the publishable public URL of your google sheet.`}}
                        <a target="_blank"
                           href="https://ninjatables.com/docs/google-sheets-integration/">
                          {{$t('View Documentation Here')}}
                        </a>
                    </p>
                </template>

                <template v-if="type === 'csv'">
                    <h3 class="nt-modal-title">
                       {{ $t('Construct Table from Remote CSV File') }}
                    </h3>
                    <p class="nt-modal-description">
                        {{ $t('Whenever your remote CSV data changes it will be synced here automatically.') }}
                    </p>
                </template>

                <div class="my-[30px]">
                    <el-steps
                        :active="active_step"
                        align-center
                    >
                        <el-step title="Step 1"></el-step>
                        <el-step title="Step 2"></el-step>
                    </el-steps>
                </div>

                <div class="nt-form-group">
                    <label for="nt-form-label">{{ $t('Table Title') }}</label>
                    <NinjaInput
                        v-model="table.post_title"
                        :placeholder="$t('Enter a title to identify your table')"
                        :disabled="!activated_features.external_data_source"
                    />
                </div>

                <div class="nt-form-group">
                    <label for="remote_url" class="nt-form-label">{{ $t('Data Source URL') }}</label>
                    <NinjaInput
                        v-model="table.remote_url"
                        :placeholder="$t('Enter your source URL')"
                        :disabled="!activated_features.external_data_source"
                    />
                </div>
            </template>

            <template v-else>
                <el-table
                        v-loading="fetching"
                        ref="rowSelectableTable"
                        :data="fields"
                        style="width:100% !important"
                        @selection-change="handleFieldsSelectionChange"
                >
                    <el-table-column type="selection"></el-table-column>
                    <el-table-column prop="name" label="Select Entry Fields"></el-table-column>
                </el-table>
            </template>
        </div>

        <template v-else>
            <el-table
                    :loading="fetching"
                    ref="rowSelectableTable"
                    :data="fields"
                    style="width:100% !important"
                    @selection-change="handleFieldsSelectionChange"
            >
                <el-table-column type="selection"></el-table-column>
                <el-table-column prop="name" label="Select Entry Fields"></el-table-column>
            </el-table>
        </template>

        <template v-if="!hasPro">
            <premium-notice/>
        </template>
        <template v-else-if="!activated_features.external_data_source">
            <UpgradeNotice/>
        </template>

        <div class="nt-modal-footer">
            <div v-if="active_step > 0" class="flex items-center gap-4">
                <NinjaButton @click="nextStep" type="secondary" :btnText="$t('Prev')" />
                <NinjaButton @click="save" :btnText="$t('Save')" :disabled="!activated_features.external_data_source"/>
            </div>

            <div v-else class="flex items-center gap-4">
                <NinjaButton @click="$emit('modalClose')" :btnText="$t('Cancel')" type="secondary" />
                <NinjaButton @click="nextStep" :btnText="$t('Next')"/>
            </div>
        </div>

        <div style="margin-top: 15px;" v-if="editing">
            <el-input
                placeholder="Remote URL..."
                v-model="table.remoteURL"
                @keyup.enter="fatchRemoteData"
            >
                <template #prepend>
                    <el-button
                        :loading="fetching"
                        @click="fatchRemoteData"
                        size="default"
                        :plain="true"
                    >{{ $t('Fetch Columns') }}
                    </el-button>
                </template>

                <template #append>
                    <el-button
                        :loading="saving"
                        @click="save"
                        size="default"
                        :plain="true"
                        type="primary"
                    >{{ $t('Update Settings') }}
                    </el-button>
                </template>
            </el-input>
        </div>
    </div>
</template>

<script>
    import PremiumNotice from '../includes/PremiumNotice';
    import UpgradeNotice from '../includes/UpgradeNotice';
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'Remote-Data-Source',
        components: {
            NinjaButton,
            NinjaInput,
            PremiumNotice,
            UpgradeNotice
        },
        props: {
            columns: {
                type: Array
            },
            type: {
                type: String,
                required: true
            },
            tableCreated: {
                type: Function,
                required: true
            },
            hasPro: {
                required: true
            },
            table: {
                type: Object,
                default: () => ({
                    post_title: '',
                    remote_url: '',
                    fields: [],
                    table_id: null,
                })
            },
            editing: {
                type: Boolean,
                default: false
            },
            activated_features: {
                type: Object,
                default: function () {
                    return {}
                }
            }
        },
        data() {
            return {
                fields: [],
                active_step: 0,
                saving: false,
                fetching: false,
            }
        },
        methods: {
            nextStep() {
                let message = '';
                if (!this.table.post_title) {
                    message += ' The title field is required.';
                }
                if (!this.table.remote_url) {
                    message += ' The url field is required.';
                }

                if ((message = jQuery.trim(message))) {
                    this.active_step = 0;
                    this.$message({showClose: true, message: message, type: 'error'});
                    return;
                }

                if (this.active_step++ >= 1) {
                    this.active_step = 0;
                } else {
                    this.fatchRemoteData();
                }
            },
            fatchRemoteData() {
                // if (this.fields.length) return;
                this.fetching = true;

                let data = {
                    action: 'ninja_table_external_data_source_create',
                    ...this.table,
                    type: this.type,
                    get_headers_only: true
                }

                delete data.custom_css;

                this.$post(data)
                    .then(res => {
                        let fields = [];
                        jQuery.each(res.data, v => fields.push({name: v}));
                        this.fields = fields;

                        if (this.editing) {
                            let selected = this.columns.map(c => c.original_name);
                            this.$nextTick(() => {
                                this.fields.filter(f => selected.indexOf(f.name) != -1).forEach(row => {
                                    this.$refs.rowSelectableTable.toggleRowSelection(row);
                                });
                            });
                        }
                    })
                    .fail(res => {
                        let message = '';
                        let messages = res.responseJSON.data.message;
                        for (let key in messages) {
                            message += ' ' + messages[key];
                        }
                        this.$message({showClose: true, message: message, type: 'error'});
                    })
                    .always(res => this.fetching = false);
            },
            handleFieldsSelectionChange(val) {
                this.table.fields = val;
            },
            save(event) {
                this.saving = true;
                let data = {
                    ...this.table,
                    type: this.type,
                    action: 'ninja_table_external_data_source_create'
                }
                delete data.custom_css;
                this.$post(data)
                    .then(({data}) => this.tableCreated(data.ID))
                    .fail(error => {
                        let message = '';
                        let messages = error.responseJSON.data.message;
                        for (let key in messages) {
                            message += ' ' + messages[key];
                        }
                        this.$message({showClose: true, message: message, type: 'error'});
                    })
                    .always(() => this.saving = false);
            }
        },
        created() {
            if (this.editing) {
                this.table.table_id = this.table.ID;
                this.fatchRemoteData();
            }
        }
    };
</script>
