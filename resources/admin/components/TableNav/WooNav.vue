<template>
    <div v-loading="loading" class="ntn_query_selections">
        <div v-for="(term,term_name) in query_terms" class="nt_each_selection">
            <div class="nt_query_header">
                <h3>{{term.title}}</h3>
                <p>{{term.description}}</p>
            </div>
            <div class="nt_query_body">
                <el-checkbox-group v-model="query_selections[term_name]">
                    <el-checkbox
                        v-for="taxonomy in term.terms"
                        :key="taxonomy.slug"
                        :label="taxonomy.slug">{{taxonomy.name}} ({{taxonomy.count}})
                    </el-checkbox>
                </el-checkbox-group>
            </div>
        </div>
        <div class="nt_each_selection">
            <el-checkbox
                :true-label="'yes'"
                :false-label="'no'"
                v-model="query_conditions.hide_out_of_stock">
                Hide <b>out of stocks</b> items
            </el-checkbox>
        </div>
        <div class="nt_each_selection">
            <label>
                Initial Order By
                <el-select size="mini" v-model="query_conditions.order_by" placeholder="Order By">
                    <el-option
                        v-for="(item,item_key) in product_orders"
                        :key="item_key"
                        :label="item"
                        :value="item_key">
                    </el-option>
                </el-select>
                <el-select
                    v-show="query_conditions.order_by && query_conditions.order_by != 'random'"
                    size="mini"
                    v-model="query_conditions.order_by_type"
                    placeholder="Order By Type">
                    <el-option
                        label="Assending Way"
                        value="ASC">
                    </el-option>
                    <el-option
                        label="Descending Way"
                        value="DESC">
                    </el-option>
                </el-select>
            </label>

        </div>
    </div>
</template>

<script type="text/babel">
    import each from 'lodash/each';

    export default {
        name: 'woo_conditions',
        props: ['query_selections', 'query_conditions'],
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
                }
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
                                this.$set(this.query_selections, item_name, []);
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
        },
        mounted() {
            this.getWooSettings();
        }
    }
</script>
