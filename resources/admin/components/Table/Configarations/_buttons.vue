<template>
    <div class="ninja_Language_settings">
        <div class="ninja_header">
            <h2>CSV Export / Print Button Settings for Frontend</h2>
            <p>You can enable/disable print and csv export settings here</p>
        </div>

        <div style="width: 100%; display: block; padding: 20px" v-if="!hasPro" class="section_block text-center">
            <h3>Export CSV and Table Print is pro only features. Please purchase <b>"Ninja Tables Pro"</b> to use this feature
            </h3>
            <get-pro/>
        </div>

        <div v-else v-loading="fetching" class="ninja_style_wrapper">
            <div style="max-width: 800px" class="section_block">
                <h3>CSV Export Button Settings</h3>
                <div class="form_group">
                    <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="table_buttons.csv.status">
                        Enable CSV Export Button
                    </el-checkbox>
                </div>
                <template v-if="table_buttons.csv.status == 'yes'">
                    <div style="max-width: 500px" class="form_group">
                        <label>Button Label</label>
                        <el-input size="small" placeholder="Button Text" v-model="table_buttons.csv.label"/>
                    </div>
                    <div class="form_group">
                        <label>Button Background Color</label>
                        <el-color-picker
                                v-model="table_buttons.csv.bg_color"
                                show-alpha>
                        </el-color-picker>
                    </div>
                    <div class="form_group">
                        <label>Button Text Color</label>
                        <el-color-picker
                                v-model="table_buttons.csv.text_color"
                                show-alpha>
                        </el-color-picker>
                    </div>

                    <div class="form_group">
                        <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="table_buttons.csv.plainText">
                            Export as Plain Text
                        </el-checkbox>
                    </div>
                </template>

                <hr/>
                <h3>Print Button Settings</h3>
                <div class="form_group">
                    <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="table_buttons.print.status">Enable Print
                        Button
                    </el-checkbox>
                </div>
                <template v-if="table_buttons.print.status == 'yes'">
                    <!--
                    /**
                    * We are gonna have to find a way to include the custom filter's value
                    * to the cloned HTML. For now it's not working, so we'll leave it.
                    */
                    -->
<!--                    <div class="form_group">-->
<!--                        <el-checkbox :true-value="'yes'" :false-value="'no'" v-model="table_buttons.print.includeFilter">-->
<!--                            Include Filter or Search-->
<!--                        </el-checkbox>-->
<!--                    </div>-->

                    <div style="max-width: 500px" class="form_group">
                        <label>Button Label</label>
                        <el-input size="small" placeholder="Button Text" v-model="table_buttons.print.label"/>
                    </div>
                    <div class="form_group form_row_full">
                        <div class="form_row_half">
                            <label>Button Background Color</label>
                            <el-color-picker
                                    v-model="table_buttons.print.bg_color"
                                    show-alpha>
                            </el-color-picker>
                        </div>
                        <div class="form_row_half">
                            <div class="form_group">
                                <label>Button Text Color</label>
                                <el-color-picker
                                        v-model="table_buttons.print.text_color"
                                        show-alpha>
                                </el-color-picker>
                            </div>
                        </div>
                    </div>

                    <el-collapse accordion>
                        <el-collapse-item title="Print Screen Header" name="header">
                          <template>
                            <label>Header in each page</label>
                            <el-radio v-model="table_buttons.print.header_each_page" label="yes">Yes</el-radio>
                            <el-radio v-model="table_buttons.print.header_each_page" label="no">No</el-radio>
                          </template>
                            <wp-editor v-model="table_buttons.print.header_html" />
                        </el-collapse-item>
                        <el-collapse-item title="Print Screen Footer" name="footer">
                          <template>
                            <label>Footer in each page</label>
                            <el-radio v-model="table_buttons.print.footer_each_page" label="yes">Yes</el-radio>
                            <el-radio v-model="table_buttons.print.footer_each_page" label="no">No</el-radio>
                          </template>
                            <wp-editor v-model="table_buttons.print.footer_html" />
                        </el-collapse-item>
                    </el-collapse>

                </template>

                <hr/>
                <h3>Buttons Position</h3>
                <div class="form_group">
                    <el-radio-group size="small" v-model="table_buttons.button_position">
                        <el-radio-button
                            v-for="(button_position, position_key) in button_positions"
                            :key="position_key"
                            :label="button_position"
                            :value="position_key" />
                    </el-radio-group>
                </div>

                <div class="form_group">
                    <label>Buttons Alignment</label>
                    <el-radio-group size="small" v-model="table_buttons.button_alignment">
                        <el-radio-button
                            v-for="(button_align, align_key) in buttonAlignments"
                            :key="align_key"
                            :label="button_align"
                            :value="align_key" />
                    </el-radio-group>
                </div>
                <div v-if="hasPro" class="form_group">
                    <el-button :loading="saving" :disabled="saving" size="small" @click="saveSettings()" type="primary">Update Settings</el-button>
                </div>
            </div>
        </div>
    </div>
</template>
<script type="text/babel">

    import wpEditor from '../../../../common/_wp_editor';
    import GetPro from "../../Tools/GetPro";

    export default {
        name: 'button_settings',
        components: {
          GetPro,
            wpEditor
        },
        props: ['table_id'],
        data() {
            return {
                table_buttons: {
                    csv: {
                        status: 'no',
                        label: 'CSV',
                        all_rows: 'no'
                    },
                    print: {
                        status: 'no',
                        label: 'Print',
                        all_rows: 'no',
                        header_each_page: 'no',
                        footer_each_page: 'no',
                    }
                },
                fetching: false,
                saving: false,
                button_positions: {
                    after_search_box: 'After Search Box',
                    before_table: 'Before of the table',
                    after_table: 'Bottom of the table',
                },
                buttonAlignments: {
                    'ninja_buttons_left': 'Left',
                    'ninja_buttons_center': 'Center',
                    'ninja_buttons_right': 'Right'
                },
                hasPro: !!window.ninja_table_admin.hasPro
            }
        },
        methods: {
            getSettings() {
                this.fetching = true;
                this.$get('settings/' + this.table_id + '/button')
                    .then(response => {
                        this.table_buttons = response.data.button_settings;
                      this.fetching = false;
                    })
                    .catch(error => {
                      this.fetching = false;
                    })
            },
            saveSettings() {
                this.saving = true;
                let data = {
                  table_id: this.table_id,
                  button_settings: this.table_buttons
                };
                this.$put('settings/' + this.table_id + '/button', data)
                    .then(response => {
                        this.$message({
                            showClose: true,
                            message: response.data.message,
                            type: 'success'
                        });
                      this.saving = false;
                    })
                    .catch(error => {
                      this.saving = false;
                    })
            }
        },
        mounted() {
            this.getSettings();
        }
    }
</script>
