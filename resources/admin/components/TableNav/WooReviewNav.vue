<template>
    <div v-loading="loading" class="ntn_query_selections">
        <div v-for="(term,term_name) in query_terms" class="nt_each_selection">
            <WooTypeSelection
                :term="term"
                :termName="term_name"
                :querySelections="query_selections[term_name]"
                @selectionChange="handleSelectionChange($event, term_name)"
            />
        </div>

        <div class="flex flex-col">
            <label class="nt-form-label mb-2">{{ $t('Reviews Status') }}</label>

            <div>
                <el-radio-group v-model="query_conditions.status" class="ninja_tables_radio_group">
                    <el-radio border value="all" label="All"/>
                    <el-radio border value="approved" label="Approved"/>
                    <el-radio border value="unapproved" label="Unapproved"/>
                </el-radio-group>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import each from 'lodash/each';
    import WooTypeSelection from './_WooTypeSelection.vue';

    export default {
        name: 'WooReviewNav',
        props: ['query_selections', 'query_conditions'],
        components: {
            WooTypeSelection
        },
        data() {
            return {
                query_terms: [],
                loading: false,
            }
        },
        watch: {
            query_conditions: {
                handler(newVal) {
                    if (newVal && !newVal.status) {
                        newVal.status = 'all';
                    }
                },
                immediate: true
            }
        },
        methods: {
            getWooSettings() {
                this.loading = true;
                this.$get({
                    action: 'ninja_table_woocommerece_get_reviews_options'
                })
                    .then(response => {
                        let terms = response.data.query_terms;
                        each(terms, (term, item_name) => {
                            if (!this.query_selections[item_name]) {
                                this.query_selections[item_name] = [];
                            }
                        });
                        this.query_terms = terms;
                    })
                    .fail(error => {

                    })
                    .always(() => {
                        this.loading = false;
                    })
            },

            handleSelectionChange(selections, termName) {
                this.query_selections[termName] = selections;
            },
        },
        mounted() {
            if (!this.query_conditions.status) {
                this.query_conditions.status = 'all';
            }
            this.getWooSettings();
        }
    }
</script>
