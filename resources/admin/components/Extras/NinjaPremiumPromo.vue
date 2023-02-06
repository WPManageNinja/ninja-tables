<template>
    <div v-if="will_it_show" class="ninja_suggest_plugin">
        <div class="ninja_form_banner">
            <img :src="image_url('banner_premium.png')"/>
        </div>
        <div class="ninja_fluent_copy">
            <p>Have you checked out NinjaTables Pro Add-On yet? We have added some exciting features in Ninja Tables with Pro Add-On</p>
            <a class="button button-primary" target="_blank" href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade">Download Now</a>
            <a target="_blank" class="button" href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade">View Details</a>
        </div>
    </div>
</template>
<script type="text/babel">
    export default {
        name: 'ninja_premium',
        data() {
            return {
                img_url_path: window.ninja_table_admin.img_url
            }
        },
        computed: {
            will_it_show() {
                 if(!window.ninja_table_admin.hasPro || window.ninja_table_admin.hasPro == "") {
                     return true;
                 } 
                 return false;
            }
        },
        methods: {
            image_url(image_name) {
                return this.img_url_path+image_name;
            }
        }
    }
</script> 