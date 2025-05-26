<template>
    <div class="ninja_intro_welcome rounded-[12px] border border-[#E1E4EA] bg-white max-w-[600px]">
        <div class="ninja_charts_promo_banner">
            <img :src="imageUrl('ninja-charts-promo.png')" alt="Ninja Charts">
        </div>

        <div class="text-[18px] font-[600] text-[#0E121B] mt-6">{{ $t('Welcome to Ninja Charts') }}</div>
        <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
            {{ $t("Best WP Charts Plugin for WordPress") }}
        </div>

        <div class="ninja_charts_promo w-full">
            <iframe
                width="100%"
                height="315"
                src="https://www.youtube.com/embed/vIHR3_vNOFM"
                frameborder="0"
                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
            ></iframe>
        </div>
        <div class="ninja_actions my-6">
            <NinjaButton class="mx-auto" type="primary" @click="click" :loading="loading"
                         :btn-text="$t('Enable Ninja Charts')"/>
        </div>
    </div>
</template>

<script>
import NinjaButton from "../@ui-utils/NinjaButton.vue";

export default {
    name: "Charts",
    components: {NinjaButton},
    data() {
        return {
            imgUrl: window.ninja_table_admin.img_url,
            loading: false
        }
    },
    beforeRouteEnter(to, from, next) {
        if (window.ninja_table_admin.ninja_charts_url) {
            window.location = window.ninja_table_admin.ninja_charts_url;
        } else {
            next();
        }
    },
    methods: {
        imageUrl(imageName) {
            return this.imgUrl + imageName;
        },
        click() {
            this.loading = true;

            let data = {
                plugin: 'ninja-charts',
            };

            this.$post('install/ninja-charts', data)
                .then((response) => {
                    this.$message({
                        showClose: true,
                        message: response.data.message,
                        type: 'success'
                    });

                    window.location = response.data.redirect;
                })
                .catch((error) => {
                    if (error.responseJSON.data.message) {
                        this.$message({
                            showClose: true,
                            message: error.responseJSON.data.message,
                            type: 'error'
                        });
                    } else {
                        this.$message({
                            showClose: true,
                            message: error.responseText,
                            type: 'error'
                        });
                    }
                })
            this.loading = false;
        }
    }
}
</script>
