<template>
    <div v-loading="loading" class="ninja_modal-body">
        <h3 class="nt-modal-title">
            {{ $t('Construct Table from Your WooCommerce Products') }}
        </h3>

        <p class="nt-modal-description">
            {{ $t('Displays products content in a searchable, sortable with Ninja Tables.') }}
            <a class="nt-link" target="_blank" href="https://ninjatables.com/docs/woocommerce-integration/">
               {{ $t('Learn more about WooCommerce integration') }}
            </a>
            {{$t('or')}}
            <a class="nt-link" target="_blank" href="https://youtu.be/uIBQoLCFs_M?si=Qtp1zji5ANsGxPSD">
                {{ $t('watch tutorial here') }}
            </a>
        </p>

        <div class="my-[30px]">
            <div class="nt-form-group">
                <label class="nt-form-label">{{ $t('Table Title') }} <span class="text-[#FB3748]">*</span></label>
                <NinjaInput
                    v-model="post_title"
                    :placeholder="$t('Enter a title to identify your table')"
                />
            </div>

            <woo-nav
                :query_selections="query_selections"
                :query_conditions="query_conditions"
            />
        </div>


        <div class="nt-modal-footer">
            <NinjaButton type="secondary" @click="$emit('modalClose')" :btnText="$t('Cancel')" />
            <NinjaButton @click="save" :btnText="$t('Create Products Table')" />
        </div>
    </div>
</template>

<script type="text/babel">
    import WooNav from '../TableNav/WooNav';
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'woo_product_table',
        components: {
            NinjaButton,
            NinjaInput,
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
