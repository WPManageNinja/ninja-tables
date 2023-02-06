<template>
    <div v-if="will_it_show" class="ninja_suggest_plugin">
        <div v-if="dismisable" @click="dismiss()" class="ninja_dismiss">X</div>
        <div class="ninja_form_banner">
            <img :src="image_url('fluentform_banner.jpg')"/>
        </div>
        <div class="ninja_fluent_copy">
            <p>Have you checked out FluentForm yet? We have developed a powerful Drag & Drop WordPress Form Builder plugin with some amazing Premium features</p>
            <a class="button button-primary" :href="fluent_url">Download from WordPress.org</a>
            <a target="_blank" class="button" :href="fluent_wp_url">View Details</a>
        </div>
    </div>
</template>
<script type="text/babel">
    export default {
        name: 'fluentpromoad_2',
        props: ['dismisable'],
        data() {
            return {
                img_url_path: window.ninja_table_admin.img_url,
                fluent_url: window.ninja_table_admin.fluentform_url,
                fluent_wp_url: window.ninja_table_admin.fluent_wp_url,
                is_installed: window.ninja_table_admin.isInstalled,
                already_dismissed: window.ninja_table_admin.dismissed
            }
        },
        computed: {
            will_it_show() {
                if(this.is_installed) {
                    return false;
                }
                if(this.dismisable && this.already_dismissed) {
                    return false;
                }
                return true;
            }
        },
        methods: {
            image_url(image_name) {
                return this.img_url_path+image_name;
            },
            dismiss() {
                let data = {
                    action: 'ninja_tables_ajax_actions',
                    target_action: 'dismiss_fluent_suggest'
                };
                this.$post(data)
                    .then(response => {
                        
                    })
                    .fail(error => {
                        
                    })
                    .always(() => {
                        this.is_installed = true;
                    });
                
            }
        }
    }
</script> 
