<template>
    <div class="ninja_Language_settings">
        <div class="text-[18px] font-[600] text-[#0E121B]">{{ $t('Language Settings') }}</div>
        <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
            {{ $t("You can configure table language settings here.") }}
        </div>

        <div class="mb-3 text-[16px] font-[500]">{{ $t("Customize Your Language Preferences") }}</div>
        <div class="px-6 py-5 border-solid border border-[#E1E4EA] rounded-[10px]">
            <div class="grid grid-cols-2 gap-5">
                <div class="w-full">
                    <div class="mb-2 text-[14px] font-[500]">{{ $t('Empty Results Text:') }}</div>
                    <NinjaInput v-model="tableSettings.no_result_text" size="large"/>
                </div>

                <div>
                    <div class="mb-2 text-[14px] font-[500]"> {{ $t("Search Box Placeholder Text") }}</div>
                    <NinjaInput v-model="tableSettings.search_placeholder" size="large"/>
                </div>

                <div>
                    <div class="mb-2 text-[14px] font-[500]">{{ $t("Search Dropdown Heading") }}</div>
                    <NinjaInput v-model="tableSettings.search_in_text" size="large"/>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-5">
            <NinjaButton type="primary" size="small" @click="storeSettings" :btn-text="$t('Save Settings')"/>
        </div>
    </div>
</template>

<script type="text/babel">
import NinjaInput from "../../../@ui-utils/NinjaInput.vue";
import NinjaButton from "../../../@ui-utils/NinjaButton.vue";

export default {
    name: 'ninja_language_settings',
    components: {NinjaButton, NinjaInput},
    props: ['tableSettings'],
    methods: {
        storeSettings() {
            this.$emit('storeSettings');
        }
    }
}
</script>
