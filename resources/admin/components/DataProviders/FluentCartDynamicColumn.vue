<template>
    <div v-loading="loading" class="fluent_cart_dynamic_field">
        <h4>{{ $t('Dynamic Column Data Settings') }}</h4>
        <hr>
        <el-form-item class="flex flex-col mt-2">
            <label class="nt-form-label">
                {{ $t("Field Type") }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>{{ $t('Field Type') }}</h3>
                        <p>{{ $t('Select The field type you want to populate for each row') }}</p>
                    </template>
                    <el-icon class="tooltip-icon-color">
                        <InfoFilled/>
                    </el-icon>
                </el-tooltip>
            </label>
            <el-select
                class="ninja-select"
                v-model="column.wp_post_custom_data_source_type"
                placeholder="Select Data Type"
                size="small"
            >
                <el-option
                    v-for="type in fct_data_types"
                    :value="type.key"
                    :disabled="type.disabled"
                    :label="type.label"
                    :key="type.key"
                ></el-option>
            </el-select>
        </el-form-item>

        <div class="flex flex-col mb-2">
            <label class="nt-form-label mb-2">
                {{ $t("Field Value") }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>{{ $t('Field Value') }}</h3>
                        <p>{{ $t('Provide the column value for your corresponding value type select') }}</p>
                    </template>

                    <el-icon class="tooltip-icon-color">
                        <InfoFilled/>
                    </el-icon>
                </el-tooltip>
            </label>
            <div v-if="selectedFiledValueType === 'options'">
                <el-select
                    class="ninja-select"
                    v-model="column.wp_post_custom_data_key"
                    :placeholder="selectedField.placeholder"
                    size="small"
                >
                    <el-option
                        v-for="(type,typevalue) in selectedField.options"
                        :value="typevalue"
                        :label="type"
                        :key="typevalue"
                    ></el-option>
                </el-select>

                <div v-if="column.wp_post_custom_data_source_type === 'featured_image'" class="mt-4">
                    <el-radio-group class="ninja_tables_radio_group" v-model="column.image_permalink_type">
                        <el-radio border value="lightbox" label="Make image as lightbox"/>
                        <el-radio border value="linked" label="Link to Product page"/>
                        <el-radio border value="" label="None"/>
                    </el-radio-group>
                    <p v-show="column.image_permalink_type === 'linked'" class="mt-2">
                        <el-checkbox :true-value="'_blank'" :false-value="'no'" v-model="column.permalink_target">Open Link in new tab
                        </el-checkbox>
                    </p>
                </div>
            </div>
            <NinjaInput
                v-else
                :textarea="true"
                :placeholder="selectedField.placeholder || 'Enter Value'"
                size="small"
                v-model="column.wp_post_custom_data_value"
            />

            <div class="nt-instruction my-4" v-if="selectedField && selectedField.instruction">
                <p v-html="selectedField.instruction"></p>
                <p v-if="selectedField.learn_more_url">
                    <a target="_blank" :href="selectedField.learn_more_url" class="nt-link">
                        {{ selectedField.learn_more_text }}
                    </a>
                </p>
            </div>
        </div>

        <div v-if="column.wp_post_custom_data_key === 'buy_now_button'">
            <label class="nt-form-label">
                {{ $t("Buy Now Button Text") }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>{{ $t('Buy Now Button Text') }}</h3>
                        <p>{{ $t('Provide Buy Now Button Text') }}</p>
                    </template>
                    <el-icon class="tooltip-icon-color">
                        <InfoFilled/>
                    </el-icon>
                </el-tooltip>
            </label>

            <NinjaInput
                :placeholder="$t('Buy Now Button Text')"
                v-model="column.buy_now_button_text"
            />
        </div>

        <template
            v-if="column.wp_post_custom_data_key === 'post_title' || (column.source_type === 'custom' && column.wp_post_custom_data_type === 'featured_image')">
            <div>
                <div>
                    {{ $t("Link") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>{{ $t('Link to Post/Author Permalink') }}</h3>
                            <p>{{ $t('Enable this if you want to link to post/Author permalink') }}</p>
                        </template>
                        <el-icon class="tooltip-icon-color">
                            <InfoFilled/>
                        </el-icon>
                    </el-tooltip>
                </div>

                <div>
                    <el-checkbox v-if="column.wp_post_custom_data_key === 'post_author'" v-model="column.permalinked"
                                 :true-value="'yes'" :false-value="'no'" value="yes"
                                 :label="$t('Link to Author Permalink')"></el-checkbox>
                    <el-checkbox v-else v-model="column.permalinked" :true-value="'yes'" :false-value="'no'" value="yes"
                                 :label="$t('Link to post permalink')"></el-checkbox>
                    <el-checkbox v-if="column.permalinked === 'yes'" v-model="column.permalink_target" :true-value="'_blank'" :false-value="''" value="_blank"
                                 :label="$t('Open link to new tab')"></el-checkbox>
                </div>


            </div>
        </template>

        <template v-else-if="column.source_type === 'tax_data' && column.wp_post_custom_data_key === 'product-categories'">
            <div class="flex flex-col mt-4">
                <label class="nt-form-label mb-2">
                    {{ $t("Link") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>{{ $t('Link to Taxonomy Permalink') }}</h3>
                            <p>{{ $t('Enable this if you want to link to Taxonomy permalink') }}</p>
                        </template>
                        <el-icon class="tooltip-icon-color">
                            <InfoFilled/>
                        </el-icon>
                    </el-tooltip>
                </label>

                <el-checkbox v-model="column.permalinked" :true-value="'yes'" :false-value="'no'" value="yes"
                             :label="$t('Link to Taxonomy')"></el-checkbox>
            </div>

            <div class="flex flex-col mt-4">
                <label class="nt-form-label">
                    {{ $t("Taxonomy Separator") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Taxonomy Separator</h3>
                            <p>Taxonomy Separator for Multiple Items</p>
                        </template>

                        <el-icon class="tooltip-icon-color">
                            <InfoFilled/>
                        </el-icon>
                    </el-tooltip>
                </label>

                <NinjaInput
                    :placeholder="$t('Enter Value')"
                    v-model="column.taxonomy_separator"
                />
            </div>

            <div v-if="column.permalinked === 'yes'" class="mt-4">
                <div class="flex flex-col">
                    <label class="nt-form-label mb-2">
                        {{ $t("Permalink Action") }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3>{{ $t('Permalink Action Type') }}</h3>
                                <p>
                                    {{ $t('Enable this if you want to make the taxonomies as table filter action. So when user click on those filters then they will see only those type of posts.') }}</p>
                            </template>
                            <el-icon class="tooltip-icon-color">
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                    </label>
                    <el-checkbox v-model="column.filter_permalinked" :true-value="'yes'" false-label="" value="yes"
                                 :label="$t('Make Taxonomies as Table Filter')"></el-checkbox>
                </div>

                <div class="flex flex-col mt-4" v-if="column.filter_permalinked !== 'yes'">
                    <label class="nt-form-label mb-2">
                        {{ $t("Open Link To New tab") }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3>{{ $t('Open Link To New tab') }}</h3>
                                <p>{{ $t('Enable this if you want to open the links to new tab') }}</p>
                            </template>
                            <el-icon class="tooltip-icon-color">
                                <InfoFilled/>
                            </el-icon>
                        </el-tooltip>
                    </label>
                    <el-checkbox v-model="column.permalink_target" :true-value="'_blank'" :false-value="''"
                                 value="_blank" :label="$t('Open link to new tab')"></el-checkbox>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import {InfoFilled} from "@element-plus/icons-vue";
import NinjaInput from "../../@ui-utils/NinjaInput.vue";

export default {
    name: "FluentCartDynamicColumn",
    components: {
        NinjaInput,
        InfoFilled
    },
    props: {
        column: {
            type: Object,
            default: () => ({})
        },
        columns: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            loading: false,
            fct_data_types: [],
            table_id: this.$route.params.table_id
        };
    },
    watch: {
        'column.wp_post_custom_data_source_type': function () {
            this.column.source_type = this.selectedField.source_type;
            this.column.wp_post_custom_data_key = '';

            if (this.column.wp_post_custom_data_source_type == 'featured_image') {
                this.column.image_permalink_type = '';
                this.column.wp_post_custom_data_key = 'thumbnail';
            }

            if (this.selectedField.source_type == 'tax_data') {
                this.column.taxonomy_separator = ', ';
            }

            this.column.column_type = 'dynamic_column';
        },
        fct_data_types(newVal) {
            if (!newVal || !Object.keys(newVal).length) {
                this.setFieldOptions();
            }
        },

    },
    computed: {
        selectedField() {
            let found = this.fct_data_types.find((element) => {
                return element.key == this.column.wp_post_custom_data_source_type;
            });
            if (found) {
                return found;
            }
            return {};
        },
        selectedFiledValueType() {
            if (this.selectedField && this.selectedField.value_type) {
                return this.selectedField.value_type;
            }
            return 'text';
        }
    },
    methods: {
        setFieldOptions() {
            this.loading = true;
            if (window.ninja_fluent_cart_custom_fields) {
                this.fct_data_types = window.ninja_fluent_cart_custom_fields;
                this.loading = false;
                return;
            }

            if (window.ninja_fluent_cart_custom_fields_completed) {
                const poll = setInterval(() => {
                    if (window.ninja_fluent_cart_custom_fields) {
                        this.fct_data_types = window.ninja_fluent_cart_custom_fields;
                        this.loading = false;
                        clearInterval(poll);
                    }
                }, 100);
                return;
            }
            window.ninja_fluent_cart_custom_fields_completed = true;

            this.$get({
                action: 'ninja_table_fluent_cart_get_custom_field_options',
                table_id: this.table_id
            })
                .then(response => {
                    window.ninja_fluent_cart_custom_fields = response.data.custom_fields;
                    this.fct_data_types = response.data.custom_fields
                })
                .fail(error => {
                    console.error('Failed to load fluent cart field options', error);
                })
                .always(() => {
                    this.loading = false;
                })
        }
    },
    mounted() {
        this.setFieldOptions();
    }
};
</script>
