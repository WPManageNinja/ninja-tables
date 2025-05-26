<template>
    <a v-if="!has_pro"
       href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&amp;utm_medium=wp&amp;utm_campaign=wp_plugin&amp;utm_term=upgrade"
       target="_blank" >
        <div class="flex">
            <div class="flex w-auto items-center gap-x-2 flex-wrap bg-[#0E121B] :hover:bg-[#222530] text-white px-4 py-2 rounded-[8px]">
                <img :src="assetUrl('icons/get-pro.svg')" alt="">
                <span class="ml-1">{{ $t('Get Pro') }}</span>
            </div>
        </div>
    </a>
</template>

<script>
import NinjaButton from "../../@ui-utils/NinjaButton.vue";
import {assetUrl} from "../../utils/ninjatablesadmin";

export default {
    name: 'GetPro',
    methods: {assetUrl},
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
</style>
