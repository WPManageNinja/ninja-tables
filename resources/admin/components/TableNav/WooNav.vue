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

        <div class="nt_each_selection">
            <el-checkbox
                :true-value="'yes'"
                :false-value="'no'"
                v-model="query_conditions.hide_out_of_stock">
                Hide <b>out of stocks</b> items
            </el-checkbox>
        </div>
        <div class="nt_each_selection mt-4">
            <label class="nt-form-label mb-2">Initial Order By</label>
            <div class="flex justify-start items-center gap-4">
                <el-select
                    class="ninja-select"
                    v-model="query_conditions.order_by"
                    placeholder="Order By"
                >
                    <el-option
                        v-for="(item,item_key) in product_orders"
                        :key="item_key"
                        :label="item"
                        :value="item_key">
                    </el-option>
                </el-select>

                <el-select
                    class="ninja-select"
                    v-show="query_conditions.order_by && query_conditions.order_by != 'random'"
                    v-model="query_conditions.order_by_type"
                    placeholder="Order By Type"
                >
                    <el-option
                        label="Ascending Way"
                        value="ASC">
                    </el-option>
                    <el-option
                        label="Descending Way"
                        value="DESC">
                    </el-option>
                </el-select>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import each from 'lodash/each';
    import WooTypeSelection from './_WooTypeSelection.vue';

    export default {
        name: 'woo_conditions',
        props: ['query_selections', 'query_conditions'],
        components: {
            WooTypeSelection
        },
        data() {
            return {
                query_terms: [],
                loading: false,
                product_orders: {
                    post_title: 'Product Title',
                    date: 'Date',
                    menu_order: 'Menu Order',
                    average_rating: 'Average Rating',
                    price: 'price',
                    popularity: 'Popularity (Sales)',
                    random: 'Random'
                },
            }
        },
        methods: {
            getWooSettings() {
                this.loading = true;
                this.$get({
                    action: 'ninja_table_woocommerece_get_options'
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
            this.getWooSettings();
        }
    }
</script>
