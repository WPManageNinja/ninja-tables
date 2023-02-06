<template>
    <div v-loading="loading" class="woo_table_creator">
        <div class="nt_module_header">
            <h3>
                Construct Table from Your WooCommerce Products
            </h3>
            <p class="ninja_subtitle">
                Displays products content in a searchable, sortable with Ninja Tables.
              <a target="_blank" href="https://ninjatables.com/docs/woocommerce-integration/">
                Learn more about woocommerece integration
              </a>
            </p>
        </div>
        <div class="form-group">
            <label for="name">{{ $t('Table Title') }}</label>
            <input v-model="post_title"
                   type="text" id="name" class="form-control"
                   placeholder="Enter a title to identify your table"
            >
        </div>

        <woo-nav
            :query_selections="query_selections"
            :query_conditions="query_conditions"
            />

        <div class="form-group">
            <el-button
                :loading="saving"
                @click="save()"
                type="primary"
                size="small">
                Create Products Table
            </el-button>
        </div>
    </div>
</template>

<script type="text/babel">
    import WooNav from '../TableNav/WooNav';

    export default {
        name: 'woo_product_table',
        components: {
            WooNav
        },
        data() {
            return {
                loading: false,
                saving: false,
                query_selections: {},
                post_title: '',
                query_conditions: {}
            }
        },
        props: {
            tableCreated: {
                type: Function
            },
        },
        methods: {
            save() {
                if(!this.post_title) {
                    this.$message({
                        showClose: true,
                        message: 'Please Provide a Table Title',
                        type: 'error'
                    });
                    return;
                }
                this.saving = true;
                this.$post({
                    action: 'ninja_table_woocommerece_create_table',
                    post_title: this.post_title,
                    query_selections: this.query_selections,
                    query_conditions: this.query_conditions
                })
                    .then(res => {
                        this.$message({
                            showClose: true,
                            message: res.data.message,
                            type: 'success'
                        });
                        this.tableCreated(res.data.table_id);
                    })
                    .fail(error => {
                        let message = '';
                        let messages = error.responseJSON.data.message;
                        for (let key in messages) {
                            message += ' ' + messages[key];
                        }
                        this.$message({showClose: true, message: message, type: 'error'});
                    })
                    .always(res => this.saving = false);
            },
        },
        mounted() {
        }
    }
</script>
