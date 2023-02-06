<template>
    <div class="ninja_table_edit">
        <div class="ninja_modal-body">
            <div class="form-group">
                <label>{{ $t('Table Title') }}</label>
                <el-input v-model="table.post_title"
                          type="text"
                          size="small"
                          placeholder="Enter a title to identify your table"
                />
            </div>

            <div class="form-group">
                <label>{{ $t('Table Caption') }}</label>
                <el-input size="small" v-model="table.table_caption"
                          type="text"
                          placeholder="Enter a table caption if you want to show"
                />
            </div>


            <div class="form-group">
                <label>{{ $t('Table Description') }}</label>
                <wp_editor v-model="table.post_content"></wp_editor>
            </div>
        </div>
        <div class="modal-footer">
            <el-button :loading="btnLoading" type="primary" size="small" @click="addTable">
                {{ $t('Update') }}
            </el-button>
        </div>
    </div>
</template>

<script type="text/babel">
    import wp_editor from '../../../common/_wp_editor';

    export default {
        name: 'add_table',
        components: {
            wp_editor: wp_editor,
        },
        props: {
            table: {
                type: Object,
                default() {
                    return {
                        ID: null,
                        post_title: '',
                        post_content: '',
                        table_caption: ''
                    }
                }
            }
        },
        data() {
            return {
                btnLoading: false,
            }
        },
        methods: {
            handleTabClick(tab, event) {
                setTimeout(() => {
                    jQuery(tab.$el).find('input:first').focus();
                }, 0);
            },
            addTable: function () {
                this.btnLoading = true;
                let data = {
                    action: 'ninja_tables_ajax_actions',
                    target_action: 'store-a-table',
                    post_title: this.table.post_title,
                    post_content: this.table.post_content,
                    table_caption: this.table.table_caption,
                    tableId: this.table.ID
                };
                this.$post(data)
                    .then((response) => {
                        this.$message({
                            showClose: true,
                            message: response.message,
                            type: 'success'
                        });

                        this.closeModal();
                    })
                    .fail((error) => {
                        if (error.responseJSON.data.message) {
                            this.$message({
                                showClose: true,
                                message: error.responseJSON.data.message,
                                type: 'error'
                            });
                        } else {
                            this.$message({
                                showClose: true,
                                message: error.responseText,
                                type: 'error'
                            });
                        }
                    })
                    .always(() => {
                        this.btnLoading = false;
                    });
            },
            closeModal() {
                this.$emit('modal_close');
            }
        },
        mounted() {

        }
    }
</script>
