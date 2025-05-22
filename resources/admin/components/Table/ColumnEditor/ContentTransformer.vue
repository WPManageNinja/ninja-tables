<template>
    <div class="ninja_modal-body ">
        <div class="nt-instruction mb-[20px]" v-if="!hasPro">
            <h3 class="nt-modal-title">Transform Column Value</h3>
            <p class="nt-modal-description">
                Transform Value is a Premium feature. Get Transform Column Value, unlimited customizations, data filters, professional looks, Many More Integrations and so many things from the Pro version.
            </p>
            <div class="py-2">
                <get-pro />
            </div>
        </div>

        <p class="nt-modal-description">
            Data Transformer is a powerful tool where you can concat any column values easily into any valid html and show as computed value.
        </p>
        <el-input
          type="textarea"
          :rows="4"
          :placeholder="placeholder"
          :disabled="!hasPro"
          v-model="column.transformed_value"
          class="mt-2"
        ></el-input>

<!--        <ninja-premium-notice v-if="!hasPro" highlight="Transform Column Value"></ninja-premium-notice>-->

        <div class="mt-4">

           <div class="flex gap-2 items-center">
               <el-switch
                   size="small"
                   :active-value="'yes'"
                   :inactive-value="'no'"
                   v-model="settings.formula_support"
                   :disabled="!hasPro"
               />
               <p class="tex-[14px] font-[400]">{{ $t('Enable Excel Formula support for Transform Value') }}</p>
           </div>

            <div class="bg-[#EBF1FF] p-4 mt-2 rounded-[8px]" v-show="settings.formula_support == 'yes'">
                <p class="nt-modal-description mb-2">Note: Excel formula is an experimental feature so all formulas may not work. We are improving this feature day by day so please don't be mad if some formulas don't work properly.</p>

                <NinjaButton
                    size="small"
                    type="secondary"
                    @click.prevent="show_formulas = true"
                    :btnText="$t('Show Formulas')"
                />

                <el-dialog
                    class="ninja_create-table-modal"
                    title="Supported Excel Formulas"
                    v-model="show_formulas"
                    width="30%">
                    <ul style="margin: 0px 20px; padding-top: 20px">
                        <li v-for="line_item in supported_formullas">{{line_item}}</li>
                    </ul>
                    <div class="flex justify-end p-4">
                        <NinjaButton
                            size="small"
                            type="secondary"
                            @click.prevent="show_formulas = false"
                            :btnText="$t('Close')"
                        />
                    </div>
                </el-dialog>
            </div>
        </div>

        <div class="my-[24px]">
            <p class="nt-modal-description">You can use the following Reference Shortcode Values to transform your cell value</p>
            <div>
                <el-table :data="columns" border  class="nt-inner-table">
                    <el-table-column prop="name" label="Column Title" />
                    <el-table-column label="Reference Shortcode">
                        <template #default="scope">
                            <span v-text="'{{row.' + scope.row.key + '}}'"></span>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <p>You may <a class="text-[#335CFF]" href="https://wpmanageninja.com/docs/ninja-tables/configuring-tables/value-transformation/" target="_blank">check the documentation here.</a></p>
            <p style="font-weight: bold" v-show="settings.formula_support == 'yes'">You can use any Excel formula into the transform value box</p>
        </div>

    </div>
</template>

<script>
    import ninja_alert from '../../includes/alert';
    import NinjaPremiumNotice from '../../includes/PremiumNotice';
    import parser from '../../../../public/js/parser';
    import { useEventBus } from '../../../eventBus';
    import NinjaButton from "../../../@ui-utils/NinjaButton.vue";
    import GetPro from "../../Tools/GetPro.vue";


    export default {
        name: 'ContentTransformer',
        props: {
            column: {
                type: Object,
                default: () => ({})
            },
            columns: {
                type: Array,
                default: () => []
            },
            settings: {
                type: Object
            }
        },
        components: {
            GetPro,
            NinjaButton,
            ninja_alert,
            NinjaPremiumNotice
        },
        watch: {
            'settings.formula_support': function () {
                this.storeSettings();
            }
        },
        data() {
            return {
                bus : useEventBus(),
                hasPro: !!window.ninja_table_admin.hasPro,
                tableId: this.$route.params.table_id,
                doingAjax: false,
                supported_formullas: [],
                show_formulas: false
            };
        },
        computed: {
            placeholder() {
                let placeholder = 'Please Input Transform Values (HTML supported)';
                if(this.settings.formula_support == 'yes') {
                    placeholder = 'Please Input Transform Values (HTML and Excel Formula are supported)'
                }
                return placeholder;
            }
        },
        methods: {
            storeSettings() {
                this.bus.emit('tableDoingAjax', true);
                this.doingAjax = true;
                let data = {
                    table_id: this.tableId,
                    table_settings: this.settings
                };
                this.$post('settings/'+this.tableId, data)
                    .then((res) => {
                    })
                    .catch((error) => {

                    })
                    .finally(() => {
                        this.bus.emit('tableDoingAjax', false);
                        this.doingAjax = false;
                    });
            },
        },
        mounted() {
          if (this.hasPro) {
            this.supported_formullas = parser.getSupportedFormulas();
          }
        }
    };
</script>
