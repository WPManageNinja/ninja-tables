<template>
    <div v-loading="btnLoading" :class="(is_editing && cell_editable) ? 'nt_cell_editing' : 'nt_cell'">
        <div v-if="is_editing && cell_editable" class="cell_editing">
            <may-be-select
                @blur="maybeSave('blur')"
                size="mini"
                v-if="column.data_type == 'selection'"
                :newColumn="row['values']"
                :column="column"></may-be-select>
            <ninja-date-picker
                @blur="maybeSave('blur')"
                only_picker="yes"
                v-focus
                v-else-if="column.data_type == 'date'"
                :column="column"
                :new_column="row['values']">
            </ninja-date-picker>
            <textarea
                v-else-if="column.data_type == 'textarea'"
                v-focus
                @blur="maybeSave('blur')"
                :key="column.key"
                class="nt_inline_textarea"
                v-model="row['values'][column.key]"
            ></textarea>
            <input
                v-else
                v-focus
                :key="column.key"
                @blur="maybeSave('blur')" @keyup.enter="maybeSave('submit')"
                class="nt_cell_input"
                v-model="row['values'][column.key]"
                type="text" />
        </div>
        <div
            v-else
            :title="getTitle(row['values'][column.key])"
            class="cell-content"
            @click="is_editing = true"
            :class="(cell_editable) ? 'nt_cell_editable' : ''"
            v-html="renderTableCell(row['values'][column.key], column, row['values'])">
        </div>
    </div>

</template>

<script type="text/babel">
    import mustache from '../../../public/js/_tiny_mustace'
    import unescape from 'lodash/unescape';
    import parser from '../../../public/js/parser';
    import mayBeSelect from '../../../common/_maybeMultiSelect';
    import NinjaDatePicker from '../Extras/_NinjaDatePicker'
    import _find from "lodash/find";
    import { euFormat } from '../../../common/helpers';


    export default {
        name: 'show_cell',
        props: ['table_id', 'row', 'columns', 'column', 'formula_support', 'is_editable'],
        components: {
            mayBeSelect,
            NinjaDatePicker
        },
        data() {
            return {
                is_editing: false,
                btnLoading: false,
                inital_value: ''
            }
        },
        methods: {
            saveOnly() {

            },
            maybeSave(type) {
                if(type == 'change') {
                    return;
                }

                if (this.btnLoading) {
                    return;
                }

                let columnKey = this.column.key;
                let columnValue = this.row.values[columnKey];

                if(this.inital_value == columnValue) {
                    this.is_editing = false;
                    return;
                }

                this.inital_value = JSON.stringify(columnValue);

                let data = {
                    row_id: this.row.id,
                    column_key: columnKey,
                    column_value: columnValue
                };

                this.btnLoading = true;
                this.is_editing = false;

                this.$post('tables/'+this.table_id+'/item/update', data)
                    .then((response) => {
                        this.$message.success({
                            showClose: true,
                            message: response.data.message,
                            type: "success"
                        });
                    })
                    .catch((error) => {
                        this.$message.error('Failed to update!');
                    })
                    .finally(() => {
                        this.btnLoading = false; // Move this to finally to ensure it runs
                    });
            },
            renderTableCell(value, column, row) {
                if (column.data_type == 'image') {
                    if (typeof value != 'object' || !value) {
                        return value;
                    }
                    if (column.link_type == 'file_download') {
                        return `<a target="${column.link_target}" href="${value.permalink}">${column.download_button}</a>`;
                    } else {
                        if (value.image_thumb) {
                            return `<img src="${value.image_thumb}" />`;
                        }
                        return '';
                    }
                } else if (column.data_type == 'button') {
                    if (!value) {
                        return '';
                    }

                    return `<a ${column.force_download} target="${column.link_target}" rel="${column.relAttributes ? column.relAttributes.join(' ') : ''}" class="nt_btn ${column.btn_extra_class}" style="color: ${column.btn_text_color}; background-color: ${column.btn_bg_color}; border-color: ${column.btn_border_color}" href='${value}'>${column.button_text}</a>`;
                }

                if (Array.isArray(value)) {
                    value = value.join(', ');
                }

                const hasTransformedValue = column.transformed_value && column.transformed_value.trim();

                if (hasTransformedValue) {
                    value = this.getShortcodes(value, column, this.row, this.columns);

                    if (this.formula_support == 'yes') {
                        // Parse formulas If has any
                        value = parser.parse(value, column.decimalSeparator);

                        if (column.decimalSeparator == ',') {
                            value = euFormat(value);
                        }

                        return value;
                    }
                }

                return value;
            },
            getShortcodes(str, column, row, columns) {
                row = Object.assign({}, row.values);
                let transValue = column.transformed_value;

                if (row.value_transformation_complete != true) {
                    jQuery.each(row, (key, item) => {
                        const col = _find(columns, ['key', key]);

                        if (col && col.data_type == 'number') {
                            if (col.decimalSeparator == '.') {
                                row[key] = row[key] ? row[key].replace(/\,/g,'') : row[key];
                            } else {
                                row[key] = row[key] ? row[key].replace(/\./g,'').replace(/\,/g,'.') : row[key];
                            }
                        }
                    })

                    row.value_transformation_complete = true;
                }

                if (transValue.indexOf('{{') != -1) {
                    transValue = unescape(
                        mustache(transValue, {row: row})
                    );
                }

                // For legacy placeholder (with single curly brace)
                let singleBraces = transValue.match(/{row.([^\}]*)}/g);

                if (singleBraces) {
                    $.each(singleBraces, (index, match) => {
                        let rowKey = match.substring(5, match.length - 1);
                        transValue = transValue.replace(match, row[rowKey] || '');
                    });
                }

                return transValue;
            },
            getTitle(str) {
                if (this.cell_editable) {
                    return 'Click to edit';
                } else {
                    return this.isHtml(str) ? '' : str;
                }
            },
            isHtml(str) {
                let doc = new DOMParser().parseFromString(str, "text/html");
                return Array.from(doc.body.childNodes).some(node => node.nodeType === 1);
            }
        },
        computed: {
            cell_editable()
            {
                if(!this.is_editable) {
                    return false;
                }
                let nonEditableTypes = ['image', 'button', 'html'];

                if(nonEditableTypes.indexOf(this.column.data_type) == -1) {
                    return true;
                }
                return false;
            }
        },
        mounted() {
            if(this.is_editable) {
                if(this.row.values[this.column.key]) {
                    this.inital_value = JSON.stringify(this.row.values[this.column.key]);
                }
            }
        }
    }
</script>
