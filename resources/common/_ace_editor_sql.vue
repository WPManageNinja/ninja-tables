<template>
    <div v-loading="loading" element-loading-text="Loading Editor...">
        <div class="ace_container">
            <div class="ninja_custom_css_editor" :id="editorId">{{ value }}</div>
        </div>
        <div class="editor_errors" :class="'ninja_'+mode+'_errors'">
            <span v-show="editorError" style="text-align: right; display: inline-block; color: #ff7171; float: right">{{ editorError }}</span>
        </div>
    </div>
</template>
<script>
export default {
    name: 'ninja_ace_editor',
    props: {
        value: {
            type: String,
            default: ''
        },
        mode: {
            type: String,
            required: true
        },
        editor_id: {
            type: String,
            default: ''
        },
        modelValue: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue', 'change'],
    data() {
        return {
            ace_path: window.ninja_table_admin.ace_path_url,
            editorError: '',
            loading: true,
            editor: null,
            internalValue: this.value || this.modelValue || ''
        }
    },
    computed: {
        editorId() {
            return this.editor_id || 'ninja_custom_css';
        }
    },
    methods: {
        loadDependencies() {
            if (typeof window.ace === 'undefined') {

                const script = document.createElement('script');

                script.src = this.ace_path + '/ace.min.js';
                script.async = true;

                script.onload = () => {
                    const modeScript = document.createElement('script');
                    modeScript.src = this.ace_path + '/mode-' + this.mode + '.js';
                    modeScript.async = true;

                    modeScript.onload = () => {
                        const themeScript = document.createElement('script');
                        themeScript.src = this.ace_path + '/theme-monokai.js';
                        themeScript.async = true;

                        themeScript.onload = () => {
                            this.initAce();
                        };
                        document.head.appendChild(themeScript);
                    };
                    document.head.appendChild(modeScript);
                };
                script.onerror = (error) => {
                    console.error('Failed to load Ace editor:', error);
                    this.loading = false;
                };
                document.head.appendChild(script);
            } else {
                this.initAce();
            }
        },
        initAce() {
            try {
                if (typeof window.ace === 'undefined') {
                    throw new Error('Ace editor not loaded');
                }
                
                window.ace.config.set("workerPath", this.ace_path);
                window.ace.config.set("modePath", this.ace_path);
                window.ace.config.set("themePath", this.ace_path);
                
                this.editor = window.ace.edit(this.editorId);
                this.editor.setTheme("ace/theme/monokai");
                this.editor.session.setMode("ace/mode/" + this.mode);

                this.editor.setValue(this.internalValue, -1);

                this.editor.getSession().on("changeAnnotation", this.handleAnnotationChange);
                this.editor.getSession().on("change", this.handleEditorChange);
                
                this.loading = false;
            } catch (error) {
                console.error('Failed to initialize Ace editor:', error);
                this.loading = false;
            }
        },
        handleAnnotationChange() {
            if (!this.editor) return;
            
            const annotations = this.editor.getSession().getAnnotations();
            this.editorError = '';
            for (const key in annotations) {
                if (annotations[key].type === 'error') {
                    this.editorError = annotations[key].text;
                    break;
                }
            }
        },
        handleEditorChange() {
            if (!this.editor) return;
            
            const value = this.editor.getSession().getValue();
            this.internalValue = value;

            this.$emit('update:modelValue', value);
            this.$emit('change', value);
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.loadDependencies();
        });
    },
    beforeUnmount() {
        if (this.editor) {
            this.editor.destroy();
            this.editor = null;
        }
    },
    watch: {
        value(newValue) {
            this.internalValue = newValue;
            if (this.editor && newValue !== this.editor.getValue()) {
                this.editor.setValue(newValue, -1);
            }
        },
        modelValue(newValue) {
            this.internalValue = newValue;
            if (this.editor && newValue !== this.editor.getValue()) {
                this.editor.setValue(newValue, -1);
            }
        },
        mode(newMode) {
            if (this.editor) {
                this.editor.session.setMode(`ace/mode/${newMode}`);
            }
        }
    }
}
</script>

<style scoped>
.ninja_custom_css_editor {
    min-height: 300px;
    height: auto;
}
.ninja_css_errors .ace_gutter-cell.ace_warning,
.ninja_sql_errors .ace_gutter-cell.ace_warning {
    display: none;
}
</style>
