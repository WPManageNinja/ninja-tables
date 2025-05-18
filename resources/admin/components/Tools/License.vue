<template>
    <div class="nt-table-license-content">
        <div class="nt-license-header">
            <div class="text-[18px] font-[600] text-[#0E121B]" v-if="is_valid == 'valid'">
                {{ $t('CSV Export / Print Button Settings for Frontend') }}
            </div>
            <div class="text-[18px] font-[600] text-[#0E121B]" v-loading="checkingLicense"
                 v-else-if="is_valid == 'expired'">
                {{ $t('Licensing has been expired') }}
            </div>
            <div v-else>
                <div class="text-[18px] font-[600] text-[#0E121B]">{{ $t('Licensing') }}</div>
                <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
                    {{
                        $t(`You need to activate your Ninja Table Pro by providing the license key below. If you don't have a license key please`)
                    }}
                    <a href="https://wpmanageninja.com/checkout/purchase-history/" target="_blank"
                       class="text-blue-500 underline">Click Here</a>
                    {{ $t('to get a license key from your purchase. Any questions or problems with your license?') }}
                    <a href="https://wpmanageninja.com/contact/" target="_blank" class="text-blue-500 underline">Contact
                        us!</a>
                </div>
            </div>
            <hr class="my-4">
        </div>
        <div v-loading="checkingLicense" class="nt-license-content mt-4">
            <div class="text-[16px] font-[500] text-center text-[#0E121B] my-4" v-if="is_valid == 'valid'">
                {{ $t('Your license is active! Enjoy Ninja Tables Pro Add On') }}
                <NinjaButton size="small" class="m-auto my-3" @click="deactivateLicense"
                             :btn-text="$t('Deactivate License')"/>
                <p v-if="renewHtml" v-html="renewHtml"></p>
            </div>
            <div v-else-if="is_valid == 'expired'">
                <div v-html="renewLicenseHtml"></div>
                <div class="text-[14px] font-[400] text-[#0E121B] mt-[10px] mb-[20px]">
                        <span>
                            {{ $t('If you already renewed your license then please') }}
                        <a @click.prevent="get_license_info" class="text-blue-500 underline"
                           href="#">{{ $t('click here to check again') }}</a>
                        </span>

                    <span>
                        {{ $t('Have a new license key? Please ') }}
                        <a href="#" @click.prevent="enter_new_license = true"
                           class="text-blue-500 underline">{{ $t('click here') }}</a>
                    </span>

                </div>
                <div v-if="enter_new_license" class="my-4 flex flex-col">
                    <p class="my-2 text-[16px] font-[500] text-[#3C434A]">{{ $t('Enter your license key') }}</p>
                    <div class="w-1/2">
                        <NinjaInput size="small" v-model="licenseKey" placeholder="License Key"/>
                        <NinjaButton class="my-3" v-loading="doing_ajax" @click="activateLicense" type="primary"
                                     size="small" :btn-text="$t('Activate Pro')"/>
                        <p v-html="error_message" v-if="error_message"></p>
                    </div>
                </div>
            </div>
            <div v-else class="my-4 flex flex-col">
                <p class="my-2 text-[16px] font-[400] text-[#3C434A]">{{ $t('Enter your license key') }}</p>
                <div class="w-1/2">
                    <NinjaInput size="small" v-model="licenseKey" placeholder="License Key"/>
                    <NinjaButton class="my-3" v-loading="doing_ajax" @click="activateLicense" type="primary"
                                 size="small" :btn-text="$t('Activate Pro')"/>
                    <p v-html="error_message" v-if="error_message"></p>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import NinjaButton from "../../@ui-utils/NinjaButton.vue";
import NinjaInput from "../../@ui-utils/NinjaInput.vue";

export default {
    name: 'license',
    components: {NinjaInput, NinjaButton},
    data() {
        return {
            licenseKey: '',
            error_message: '',
            enter_new_license: false,
            checkingLicense: false,
            doing_ajax: false,
            renewLicenseHtml: '',
            renewHtml: '',
            is_valid: window.ninja_table_admin.hasValidLicense
        }
    },
    methods: {
        activateLicense() {
            if (!this.licenseKey) {
                this.error_message = 'Please provide a license key';
                return;
            }
            this.doing_ajax = true;
            this.error_message = '';
            this.$post({
                action: '_ninjatables_pro_license_activate_license',
                _ninjatables_pro_license_key: this.licenseKey
            })
                .then(response => {
                    if (response.data && response.data.message) {
                        jQuery('.error_notice_ninjatables_pro_license').remove();
                        this.is_valid = 'valid';
                    } else {
                        this.error_message = 'Something is wrong when contacting with license server. Please make sure you have curl installed you server';
                    }
                    this.doing_ajax = false;
                })
                .fail(error => {
                    if (error.responseJSON && error.responseJSON.data) {
                        this.error_message = error.responseJSON.data.message;
                    } else {
                        this.error_message = 'Sorry, Something is wrong! Please make sure you have ninja tables pro installed and curl installed on your server';
                    }
                    this.doing_ajax = false;
                })
                .always(() => {
                    this.doing_ajax = false;
                });
        },
        deactivateLicense() {
            this.doing_ajax = true;
            this.error_message = '';
            this.$post({
                action: '_ninjatables_pro_license_deactivate_license'
            })
                .then(response => {
                    this.is_valid = false;
                })
                .fail(error => {
                    console.log(error);
                    this.error_message = error.responseJSON.data.message;
                    if (error.responseJSON && error.responseJSON.data) {
                        this.$message.error(error.responseJSON.data.message);
                    } else {
                        this.$message.error('Sorry, Something is wrong! Please try again');
                    }
                })
                .always(() => {
                    this.doing_ajax = false;
                });

        },
        get_license_info() {
            this.checkingLicense = true;
            this.error_message = '';
            this.$get({
                action: '_ninjatables_pro_license_get_license_info'
            })
                .then(response => {
                    this.renewLicenseHtml = response.data.renewHtml;
                    this.is_valid = response.data.status;
                    this.renewHtml = response.data.renewHtml;
                })
                .fail(error => {
                    this.error_message = error.responseJSON.data.message;
                })
                .always(() => {
                    this.checkingLicense = false;
                });
        }
    },
    mounted() {
        this.get_license_info();
    }
}
</script>
