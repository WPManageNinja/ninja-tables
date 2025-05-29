<template>
    <div class="ninja_charts_welcome_page" :style="{ background: `url(${assetUrl('img/on-board.png')})` }">
        <div class="bg-white w-full lg:w-[550px] mx-auto border border-gray-200 rounded-2xl p-8">
            <div class="flex justify-center">
                <img width="36" height="36" :src="assetUrl('img/ninja_charts.png')" alt="Ninja Tables">
            </div>
            <div class="my-3 text-center">
                <h2>{{ $t('Welcome to Ninja Charts') }}</h2>
                <p class="text-[#525866] text-[14px] font-[300] my-2">
                    {{
                        $t('Create visually impressive and dynamic data charts with this free WordPress charts plugin.')
                    }}
                </p>
            </div>
            <div class="ninja_charts_promo w-full my-6">
                <iframe
                    width="100%"
                    height="250"
                    src="https://www.youtube.com/embed/vIHR3_vNOFM"
                    frameborder="0"
                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
            <div class="block flex items-center gap-4 justify-center">
                <a class="w-full" href="https://ninjatables.com/docs-category/ninja-charts/" target="_blank">
                    <NinjaButton class="w-full" type="secondary" :btnText="$t('Documentation')"/>
                </a>
                <NinjaButton class="w-full" type="primary" @click="click" :loading="loading"
                             :btn-text="$t('Enable Ninja Charts')"/>
            </div>
        </div>
    </div>
</template>

<script>
import NinjaButton from "../@ui-utils/NinjaButton.vue";
import {assetUrl} from "../utils/ninjatablesadmin";

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
        assetUrl,
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

<style lang="scss">
.ninja_charts_welcome_page {
    margin: 45px auto 0px;
    padding: 30px 20px;

    h2 {
        font-size: 30px;
    }
}
</style>
