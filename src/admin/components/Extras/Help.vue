<template>
    <div style="margin-top: 10px">
        <h1 class="wp-heading-inline">{{ $t('Help and Documentation') }}</h1>
        <hr />
        <ninja_premium></ninja_premium>
        <div class="ninja_documentaion_wrapper">
            <div class="ninja_doc_top_blocks">
                <div class="ff_block block_1_3">
                    <div class="ff_block_box help_container text-center">
                      <div>
                        <img class="block_icon" :src="imageUrl('fluent-icon.png')" />
                        <h3>Try Fluent Forms</h3>
                        <p><b>Need to build a contact form by drag and drop form builder?</b> Try the modern contact form plugin with all the necessary input fields, notifications and connect your form with powerful integrations</p>
                      </div>

                      <p><a target="_blank" class="button button-primary" href="https://wordpress.org/plugins/fluentform/">Download Fluent Forms</a></p>
                    </div>
                </div>

              <div class="ff_block block_1_3">
                <div class="ff_block_box help_container text-center">
                  <div>

                  <img class="block_icon" :src="imageUrl('fluentCRM-logo.svg')" style="height: 220px; width: 220px" />
                  <h3>Try Fluent CRM</h3>
                  <p>The easiest and fastest email newsletter, email marketing plugin & CRM solutions for Wordpress.</p>
                  </div>

                  <p><a target="_blank" class="button button-primary" href="https://wordpress.org/plugins/fluent-crm/">Download FluentCRM</a></p>
                </div>
              </div>

              <div class="ff_block block_1_3">
                  <div class="ff_block_box help_container text-center">
                    <div>
                      <img class="block_icon" src="https://ps.w.org/wp-payment-form/assets/icon-256x256.png?rev=2745100" />
                      <h3>Paymattic - Accept Payment Easily with WordPress Forms</h3>
                      <p>Do you want to accept payments along with user inputs in WordPress? Then Paymattic is the perfect plugin for you.</p>
                    </div>

                      <p><a target="_blank" class="button button-primary" href="https://wordpress.org/plugins/wp-payment-form/">Download Paymattic</a></p>
                  </div>
                <div>

                </div>
                </div>


                <div style="width: 100%;float: none;position: relative;height: 10px;overflow: hidden;"></div>

                <div class="ff_block block_1_3">
                    <div class="ff_block_box ninja_tables_user_guide help_container">
                        <h3>User Guide</h3>
                        <p>Please check the following Tutorials and Documentation for Ninja Tables Plugin</p>
                        <ul class="doc_items">
                            <li v-for="(doc, index) in docs" :key="index"><a :href="doc.url" target="_blank">{{ doc.title }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="ff_block block_1_3">
                    <div class="ff_block_box text-center">
                      <div>
                        <img class="block_icon" :src="imageUrl('heart.png')" />
                        <h3>Love this Plugin?</h3>
                        <p>Please write a review in wp.org plugin repository. We appreciate it!</p>
                      </div>
                        <p><a target="_blank" class="button button-primary" href="https://wordpress.org/plugins/ninja-tables/reviews/">Write Review</a></p>
                    </div>
                </div>
                <div class="ff_block block_1_3">
                    <div class="ff_block_box text-center">
                      <div>
                        <img class="block_icon" :src="imageUrl('support.png')" />
                        <h3>Need An Expert Support?</h3>
                        <p>Our EXPERTS would like to assist you for your query and any customization.</p>
                    </div>
                        <p><a target="_blank" class="button button-primary" href="https://wpmanageninja.com/support-tickets/">Contact Support</a></p>
                    </div>
                </div>
            </div>
        </div>

        <fluentpromoad></fluentpromoad>
    </div>
</template>

<script type="text/babel">
   // import FluentPromo from './FluentPromoAdd';
   import FluentPromoAdd from './FluentPromoAdd2.vue';
   import nijaPremium from './NinjaPremiumPromo';
    export default {
        name: 'help',
        components: {
            'fluentpromoad': FluentPromoAdd,
            'ninja_premium': nijaPremium
        },
        data() {
            return {
                docs: [
                   {
                       title: 'Getting started with Ninja Tables',
                       url: 'https://ninjatables.com/docs-category/getting-started-with-ninja-tables/'
                   },
                   {
                       title: 'Ninja Tables Demo',
                       url: 'https://ninjatables.com/demo/'
                   },
                   {
                       title: 'Ninja Tables Drag and Drop',
                       url: 'https://ninjatables.com/docs-category/simple-mode/'
                   },
                   {
                       title: 'Ninja Tables Advanced Mode: ',
                       url: 'https://ninjatables.com/docs-category/advanced-mode/'
                   },
                   {
                       title: 'Export/Import Data from Ninja Tables',
                       url: 'https://ninjatables.com/docs-category/import-export/'
                   },
                   {
                       title: 'Ninja Tables Integrations',
                       url: 'https://ninjatables.com/docs-category/integrations/'
                   },
                   {
                       title: 'Responsive breakpoint settings',
                       url: 'https://ninjatables.com/docs-category/responsive-settings/'
                   },
               ],
                img_url: window.ninja_table_admin.img_url
            }
        },
        methods: {
           imageUrl(imageName) {
               return this.img_url+imageName;
           }
        },
        mounted() {

        }
    }
</script>
<style>
.ninja_doc_top_blocks .ff_block .ff_block_box {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.ninja_doc_top_blocks .ff_block .ff_block_box > p {
  margin-bottom: 0;

}
</style>
