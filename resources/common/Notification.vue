<template>
    <div class="vue-notification" :class="'vue-' + notification.type" v-show="notification.active">
        <button type="button" class="close" aria-label="Close" @click="notification.active = false">
            <span aria-hidden="true">×</span>
        </button>
        <p v-for="message in notification.messages">{{ message }}</p>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'notification'
    }
</script> 