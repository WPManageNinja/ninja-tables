<template>
    <div class="nt-instruction">
        <h3 class="nt-modal-title mb-3">{{title}}</h3>
        <p class="text-[14px] font-[400] text-[#525866]">
            This is a Premium feature. Get
            <b v-if="highlight">{{highlight}},</b>
            <b>unlimited customizations</b>,
            <b>data filters</b>,
            <b>professional looks</b>,
            <b>Many More Integrations</b>
            and so many things from the Pro version.
        </p>

        <div class="mt-4">
            <GetPro/>
        </div>
    </div>
</template>

<script>
    import GetPro from "../Tools/GetPro";
    export default {
      name: "PremiumNotice",
      components: {GetPro},
      props: ['highlight', 'title']
    }
</script>

<style scoped lang="scss">
    .premium-notice {
        .buy_now_button {
            text-decoration: none;
        }
    }
</style>
