<template>
    <div>
        <div v-if="only_picker === 'yes'" class="ninja_only_picker">
            <el-date-picker
                :type="type"
                size="small"
                @blur="$emit('blur')"
                v-model="new_column[column.key]"
                :value-format="elementFormat"
                :format="elementFormat"
                :picker-options="pickerOptions"
                placeholder="Pick a day"
            />
        </div>

        <div v-else class="ninja_date_picker">
            <el-input
                :placeholder="column.dateFormat"
                type="text"
                v-model="new_column[column.key]"
                :id="slugify(column.key)"
                class="nt-date-input"
            >

                <template #prefix>
                    <el-icon><Calendar /></el-icon>
                </template>

                <template #append>
                    <el-date-picker
                        :type="type"
                        size="small"
                        style="width: 150px"
                        @blur="$emit('blur')"
                        v-model="new_column[column.key]"
                        :value-format="elementFormat"
                        :format="elementFormat"
                        :picker-options="pickerOptions"
                        placeholder="Pick a day"
                    >
                        <template #prefix>
                            <el-icon><Calendar /></el-icon>
                        </template>
                    </el-date-picker>
                </template>
            </el-input>
        </div>
    </div>
</template>

<script>
    import {Calendar} from "@element-plus/icons-vue";

    export default {
        name: 'ninjaDatePicker',
        components: {Calendar},
        props: ['column', 'new_column', 'only_picker'],
        computed: {
            elementFormat() {
                let originalFormat  = this.column.dateFormat;
                let defaultTimeFormat  = 'h:m:s';

                // this code snippets is creating a problem after updating to element-plus. without this code, it's working. keep it for now
                // let updatedFormat = '';
                // if(originalFormat == 'DD-MMM-YY') {
                //   updatedFormat = 'dd-MMM-yy';
                // } else {
                //   updatedFormat = originalFormat.replace(/Y/g, 'y').replace(/D/g, 'd');
                // }
                let updatedFormat = originalFormat;

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
            },
            pickerOptions() {
                return {
                    firstDayOfWeek: parseInt(this.column?.firstDayOfWeek) || 7,
                };
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
    .el-input.nt-date-input {
        width: 100%;
        .el-input__wrapper {
            border-radius: 8px 0 0 8px !important;
            box-shadow: none;
            &:hover{
                box-shadow: none;
            }
            .el-input__inner {
                background: transparent !important;
            }
        }
        .el-input__wrapper.is-focus{
            box-shadow: none;
        }

        .el-input-group__append {
            border-radius: 0 8px 8px 0 !important;
            padding: 5px 10px;
            .el-input {
                .el-input__wrapper {
                    border: none;
                    border-radius: 0 8px 8px 0 !important;
                    background: transparent !important;

                    .el-input__inner {
                        background: transparent !important;
                    }
                }
            }
        }
    }
}

.ninja_only_picker {
    .el-input {
        .el-input__wrapper {
            border-radius: 8px !important;
            padding: 5px;
           .el-input__inner {
                border: none;
                background: transparent !important;
            }
        }
    }
}
</style>
