<template>
    <div class="ninja_intro_welcome">
        <div class="ninja_charts_promo_banner">
            <img :src="imageUrl('ninja-charts-promo.png')" alt="Ninja Charts">
        </div>
        <h2>Welcome to Ninja Charts</h2>
        <p>Best WP Charts Plugin for WordPress</p>
        <div class="ninja_charts_promo">
            <iframe
                    width="600"
                    height="315"
                    src="https://www.youtube.com/embed/vIHR3_vNOFM"
                    frameborder="0"
                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
            ></iframe>
        </div>
        <div class="ninja_actions">
            <el-button type="danger" :loading="loading" @click="click()">
                Enable Ninja Charts
            </el-button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Charts",
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
                    action: 'ninja_tables_ajax_actions',
                    target_action: 'install-extra-plugins',
                    plugin: 'ninja-charts',
                };

                this.$post('ninja-charts', data)
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

<style lang="scss" scoped>
    .ninja_intro_welcome {
        max-width: 600px;
        margin: 45px auto 0px;
        padding: 30px;
        background: white;
        text-align: center;

        img {
            width: 100%;
        }

        h2 {
            font-size: 30px;
        }

        .ninja_actions {
            margin-top: 30px;
        }
    }
</style>
