<template>
    <div class="nt-instruction">
        <p>
            This is a Premium feature. Get
            <b v-if="highlight">{{highlight}},</b>
            <b>unlimited customizations</b>,
            <b>data filters</b>,
            <b>professional looks</b>,
            <b>Many More Integrations</b>
            and so many things from the Pro version.
        </p>

        <div class="mt-2">
            <get-pro/>
        </div>
    </div>
</template>

<script>
    import GetPro from "../Tools/GetPro";
    export default {
      name: "PremiumNotice",
      components: {GetPro},
      props: ['highlight']
    }
</script>

<style scoped lang="scss">
    .premium-notice {
        .buy_now_button {
            text-decoration: none;
        }
    }
</style>
