<template>
    <div class="wp_vue_editor_wrapper" :class="'editor_wrapper_'+ninja_editor_id">
        <template v-if="hasWpEditor">
            <button @click="showPro" v-if="!has_pro" type="button" class="button ninja_demo_media_button">
                <span class="dashicons dashicons-admin-media"></span>
                {{ $t('Add Media (pro)') }}
            </button>
            <textarea class="wp_vue_editor" :id="ninja_editor_id">{{modelValue}}</textarea>
        </template>
        <template v-else>
            <p style="font-style: italic"><small>{{ $t('WP Editor is only available on WordPress version 4.8 or later. Please Upgrade Your WordPress Core') }}</small></p>
            <textarea
                class="wp_vue_editor wp_vue_editor_plain"
                v-model="plain_content">
            </textarea>
        </template>
    </div>
</template>

<script>
export default {
    name: 'wp_editor',
    props: {
        editor_id: {
            type: String,
            default() {
                return 'wp_editor_'+Date.now();
            }
        },
        modelValue: {
            type: String,
            default() {
                return '';
            }
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            hasWpEditor: typeof window !== 'undefined' && window.wp && !!window.wp.editor,
            plain_content: this.modelValue,
            has_pro: typeof window !== 'undefined' && window.ninja_table_admin && !!window.ninja_table_admin.hasPro,
            editorInitialized: false
        }
    },
    computed: {
        ninja_editor_id() {
            let editorId = this.slugify(this.editor_id);
            let letters = /^[A-Za-z]+$/;
            if(!editorId.match(letters))
            {
                editorId = 'editor_english_'+Math.floor(Math.random() * 1001)+'_'+Math.floor(Math.random() * 101);
            }
            return 'ninja_editor_'+this.slugify(editorId);
        }
    },
    watch: {
        plain_content() {
            this.$emit('update:modelValue', this.plain_content);
        },
        modelValue(newValue, oldValue) {
            // Update editor content when modelValue changes
            if (this.hasWpEditor && this.editorInitialized) {
                const editor = window.tinymce.get(this.ninja_editor_id);
                if (editor) {
                    const currentContent = editor.getContent();
                    if (currentContent !== newValue) {
                        editor.setContent(newValue || '');
                    }
                } else {
                    const textarea = document.getElementById(this.ninja_editor_id);
                    if (textarea) {
                        textarea.value = newValue || '';
                    }
                }
            }

            this.plain_content = newValue;

            if(!newValue && oldValue) {
                this.reloadEditor();
            }
        }
    },
    methods: {
        initEditor() {
            if(this.hasWpEditor) {
                // First remove any existing instance
                if (window.wp && window.wp.editor) {
                    wp.editor.remove(this.ninja_editor_id);
                }

                const that = this;
                wp.editor.initialize(this.ninja_editor_id, {
                    mediaButtons: this.has_pro,
                    mode: "none",
                    tinymce: {
                        toolbar1: 'formatselect,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,strikethrough,underline,forecolor,codeformat,removeformat,undo,redo',
                        valid_elements: "*[*]",
                        forced_root_block: "",
                        setup(editor) {
                            editor.on('init', function() {
                                if (that.modelValue) {
                                    editor.setContent(that.modelValue);
                                }
                                that.editorInitialized = true;
                            });

                            editor.on('change input keyup', function() {
                                that.changeContentEvent();
                            });
                        }
                    },
                    quicktags: true,
                });

                // Handle text mode changes
                jQuery('#'+this.ninja_editor_id).on('input change', function(e) {
                    that.changeContentEvent();
                });
            }
        },
        slugify(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                .replace(/^-+/, '')             // Trim - from start of text
                .replace(/-+$/, '');            // Trim - from end of text
        },
        reloadEditor() {
            if (window.wp && window.wp.editor) {
                wp.editor.remove(this.ninja_editor_id);
                jQuery('#'+ this.ninja_editor_id).val('');
                this.editorInitialized = false;
                this.$nextTick(() => {
                    this.initEditor();
                });
            }
        },
        changeContentEvent() {
            if (window.wp && window.wp.editor) {
                let content = wp.editor.getContent(this.ninja_editor_id);
                this.$emit('update:modelValue', content);
            }
        },
        showPro() {
            if (window.ninjaTableBus) {
                window.ninjaTableBus.$emit('show_pro_popup', 1);
            }
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.initEditor();
        });
    },
    beforeUnmount() {
        if (window.wp && window.wp.editor && this.ninja_editor_id) {
            wp.editor.remove(this.ninja_editor_id);
        }
    }
}
</script>

<style lang="scss">
button.button.ninja_demo_media_button {
    position: absolute;
    z-index: 9999999999;
    cursor: pointer;
}
.wp_vue_editor {
    width: 100%;
    min-height: 100px;
}
.wp_vue_editor_wrapper {
    position: relative;

    .popover-wrapper {
        z-index: 2;
        position: absolute;
        top: 0;
        left: 0;

        &-plaintext {
            left: auto;
            right: 0;
            top: -32px;
        }
    }
}
</style>
