<template>
    <div>
        <div class="ninja_modal-body" v-if="!editing">
            <div class="external-link-heading">
                <template v-if="type === 'google-csv'">
                    <h3 class="nt-modal-title">
                        {{ $t('Construct Table from Google Sheets') }}
                    </h3>
                    <p class="nt-modal-description">
                        {{`Whenever your Google Sheets data changes it will be automatically reflected here.
                            You won't have to do a thing. Please provide the publishable public URL of your google sheet.`}}
                        <a class="nt-link" target="_blank"
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
            </div>

            <div class="my-[30px]">
                <el-steps
                    class="mx-auto my-5 max-w-[350px] nt-steps"
                    :active="active_step"
                    align-center
                >
                    <el-step title="Step 1"></el-step>
                    <el-step title="Step 2"></el-step>
                </el-steps>
            </div>

            <div>
                <template v-if="active_step === 0">
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
                    <div class="nt-checkbox-group-wrapper">
                        <div class="nt-checkbox-group-header"
                             style="border-bottom: 1px solid #E1E4EA">
                            <div>{{ $t('Select Entry Fields') }}</div>
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
                            <div v-if="fetching" class="text-center mt-2 text-gray-500 text-sm">
                                <el-icon class="is-loading">
                                    <Loading />
                                </el-icon>
                            </div>

                            <el-checkbox-group
                                v-else
                                v-model="selectedFields"
                                @change="handleFieldsSelectionChange"
                                class="nt-checkbox-group"
                                v-loading="fetching"
                                element-loading-text="Loading fields..."
                            >
                                <el-checkbox v-for="field in fields" :key="field.name" :value="field.name">
                                    {{ field.name }}
                                </el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>
                </template>
            </div>
        </div>


<!--  for table edit nav -->
        <div v-else class="mt-5">
            <div class="nt-checkbox-group-wrapper">
                <div class="nt-checkbox-group-header"
                     style="border-bottom: 1px solid #E1E4EA">
                    <div>{{ $t('Select Entry Fields') }}</div>
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

                    <div v-if="fetching" class="text-center mt-2 text-gray-500 text-sm">
                        <el-icon class="is-loading">
                            <Loading />
                        </el-icon>
                    </div>

                    <el-checkbox-group
                        v-else
                        v-model="selectedFields"
                        @change="handleFieldsSelectionChange"
                        class="nt-checkbox-group pb-2"
                        v-loading="fetching"
                        element-loading-text="Loading fields..."
                    >
                        <el-checkbox v-for="field in fields" :key="field.name" :value="field.name">
                            {{ field.name }}
                        </el-checkbox>
                    </el-checkbox-group>
                </div>
            </div>
        </div>

        <div v-if="!hasPro" class="mb-4">
            <premium-notice/>
        </div>

        <div v-else-if="!activated_features.external_data_source" class="mb-4">
            <UpgradeNotice/>
        </div>

        <div class="nt-modal-footer" v-if="!editing">
            <div v-if="active_step > 0" class="flex items-center gap-4">
                <NinjaButton @click="nextStep" type="secondary" :btnText="$t('Previous')" />
                <NinjaButton @click="save" :btnText="$t('Save')" :disabled="!activated_features.external_data_source"/>
            </div>

            <div v-else class="flex items-center gap-4">
                <NinjaButton @click="$emit('modalClose')" :btnText="$t('Cancel')" type="secondary" />
                <NinjaButton @click="nextStep" :btnText="$t('Next')"/>
            </div>
        </div>

        <div v-if="editing" class="mt-5 mb-2">

            <div class="flex gap-5">
                <el-input
                    size="small"
                    :placeholder="$t('Remote URL...')"
                    v-model="table.remoteURL"
                    @keyup.enter="fatchRemoteData"
                >
                    <template #suffix>
                        <NinjaButton
                            size="small"
                            :btn-text="$t('Fetch Columns')"
                            :icon="assetUrl('icons/refresh.svg')"
                            :loading="fetching"
                            @click="fatchRemoteData"
                            type="secondary"
                            />
                    </template>
                </el-input>

                <NinjaButton
                    :loading="saving"
                    @click="save"
                    :btn-text="$t('Update')"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import PremiumNotice from '../includes/PremiumNotice';
    import UpgradeNotice from '../includes/UpgradeNotice';
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";
    import {assetUrl} from "../../utils/ninjatablesadmin";

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
                selectedFields: [],
                checkAll: false,
                isIndeterminate: false
            }
        },
        methods: {
            assetUrl,
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
                            this.selectedFields = selected.filter(name =>
                                this.fields.some(f => f.name === name)
                            );
                            this.updateCheckAllState();
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
            handleCheckAllChange(val) {
                this.selectedFields = val ? this.fields.map(field => field.name) : [];
                this.handleFieldsSelectionChange(this.selectedFields);
            },
            handleFieldsSelectionChange(value) {
                this.table.fields = this.fields.filter(field => value.includes(field.name));
                this.updateCheckAllState();
            },
            updateCheckAllState() {
                const fieldsCount = this.fields.length;
                const selectedCount = this.selectedFields.length;
                this.checkAll = fieldsCount > 0 && selectedCount === fieldsCount;
                this.isIndeterminate = selectedCount > 0 && selectedCount < fieldsCount;
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
