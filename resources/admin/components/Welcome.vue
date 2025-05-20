<template>
    <div class="ninja_intro_welcome" :style="{ background: `url(${assetUrl('img/on-board.png')})` }">
        <div class="bg-white w-[475px] mx-auto border border-gray-200 rounded-2xl p-4">
            <div class="flex justify-center">
                <img :src="assetUrl('icons/welcome.svg')" alt="Ninja Tables">
            </div>
            <div class="my-4">
                <h2>Welcome to Ninja Tables</h2>
                <p class="mx-5 text-[#525866] text-[14px] text-center font-[300]">Thank you for installing Ninja Tables - Best Responsive Table Plugin for WordPress</p>
            </div>
            <div class="flex justify-center items-center gap-2">
                <NinjaButton @click="create">
                    Create Your First Table
                </NinjaButton>
                <router-link :to="{ name: 'import_tables' }">
                    <NinjaButton type="secondary">{{ $t('Import From CSV') }}</NinjaButton>
                </router-link>
            </div>
            <hr class="my-4 border-[0.5px] border-gray-200" />
            <div class="ninja_docs">
                <h4>Ninja Tables Documentation</h4>
                <ul>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.6 12.0016L10.2 17.4016V6.60156L15.6 12.0016Z" fill="#0E121B"></path></svg>
                        <a class="text-[#335cff]" target="_blank" href="https://wpmanageninja.com/ninja-tables/use-case/?ninja_intro=1">
                            Demo and Basic Settings
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.6 12.0016L10.2 17.4016V6.60156L15.6 12.0016Z" fill="#0E121B"></path></svg>
                        <a class="text-[#335cff]" target="_blank" href="https://wpmanageninja.com/docs/ninja-tables/setting-up-a-table/?ninja_intro=1">
                            Setting Up a Table
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.6 12.0016L10.2 17.4016V6.60156L15.6 12.0016Z" fill="#0E121B"></path></svg>
                        <a class="text-[#335cff]" target="_blank" href="https://wpmanageninja.com/docs/ninja-tables/configure-responsive-breakdowns-for-table/?ninja_intro=1">
                            Make Your Table Looks Great on All Devices
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</template>

<script>
import NinjaButton from '../@ui-utils/NinjaButton.vue';
import { assetUrl } from '../utils/ninjatablesadmin';

export default {
    name: "Welcome",
    components: {
        NinjaButton
    },
    methods: {
        assetUrl,
        create() {
            this.$emit('create', true);
        }
    }
}
</script>

<style lang="scss">
.ninja_intro_welcome {
    margin: 45px auto 0px;
    padding: 30px 20px;
    text-align: center;

    h2 {
        font-size: 30px;
    }

    .ninja_actions {
        margin-bottom: 30px;
    }

    .ninja_docs {
        text-align: left;
    }
}
</style>
