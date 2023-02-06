<template>
    <div class="review_dialog">
        <span @click="recordActivity('dismiss')" class="consentDismiss">x</span>
        <div class="consent_body">
            <p>Thank You for using Ninja Tables Plugin. We are continuously working on it to improve this plugin. If You can spare a minute, Please help us by leaving a five star rating on wordpress.org</p>
            <a target="_blank" @click="recordActivity('yes')" href="https://wordpress.org/support/plugin/ninja-tables/reviews/#new-post" class="el-button el-button--success el-button--small">Happy To Help</a>
            <span style="cursor: pointer; font-size: 11px" @click="recordActivity('no')">Hide Notification</span>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'review_dialog',
        methods: {
            recordActivity(status) {
                this.$post({
                    action: 'ninja_table_review_consent',
                    status: status
                })
                    .then(() => {})
                    .fail(() => {})
                    .always(() => {
                        this.$emit('hideNotification', true);
                    });
            }
        }
    }
</script>

<style lang="scss">
    .review_dialog {
        background: white;
        padding: 10px 15px 20px;
        font-size: 16px;
        position: relative;
        span.consentDismiss {
            position: absolute;
            right: 7px;
            top: 2px;
            z-index: 99999;
            cursor: pointer;
            color: rgb(128, 128, 128);
        }
        p {
            font-size: 16px;
        }
        a {
            text-decoration: none;
            cursor: pointer;
            margin-right: 20px;
        }
    }
</style>
