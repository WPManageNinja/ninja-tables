<template>
    <div :class="(only_picker == 'yes') ? 'ninja_only_picker' : 'ninja_date_picker'">
        <input
                :placeholder="column.dateFormat"
                type="text"
                size="small"
                v-if="only_picker != 'yes'"
                v-model="new_column[column.key]"
                :id="slugify(column.key)"
                class="form-control"
        >
        <el-date-picker
                :type="type"
                size="small"
                @blur="$emit('blur')"
                v-model="new_column[column.key]"
                :value-format="elementFormat"
                :format="elementFormat"
                placeholder="Pick a day">
        </el-date-picker>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'ninjaDatePicker',
        props: ['column', 'new_column', 'only_picker'],
        computed: {
            elementFormat() {
                let originalFormat  = this.column.dateFormat;
                let defaultTimeFormat  = 'h:m:s';
                let updatedFormat = '';
                if(originalFormat == 'DD-MMM-YY') {
                  updatedFormat = 'dd-MMM-yy';
                } else {
                  updatedFormat = originalFormat.replace(/Y/g, 'y').replace(/D/g, 'd');
                }

                if (this.type === 'date') {
                  return updatedFormat;
                } else {
                  if (this.column.timeFormat) {
                    defaultTimeFormat = this.column.timeFormat;
                  }
                  return updatedFormat +' '+defaultTimeFormat;
                }
            },
            type() {
                return this.column.showTime === 'yes' ? 'datetime' : 'date';
            }
        },
        methods: {
            slugify(text) {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-')           // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                    .replace(/^-+/, '')             // Trim - from start of text
                    .replace(/-+$/, '');            // Trim - from end of text
            },
        },
    }
</script>

<style lang="scss">
    .ninja_date_picker {
        > .form-control {
            width: 90%;
            float: left;
        }
        > .el-date-editor {
            width: 8px !important;
            padding: 0px;
            margin: 0px;
            cursor: pointer;
            .el-input__inner {
                width: 10px !important;
                padding: 15px;
                background: rgb(128, 128, 128);
                height: 34px;
            }
        }
    }
</style>
