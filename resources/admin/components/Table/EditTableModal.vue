<template>
    <div class="p-[20px]">
        <div class="ninja_modal-body">
            <div class="form-group">
                <label class="nt-form-label">{{ $t('Table Title') }}</label>
                <NinjaInput
                    v-model="table.post_title"
                    :placeholder="$t('Enter a title to identify your table')"
                />
            </div>

            <div class="form-group">
                <label class="nt-form-label">{{ $t('Table Caption') }}</label>
                <NinjaInput
                    v-model="table.table_caption"
                    :placeholder="$t('Enter a table caption if you want to show')"
                />
            </div>

            <div class="form-group">
                <label class="nt-form-label">{{ $t('Table Description') }}</label>
                <WPEditor v-model="table.post_content" />
            </div>
        </div>

        <div class="pt-[10px] flex justify-end items-center gap-x-2">
            <NinjaButton
                type="secondary"
                @click="closeModal"
                :btn-text="$t('Cancel')"
            />
            <NinjaButton
                :loading="btnLoading"
                type="primary"
                @click="addTable"
                :btn-text="$t('Update')"
            />
        </div>
    </div>
</template>

<script type="text/babel">
    import WPEditor from '../../../common/_wp_editor';
    import NinjaInput from "../../@ui-utils/NinjaInput.vue";
    import NinjaButton from "../../@ui-utils/NinjaButton.vue";

    export default {
        name: 'add_table',
        components: {
            NinjaButton,
            NinjaInput,
            WPEditor,
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
                    post_title: this.table.post_title,
                    post_content: this.table.post_content,
                    table_caption: this.table.table_caption,
                    tableId: this.table.ID
                };
                this.$post('tables', data)
                    .then((response) => {
                        this.$message({
                            showClose: true,
                            message: response.message,
                            type: 'success'
                        });
                        this.btnLoading = false;
                        this.closeModal();
                    })
                    .catch((error) => {
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
                      this.btnLoading = false;
                    })
            },
            closeModal() {
                this.$emit('modal_close');
            }
        },
        mounted() {

        }
    }
</script>
