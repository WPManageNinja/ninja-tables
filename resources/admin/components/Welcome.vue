<template>
    <div class="ninja_intro_welcome" :style="{ background: `url(${assetUrl('img/on-board.png')})` }">
        <div class="bg-white w-[475px] mx-auto border border-gray-200 rounded-2xl p-8">
            <div class="flex justify-center">
                <img width="36" height="36" :src="assetUrl('img/ninja_table.svg')" alt="Ninja Tables">
            </div>
            <div class="my-4 text-center">
                <h2>{{ $t('Welcome to Ninja Tables') }}</h2>
                <p class="text-[#525866] text-[14px] font-[300]">{{ $t('Thank you for installing Ninja Tables - Best Responsive Table Plugin for WordPress') }}</p>
            </div>
            <div class="flex items-center gap-4">
                <NinjaButton @click="create" class="w-full">
                    {{$t('Create Your First Table')}}
                </NinjaButton>
                <router-link :to="{ name: 'import_tables' }" class="w-full">
                    <NinjaButton type="secondary" class="w-full">{{ $t('Import From CSV') }}</NinjaButton>
                </router-link>
            </div>
            <hr class="my-4 border-[0.5px] border-gray-200" />
            <div class="ninja_docs">
                <h4 class="nt-modal-subtitle mb-2">{{ $t('Ninja Tables Documentation') }}</h4>
                <ul>
                    <li class="flex hover:underline">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                          <path d="M10 17.5C5.85775 17.5 2.5 14.1422 2.5 10C2.5 5.85775 5.85775 2.5 10 2.5C14.1422 2.5 17.5 5.85775 17.5 10C17.5 14.1422 14.1422 17.5 10 17.5ZM8.2825 15.7502C7.54256 14.1807 7.1139 12.4827 7.02025 10.75H4.0465C4.19244 11.9042 4.67044 12.9911 5.42243 13.8788C6.17441 14.7664 7.16801 15.4166 8.2825 15.7502ZM8.5225 10.75C8.63575 12.5792 9.1585 14.2975 10 15.814C10.8642 14.2574 11.3691 12.5271 11.4775 10.75H8.5225ZM15.9535 10.75H12.9797C12.8861 12.4827 12.4574 14.1807 11.7175 15.7502C12.832 15.4166 13.8256 14.7664 14.5776 13.8788C15.3296 12.9911 15.8076 11.9042 15.9535 10.75ZM4.0465 9.25H7.02025C7.1139 7.51734 7.54256 5.81926 8.2825 4.24975C7.16801 4.58341 6.17441 5.23356 5.42243 6.12122C4.67044 7.00888 4.19244 8.09583 4.0465 9.25ZM8.52325 9.25H11.4767C11.3686 7.47295 10.864 5.74265 10 4.186C9.13576 5.74259 8.63092 7.47289 8.5225 9.25H8.52325ZM11.7175 4.24975C12.4574 5.81926 12.8861 7.51734 12.9797 9.25H15.9535C15.8076 8.09583 15.3296 7.00888 14.5776 6.12122C13.8256 5.23356 12.832 4.58341 11.7175 4.24975Z" fill="#99A0AE"/>
                        </svg>
                        <a class="nt-link" target="_blank" href="https://wpmanageninja.com/ninja-tables/use-case/?ninja_intro=1">
                            {{ $t('Demo and Basic Settings') }}
                        </a>
                    </li>
                    <li class="flex hover:underline">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                          <path d="M4.75 9.25H15.25V4.75H4.75V9.25ZM16.75 4V16C16.75 16.1989 16.671 16.3897 16.5303 16.5303C16.3897 16.671 16.1989 16.75 16 16.75H4C3.80109 16.75 3.61032 16.671 3.46967 16.5303C3.32902 16.3897 3.25 16.1989 3.25 16V4C3.25 3.80109 3.32902 3.61032 3.46967 3.46967C3.61032 3.32902 3.80109 3.25 4 3.25H16C16.1989 3.25 16.3897 3.32902 16.5303 3.46967C16.671 3.61032 16.75 3.80109 16.75 4ZM15.25 10.75H4.75V15.25H15.25V10.75ZM6.25 12.25H8.5V13.75H6.25V12.25ZM6.25 6.25H8.5V7.75H6.25V6.25Z" fill="#99A0AE"/>
                        </svg>
                        <a class="nt-link" target="_blank" href="https://wpmanageninja.com/docs/ninja-tables/create-a-table/">
                            {{ $t('Setting Up a Table') }}
                        </a>
                    </li>
                    <li class="flex hover:underline">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                          <path d="M15.25 7H16.75C16.9489 7 17.1397 7.07902 17.2803 7.21967C17.421 7.36032 17.5 7.55109 17.5 7.75V16.75C17.5 16.9489 17.421 17.1397 17.2803 17.2803C17.1397 17.421 16.9489 17.5 16.75 17.5H10.75C10.5511 17.5 10.3603 17.421 10.2197 17.2803C10.079 17.1397 10 16.9489 10 16.75V16H4C3.80109 16 3.61032 15.921 3.46967 15.7803C3.32902 15.6397 3.25 15.4489 3.25 15.25V3.25C3.25 3.05109 3.32902 2.86032 3.46967 2.71967C3.61032 2.57902 3.80109 2.5 4 2.5H14.5C14.6989 2.5 14.8897 2.57902 15.0303 2.71967C15.171 2.86032 15.25 3.05109 15.25 3.25V7ZM13.75 7V4H4.75V14.5H10V7.75C10 7.55109 10.079 7.36032 10.2197 7.21967C10.3603 7.07902 10.5511 7 10.75 7H13.75ZM11.5 8.5V16H16V8.5H11.5Z" fill="#99A0AE"/>
                        </svg>
                        <a class="nt-link" target="_blank" href="https://wpmanageninja.com/docs/ninja-tables/configure-responsive-breakdowns-for-table/?ninja_intro=1">
                            {{ $t('Make Your Table Looks Great on All Devices') }}
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
