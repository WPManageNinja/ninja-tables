<template>
    <div>
        <p>
            Data Transformer is a powerful tool where you can concat any column values easily into any valid html and show as computed value.
        </p>
        <el-input
          type="textarea"
          :rows="4"
          :placeholder="placeholder"
          :disabled="!hasPro"
          v-model="column.transformed_value"
        ></el-input>
        <ninja-premium-notice v-if="!hasPro" highlight="Transform Column Value"></ninja-premium-notice>

        <div class="ninja_instruction">
            <el-checkbox :disabled="!hasPro" :true-value="'yes'" :false-value="'no'" v-model="settings.formula_support">Enable Excel Formula support for Transform Value</el-checkbox>
            <div v-show="settings.formula_support == 'yes'">
                <p>Note: Excel formuala is an experimental feature so all formulas may not work. We are improving this feature day by day so please don't be mad if some formulas don't work properly.</p>

                <el-button size="small" @click="show_formulas = true">Show Formulas</el-button>
                <el-dialog
                    title="Supported Excel Formullas"
                    v-model="show_formulas"
                    width="30%">
                    <ul style="margin: 0px 20px; padding-top: 20px">
                        <li v-for="line_item in supported_formullas">{{line_item}}</li>
                    </ul>
                </el-dialog>
            </div>
        </div>

        <div class="ninja_instruction">
            <p>You can use the following Reference Shortcode Values to transform your cell value</p>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Column Title</th>
                        <th>Reference Shortcode</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="column in columns" :key="column.key">
                        <td>{{ column.name }}</td>
                        <td><span v-text="'{{row.' + column.key + '}}'"></span></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <p>You may <a href="https://wpmanageninja.com/docs/ninja-tables/configuring-tables/value-transformation/" target="_blank">check the documentation here.</a></p>
            <p style="font-weight: bold" v-show="settings.formula_support == 'yes'">You can use any Excel formula into the transform value box</p>
        </div>

    </div>
</template>

<script>
    import ninja_alert from '../../includes/alert';
    import NinjaPremiumNotice from '../../includes/PremiumNotice';
    import parser from '../../../../public/js/parser';
    import { useEventBus } from '../../../eventBus';


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
