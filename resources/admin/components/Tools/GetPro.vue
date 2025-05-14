<template>
    <a v-if="!has_pro"
       href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&amp;utm_medium=wp&amp;utm_campaign=wp_plugin&amp;utm_term=upgrade"
       target="_blank" style="display: inline-block">
        <NinjaButton :type="type" :size="size" :btnText="$t(text)"/>
    </a>
</template>

<script>
import NinjaButton from "../../@ui-utils/NinjaButton.vue";

export default {
    name: 'GetPro',
    components: {NinjaButton},
    data() {
        return {
            has_pro: window.ninja_table_admin.hasPro
        }
    },
    props: {
        text: {
            type: String,
            default: 'Get Pro'
        },
        type: {
            type: String,
            default: 'danger'
        },
        size: {
            type: String,
            default: 'small'
        }
    }
}
</script>
