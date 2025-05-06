<template>
    <div v-loading="loading" class="wp_posts_dynamic_field">
        <h4>{{ $t('Dynamic Post Data Settings') }}</h4>
        <hr />
        <template v-if="column.source_type == 'custom'">
            <el-form-item>
                <template #label>
                    {{ $t("Field Type") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Field Type</h3>
                            <p>Select The field type you want to populate for each row</p>
                        </template>

                        <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>
                <el-select
                        style="width: 90%"
                        v-model="column.wp_post_custom_data_type"
                        placeholder="Select Field"
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

                        <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>
                <template v-if="selectedFiledValueType == 'options'">
                    <el-select
                            style="width: 90%"
                            v-model="column.wp_post_custom_data_value"
                            :placeholder="selectedField.placeholder"
                            size="small"
                    >
                        <el-option
                                v-for="type in selectedField.options"
                                :value="type"
                                :label="type"
                                :key="type"
                        ></el-option>
                    </el-select>
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
        </template>

        <template v-if="column.source_type == 'post_data' || (column.source_type == 'custom' && column.wp_post_custom_data_type == 'featured_image')">
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
                        <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>

                <el-checkbox v-if="column.original_name == 'post_author'" v-model="column.permalinked" :true-value="'yes'" :false-value="'no'">Link to Author Permalink</el-checkbox>

                <el-checkbox v-else="column.original_name != 'post_author'" v-model="column.permalinked" :true-value="'yes'" :false-value="'no'">Link to post permalink</el-checkbox>
            </el-form-item>

            <template v-if="column.permalinked == 'yes'">
                <el-form-item v-if="column.original_name == 'post_author'">
                    <template #label>
                        {{ $t("Permalink Action") }}
                        <el-tooltip class="item" placement="bottom-start" effect="light">
                            <template #content>
                                <h3>Permalink Action Type</h3>
                                <p>
                                    Enable this if you want to make the author as table filter action. So when user click on those filters then they will see only the selected author posts.
                                </p>
                            </template>
                            <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </template>

                    <el-checkbox v-model="column.filter_permalinked" :true-value="'yes'" :false-value="'no'">Make Taxonomies as Table Filter</el-checkbox>
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
                            <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </template>

                    <el-checkbox v-model="column.permalink_target" :true-value="'_blank'" :false-value="''">Open link to new tab</el-checkbox>
                </el-form-item>
            </template>
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
                        <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                    </el-tooltip>
                </template>

                <el-checkbox v-model="column.permalinked" :true-value="'yes'" :false-value="'no'">Link to Taxonomy</el-checkbox>
            </el-form-item>

            <el-form-item>
                <template #label>
                    {{ $t("Taxonomy Separator") }}
                    <el-tooltip class="item" placement="bottom-start" effect="light">
                        <template #content>
                            <h3>Taxonomy Separator</h3>
                            <p>Taxonomy Separator for Multiple Items</p>
                        </template>

                        <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
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
                            <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </template>
                    <el-checkbox v-model="column.filter_permalinked" :true-value="'yes'" :false-value="'no'">Make Taxonomies as Table Filter</el-checkbox>
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
                            <el-icon :color="tooltipIconColorCode"><InfoFilled /></el-icon>
                        </el-tooltip>
                    </template>
                    <el-checkbox v-model="column.permalink_target" :true-value="'_blank'" :false-value="''">Open link to new tab</el-checkbox>
                </el-form-item>
            </template>
        </template>
    </div>
</template>

<script>
    import {InfoFilled} from "@element-plus/icons-vue";

    export default {
        name: "WPPostDynamicColumn",
        components: {
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
                post_data_types: [],
                table_id: this.$route.params.table_id
            };
        },
        watch: {
            'column.wp_post_custom_data_type': function () {
                this.column.wp_post_custom_data_value = '';
            }
        },
        computed: {
            selectedField() {
                let found = this.post_data_types.find((element) => {
                    return element.key == this.column.wp_post_custom_data_type;
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
        methods: {
            setFieldOptions() {
                this.loading = true;
                if(window.ninja_wp_posts_custom_fields) {
                    this.post_data_types = window.ninja_wp_posts_custom_fields;
                    this.loading = false;
                    return;
                }

                this.$get({
                    action: 'ninja_table_wp-posts_get_custom_field_options',
                    table_id: this.table_id
                })
                    .then(response => {
                        window.ninja_wp_posts_custom_fields = response.data.custom_fields;
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
