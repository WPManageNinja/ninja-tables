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
    name: 'NinjaAceEditor',
    props: {
        value: {
            type: String,
            default: ''
        },
        mode: {
            type: String,
            required: true
        },
        editorId: {
            type: String,
            default: 'ninja_custom_css'
        }
    },
    data() {
        return {
            ace_path: window.ninja_table_admin.ace_path_url,
            editorError: '',
            loading: true,
            editor: null
        }
    },
    methods: {
        async loadDependencies() {
            try {
                if (typeof ace === 'undefined') {
                    await new Promise((resolve, reject) => {
                        const script = document.createElement('script')
                        script.src = this.ace_path + '/ace.min.js'
                        script.onload = resolve
                        script.onerror = reject
                        document.head.appendChild(script)
                    })
                }
                this.initAce()
            } catch (error) {
                console.error('Failed to load Ace editor:', error)
                this.loading = false
            }
        },
        initAce() {
            try {
                ace.config.set("workerPath", this.ace_path)
                ace.config.set("modePath", this.ace_path)
                ace.config.set("themePath", this.ace_path)
                
                this.editor = ace.edit(this.editorId)
                this.editor.setTheme("ace/theme/monokai")
                this.editor.session.setMode(`ace/mode/${this.mode}`)
                
                // Set initial value
                this.editor.setValue(this.value || '', -1)
                
                // Setup event listeners
                this.editor.getSession().on("changeAnnotation", this.handleAnnotationChange)
                this.editor.getSession().on("change", this.handleEditorChange)
                
                this.loading = false
            } catch (error) {
                console.error('Failed to initialize Ace editor:', error)
                this.loading = false
            }
        },
        handleAnnotationChange() {
            const annotations = this.editor.getSession().getAnnotations()
            this.editorError = annotations.find(a => a.type === 'error')?.text || ''
        },
        handleEditorChange() {
            const value = this.editor.getSession().getValue()
            this.$emit('update:value', value)
            this.$emit('change', value)
        }
    },
    mounted() {
        this.loadDependencies()
    },
    beforeUnmount() {
        if (this.editor) {
            this.editor.destroy()
            this.editor = null
        }
    },
    watch: {
        value(newValue) {
            // Update editor content when prop changes
            if (this.editor && newValue !== this.editor.getValue()) {
                this.editor.setValue(newValue, -1)
            }
        },
        mode(newMode) {
            // Update editor mode when prop changes
            if (this.editor) {
                this.editor.session.setMode(`ace/mode/${newMode}`)
            }
        }
    }
}
</script>

<style>
    .ninja_custom_css_editor {
       min-height: 350px;
        height: auto;
    }
    .ninja_css_errors .ace_gutter-cell.ace_warning {
        display: none;
    }
</style>
