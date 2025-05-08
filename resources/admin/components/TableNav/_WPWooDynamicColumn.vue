<template>
    <div v-loading="loading" class="wp_posts_dynamic_field">
        <h4>{{ $t('Dynamic Column Data Settings') }}</h4>
        <hr />
        <el-form-item>
            <template #label>
                {{ $t("Field Type") }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Field Type</h3>
                        <p>Select The field type you want to populate for each row</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-select
                style="width: 90%"
                v-model="column.wp_post_custom_data_source_type"
                placeholder="Select Data Type"
                size="small"
            >
                <el-option
                    v-for="type in post_data_types"
                    :value="type.key"
                    :disabled="type.disabled"
                    :label="type.label"
                    :key="type.key"
                ></el-option>
            </el-select>
        </el-form-item>
        <el-form-item>
            <template #label>
                {{ $t("Field Value") }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Field Value</h3>
                        <p>Provide the column value for your corresponding value type select</p>
                    </template>

                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <template v-if="selectedFiledValueType == 'options'">
                <el-select
                    style="width: 90%"
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

                <div v-if="column.wp_post_custom_data_source_type == 'featured_image'">
                    <el-radio-group v-model="column.image_permalink_type">
                        <el-radio value="lightbox" label="Make image as lightbox" />
                        <el-radio value="linked" label="Link to Product page" />
                        <el-radio value="" label="None" />
                    </el-radio-group>
                    <p v-show="column.image_permalink_type == 'linked'">
                        <el-checkbox :true-value="'_blank'" :false-value="'no'" v-model="column.permalink_target">Open Link in new tab</el-checkbox>
                    </p>
                </div>

            </template>

            <el-input
                v-else
                :type="selectedFiledValueType"
                :placeholder="selectedField.placeholder"
                size="small"
                v-model="column.wp_post_custom_data_value"
            >
            </el-input>

            <div class="ninja_instruction" v-if="selectedField && selectedField.instruction">
                <p v-html="selectedField.instruction"></p>
                <p v-if="selectedField.learn_more_url">
                    <a target="_blank" :href="selectedField.learn_more_url">
                        {{selectedField.learn_more_text}}
                    </a>
                </p>
            </div>
        </el-form-item>

        <el-form-item v-if="column.wp_post_custom_data_key == 'buy_now_button'">
            <template #label>
                {{ $t("Buy Now Button Text") }}
                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <template #content>
                        <h3>Buy Now Button Text</h3>
                        <p>Provide Buy Now Button Text</p>
                    </template>
                    <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                </el-tooltip>
            </template>
            <el-input
                placeholder="Buy Now Button Text"
                size="small"
                v-model="column.buy_now_button_text"
            >
            </el-input>
        </el-form-item>

        <template v-if="column.wp_post_custom_data_key == 'post_title' || (column.source_type == 'custom' && column.wp_post_custom_data_type == 'featured_image')">
            <el-form-item>
                <template #label>
                    {{ $t("Link") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Link to Post/Author Permalink</h3>
                            <p>
                                Enable this if you want to link to post/Author permalink
                            </p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>

                <el-checkbox v-if="column.wp_post_custom_data_key == 'post_author'" v-model="column.permalinked" :true-value="'yes'" :false-value="'no'" value="yes" label="Link to Author Permalink"></el-checkbox>
                <el-checkbox v-else v-model="column.permalinked" :true-value="'yes'" :false-value="'no'" value="yes" label="Link to post permalink"></el-checkbox>
                <el-checkbox v-model="column.permalink_target" true-label="_blank" false-label="" value="_blank" label="Open link to new tab"></el-checkbox>
            </el-form-item>
        </template>

        <template v-else-if="column.source_type == 'tax_data'">
            <el-form-item>
                <template #label>
                    {{ $t("Link") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Link to Taxonomy Permalink</h3>
                            <p>
                                Enable this if you want to link to Taxonomy permalink
                            </p>
                        </template>
                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>

                <el-checkbox v-model="column.permalinked" :true-value="'yes'" :false-value="'no'" value="yes" label="Link to Taxonomy"></el-checkbox>
            </el-form-item>

            <el-form-item>
                <template #label>
                    {{ $t("Taxonomy Separator") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Taxonomy Separator</h3>
                            <p>Taxonomy Separator for Multiple Items</p>
                        </template>

                        <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>

                <el-input
                    placeholder="Enter Value"
                    size="small"
                    v-model="column.taxonomy_separator"
                ></el-input>
            </el-form-item>

            <template v-if="column.permalinked == 'yes'">
                <el-form-item>
                    <template #label>
                        {{ $t("Permalink Action") }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3>Permalink Action Type</h3>
                                <p>
                                    Enable this if you want to make the taxonomies as table filter action. So when user click on those filters then they will see only those type of posts.
                                </p>
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </template>
                    <el-checkbox v-model="column.filter_permalinked" :true-value="'yes'" false-label="" value="yes" label="Make Taxonomies as Table Filter"></el-checkbox>
                </el-form-item>
                <el-form-item v-if="column.filter_permalinked != 'yes'">
                    <template #label>
                        {{ $t("Open Link To New tab") }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3>Open Link To New tab</h3>
                                <p>
                                    Enable this if you want to open the links to new tab
                                </p>
                            </template>
                            <el-icon class="tooltip-icon-color"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </template>
                    <el-checkbox v-model="column.permalink_target" true-label="_blank" false-label="" value="_blank" label="Open link to new tab"></el-checkbox>
                </el-form-item>
            </template>
        </template>
    </div>
</template>>

<script type="text/babel">
    import {InfoFilled} from "@element-plus/icons-vue";

    export default {
        name: "WPWooDynamicColumn",
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
                post_data_types: [],
                table_id: this.$route.params.table_id
            };
        },
        watch: {
            'column.wp_post_custom_data_source_type': function () {
                // Find selected type
                this.column.source_type = this.selectedField.source_type;

                if(this.selectedField.source_type == 'tax_data') {
                    this.column.taxonomy_separator = ', ';
                }

                this.column.wp_post_custom_data_key = '';
            }
        },
        computed: {
            selectedField() {
                let found = this.post_data_types.find((element) => {
                    return element.key == this.column.wp_post_custom_data_source_type;
                });
                if(found) {
                    return found;
                }
                return {};
            },
            selectedFiledValueType() {
                if(this.selectedField && this.selectedField.value_type) {
                    return this.selectedField.value_type;
                }
                return 'text';
            }
        },
        components: {
            InfoFilled
        },
        methods: {
            setFieldOptions() {
                this.loading = true;
                if(window.ninja_wp_woo_custom_fields) {
                    this.post_data_types = window.ninja_wp_woo_custom_fields;
                    this.loading = false;
                    return;
                }

                this.$get({
                    action: 'ninja_table_wp_woo_get_custom_field_options',
                    table_id: this.table_id
                })
                    .then(response => {
                        window.ninja_wp_woo_custom_fields = response.data.custom_fields;
                        this.post_data_types = response.data.custom_fields
                    })
                    .fail(error => {

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
