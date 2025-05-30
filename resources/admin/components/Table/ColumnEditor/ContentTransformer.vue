<template>
    <div class="ninja_modal-body ">
        <div class="nt-instruction mb-[20px]" v-if="!hasPro">
            <h3 class="nt-modal-title mb-3">{{ $t('Transform Column Value') }}</h3>
            <div class="text-[14px] font-[400] text-[#525866]">
                {{ $t('Use simple HTML code to transform a column’s data value. Transform value is a Pro feature.') }}
                <a class="nt-link" target="_blank" href="https://ninjatables.com/docs/transform-value/">
                    {{ $t('View documentation') }}
                </a>
            </div>
            <div class="mt-4">
                <GetPro />
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
                <p class="nt-modal-description mb-2">
                    {{ $t(`Note: Excel formula is an experimental feature so all formulas may not work. We are improving this feature day by day so please don't be mad if some formulas don't work properly.`) }}
                </p>

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
                    align-center
                    width="60%">

                    <div class="py-5 px-6 h-[400px] overflow-y-scroll scrollbar-always-visible">
                        <ul class="grid grid-cols-5 gap-2">
                            <li v-for="line_item in supported_formullas">{{line_item}}</li>
                        </ul>
                    </div>

                    <hr>

                    <div class="flex justify-end py-4 px-5">
                        <NinjaButton
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
            <p>You may <a class="text-[#335CFF]" href="https://ninjatables.com/docs/transform-value/" target="_blank">check the documentation here.</a></p>
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

<style scoped>
.scrollbar-always-visible {
    overflow-y: scroll !important;
    scrollbar-width: auto;
}

/* For WebKit browsers (Chrome, Safari) */
.scrollbar-always-visible::-webkit-scrollbar {
    width: 8px;
}

.scrollbar-always-visible::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.scrollbar-always-visible::-webkit-scrollbar-thumb {
    background: #a2a1a1;
    border-radius: 8px;
}

.scrollbar-always-visible::-webkit-scrollbar-thumb:hover {
    background: #828282;
    border-radius: 8px;
}
</style>
