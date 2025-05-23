<template>
    <a v-if="!has_pro"
       href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&amp;utm_medium=wp&amp;utm_campaign=wp_plugin&amp;utm_term=upgrade"
       target="_blank" >
       <span class="get-pro-button">{{ $t('Get Pro') }}</span>
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

<style scoped>
a:focus{
    box-shadow: none;

}
.get-pro-button {
    background: #fff;
    border: 1px solid #FB3748;
    color: #fb3748;
    padding: 5px 10px;
    font-size: 14px;
    cursor: pointer;
    border-radius: 8px;
}
.get-pro-button:hover {
    background: #FCEBE6;
    transition: all 0.3s ease;
}
</style>
