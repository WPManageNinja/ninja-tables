<template>
    <div class="privacy">
        <div class="ninja_header">
            <h2>Global Appearance Settings</h2>
        </div>

        <div v-loading="fetching" class="ninja_content">
            <div class="ninja_block">
                <p>
                    The following settings will be applied to the newly created tables. Of course, You can customize the
                    appearance settings to each table level.
                </p>
            </div>
            <hr/>
            <template>
                <div class="ninja_block">
                    <div class="form_group">
                        <h3>Default Styling Library</h3>
                        <el-radio-group size="small" v-model="default_settings.css_lib">
                            <el-radio-button
                                    v-for="(tableLib, libKey) in table_styles"
                                    :key="libKey"
                                    :value="libKey">
                                {{ tableLib.title }}
                                <el-tooltip placement="top-end" effect="light" :content="tableLib.description">
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </el-radio-button>
                        </el-radio-group>
                    </div>

                    <div class="form_group">
                        <h3>Default Table Styles</h3>
                        <label
                                v-for="tableStyle in availableStyles"
                                :key="tableStyle.key"
                                :for="'table_style_'+tableStyle.key">
                            <input v-model="default_settings.css_classes" type="checkbox" name="table_styles"
                                   :value="tableStyle.key" :id="'table_style_'+tableStyle.key"/>
                            {{ tableStyle.title }}
                            <el-tooltip placement="top-end" effect="light" :content="tableStyle.description">
                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </label>
                    </div>

                    <div class="form_group">
                        <h3>Default Features</h3>
                        <label for="show_title">
                            <input v-model="default_settings.show_title" type="checkbox" value="1" id="show_title"/> {{
                            $t('Show Table Title') }}
                            <el-tooltip placement="top-end" effect="light"
                                        content="Enable this if you want to show table title in frontend">
                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </label>
                        <label for="show_description">
                            <input v-model="default_settings.show_description" type="checkbox" value="1"
                                   id="show_description"/> {{ $t('Show Table Description') }}
                            <el-tooltip placement="top-end" effect="light"
                                        content="Enable this if you want to show table description in frontend">
                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </label>
                        <label for="enable_search">
                            <input v-model="default_settings.enable_search" type="checkbox" value="1"
                                   id="enable_search"/> {{ $t('Enable the visitor to filter or search the table.')
                            }}
                        </label>
                        <label for="column_sorting">
                            <input v-model="default_settings.column_sorting" type="checkbox" value="1"
                                   id="column_sorting"/> {{ $t('Enable sorting of the table by the visitor') }}
                        </label>
                        <label><input v-model="default_settings.hide_all_borders" type="checkbox">
                            Hide All Borders
                        </label>
                    </div>

                    <div class="form_group" style="max-width: 400px">
                        <h3>Default Table Color</h3>
                        <select class="form_control" v-model="default_settings.table_color">
                            <option v-for="(colorName, colorKey) in tableColors" :key="colorKey" :value="colorKey">
                                {{ colorName }}
                            </option>
                        </select>
                    </div>

                    <div class="form_group">
                        <h3>Default Pagination Setting</h3>
                        <el-switch
                                inactive-color="gray"
                                active-text="Hide Pagination (Show all data at once)"
                                active-value="1" inactive-value="0"
                                v-model="default_settings.show_all"></el-switch>
                    </div>
                </div>

                <div class="form_group" style="max-width: 400px">
                    <label for="items_per_page">{{ $t('Pagination Items Per Page') }}</label>
                    <input id="items_per_page" class="form_control" type="number"
                           v-model="default_settings.perPage"
                           :disabled="default_settings.show_all == true || default_settings.show_all == '1'"/>
                </div>

              <div class="font-setting">
                <h3>Default Font Setting</h3>
                <div class="font form_group ">
                  <label>{{$t('Font Family')}}</label>
                  <select class="form_control" v-model="default_settings.table_font_family">
                    <option v-for="(family, key) in fontFamily" :key="key" :label="family === 'inherit' ? 'theme-font' : family" :value="family"></option>
                  </select>
                </div>
                <div class="font form_group" style="max-width: 400px">
                  <label>{{$t('Font Size')}}</label>
                  <input class="form_control" type="number" :min="1" :max="50" v-model="default_settings.table_font_size"/>
                </div>
              </div>
                <div style="margin-top: 30px" class="form-group">
                    <el-button :loading="saving" @click="store" type="primary" size="small">Update</el-button>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import {tableLibs} from '../../data/data'
    import intersection from 'lodash/intersection';
    import forEach from 'lodash/forEach'

    export default {
        name: "Privacy",
        data() {
            return {
                fetching: false,
                saving: false,
                tableLibs: {},
                default_settings: {},
                fontFamily: ['inherit', 'cursive', 'fantasy', 'monospace', 'sans-serif', 'serif', 'system-ui', 'ui-monospace', 'ui-rounded', 'ui-sans-serif', 'ui-serif']
            };
        },
        computed: {
            tableColors() {
                let lib = tableLibs();
                return lib.footable.colors;
            },
            table_styles() {
                let lib = tableLibs();
                return lib.footable.css_libs;
            },
            availableStyles() {
                let lib = this.table_styles[this.default_settings.css_lib];
                if (lib)
                    return lib.styles;
                return false;
            }
        },
        methods: {
            get() {
                this.fetching = true;
                this.$get('tables/tools/default-settings')
                    .then(response => {
                        this.default_settings = response.data.default_settings;
                    })
                    .catch(e => {
                    })
              this.fetching = false;
            },
            store() {
                this.saving = true;
                let validStyles = [];
                forEach(this.availableStyles, (style) => {
                    validStyles.push(style.key);
                });
                this.default_settings.css_classes = intersection(validStyles, this.default_settings.css_classes);
                this.$post('tables/tools/default-settings', {
                    default_settings: this.default_settings
                })
                    .then(response => {
                        this.$message.success({
                            showClose: true,
                            message: response.data.message,
                            type: "success"
                        });
                    })
                    .catch(e => {
                    })
              this.saving = false;
            }
        },
        mounted() {
            this.get();
        }
    };
</script>


