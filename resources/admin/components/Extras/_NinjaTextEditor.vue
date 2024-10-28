<template>
<!--    <pre>{{value}}</pre>-->
   <span
       ref="ninja_table_text_editor"
       contenteditable="true"
       v-html="value"
       data-placeholder="Add New"
       @keyup="updateContent"
       :class="{'placeholder': !value}"
       @keydown.delete="handleDelete"
       @input="updateContent"
   ></span>
</template>
<script>

import { restoreCursorPosition, saveCursorPosition } from '../../utils/cursorSetup';


export default {
    props: {
        value: {
            type: String,
        },
        idx: {
            type: [Number, String],
            default: undefined
        },
        toolbar: {
            type: String,
            default: 'bold italic backcolor underline | link unlink'
        },
    },
    mounted() {
        this.initTinymce();
    },
    beforeDestroy() {
        if (this.editor) {
            this.editor.destroy();
        }
    },
    methods: {
        handleDelete(event) {
            const $ref = this.$refs.ninja_table_text_editor;
            const content = $ref.innerHTML;
            if (content.length <= 1 || content === 'Add New') {
                event.preventDefault();
                this.$emit('input', '');
            }
        },
        initTinymce() {
            const $this = this;
            const $ref = this.$refs.ninja_table_text_editor;
            tinymce.init({
                target: $ref,
                inline: true,
                menubar: false,
                plugins: 'link',
                toolbar: this.toolbar,
                setup(editor) {
                    editor.on('init', function () {
                        tinymce.$('p', editor.getBody()).css('margin', '0');
                    });

                    editor.on('NodeChange', function () {
                        tinymce.$('p', editor.getBody()).css('margin', '0');
                    });

                    editor.on('change', function () {
                        const cursorPosition = saveCursorPosition($ref);
                        if ($this.idx !== undefined) {
                            $this.$emit('input', editor.getContent(), $this.idx);
                        } else {
                            $this.$emit('input', editor.getContent());
                        }
                        $this.$nextTick(() => {
                            restoreCursorPosition($ref, cursorPosition);
                        });
                    });

                    editor.on('click', function () {
                        const mceuId = tinymce.activeEditor?.theme?.panel?._id || 'mceu_7';
                        const getEle = document.getElementById(mceuId);
                        getEle.addEventListener('click', function (event) {
                            event.stopPropagation();
                        });
                    });
                }
            });
        },
        updateContent(event) {
            const $ref = this.$refs.ninja_table_text_editor;
            const cursorPosition = saveCursorPosition($ref);
            this.$emit('input', event.target.innerHTML);
            this.$nextTick(() => {
                restoreCursorPosition($ref, cursorPosition);
            });
            const content = event.target.innerHTML;
            const finalContent = content === 'Add New' ? '' : content;

            if (this.idx !== undefined) {
                this.$emit('input', finalContent, this.idx);
            } else {
                this.$emit('input', finalContent);
            }

            this.$nextTick(() => {
                restoreCursorPosition($ref, cursorPosition);
            });
        }
    }
};
</script>

<style scoped>
.placeholder {
    position: relative;
    color: transparent;
}

.placeholder::before {
    content: attr(data-placeholder);
    position: absolute;
    left: 25%;
    top: 0;
    color: gray;
    opacity: 0.6;
    pointer-events: none;
}
</style>