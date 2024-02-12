<template>
    <div class="ninja-tables-help">
        <div class="d-grid user-guide">
            <div class="column">
                <h2>User Guide</h2>
                <p>Please check the Tutorials and Documentation for the Ninja Tables Plugin!</p>
                <ul>
                    <li v-for="(doc, index) in user_guides" :key="index"><a :href="doc.url" target="_blank">{{
                            doc.title
                        }}</a></li>
                </ul>
            </div>
            <div class="column">
                <h2>Popular Searches</h2>
                <p>Please check some FAQs people search for.</p>
                <ul>
                    <li v-for="(doc, index) in popular_searches" :key="index"><a :href="doc.url" target="_blank">{{
                            doc.title
                        }}</a></li>
                </ul>
            </div>
        </div>
        <div class="d-grid col-1 review">
            <div class="column text-center">
                <img class="review-icon" alt="img icon" :src="imageUrl('heart.png')"/>
                <p>Love this Plugin? Give us a 5-star review to show love.</p>
                <a target="_blank" class="button"
                   href="https://wordpress.org/plugins/ninja-tables/reviews/">Write Review</a>
            </div>
        </div>
        <div class="d-grid download">
            <div class="column">
                <img class="banner-img" :src="imageUrl('help-page-banner.png')"/>
            </div>
            <div class="column">
                <div>
                    <p>
                        Have you tried the Ninja Tables Pro?
                    </p>
                    <p>Explore limitless possibilities and get the best table-building experience.</p>
                    <ul>
                        <li>
                            24/7 Priority Support
                        </li>
                        <li>
                            Pro-exclusive Perks
                        </li>
                        <li>
                            Integrations & a lot more!
                        </li>
                    </ul>
                    <a class="button" href="https://ninjatables.com/pricing/" target="_blank">Download Now</a>
                    <a class="visit-site" href="https://ninjatables.com/" target="_blank">Visit Site</a>
                </div>
            </div>
        </div>
        <div class="d-grid support">
            <div class="text-center">
                <img class="icon" :src="imageUrl('support.png')"/>
                <h3>Need An Expert Support?</h3>
                <p>Our experts would like to assist you with your query and any customization.</p>
                <a target="_blank" class="button" href="https://wpmanageninja.com/support-tickets/">Contact
                    our Support team</a>
            </div>
            <div class="text-center">
                <img class="icon" :src="imageUrl('getintouch.png')"/>
                <h3>Get in touch!</h3>
                <p>Connect with the Ninja Tables community on Social Media.</p>
                <div>
                    <a href="https://www.facebook.com/ninjatables/"><img class="icon-mini" :src="imageUrl('facebook.png')"/></a>
                    <a href="https://www.linkedin.com/company/ninja-tables/"><img class="icon-mini" :src="imageUrl('linkedin.png')"/></a>
                    <a href="https://twitter.com/Ninja_Tables/"><img class="icon-mini" :src="imageUrl('x.png')"/></a>
                    <a href="https://www.youtube.com/playlist?list=PLXpD0vT4thWGhHDY0X7UpN9JoR0vu2O_C"><img class="icon-mini" :src="imageUrl('youtube.png')"/></a>
                </div>
            </div>
        </div>
        <div class="product-wrap">
            <h2 class="text-center section-title">Our Products</h2>
            <div class="products d-grid col-3">
                <div class="product-box">
                    <img class="icon" :src="imageUrl('fluentform-logo.png')"/>
                    <p>Join the 400K+ happy users who use the fastest contact form plugin for their websites.</p>
                    <a class="button" href="https://wordpress.org/plugins/fluentform/">
                        Try Fluent Forms for Free
                    </a>
                </div>
                <div class="product-box">
                    <img class="icon" :src="imageUrl('fluentcrm-logo.png')"/>
                    <p>The easiest and fastest email newsletter, email marketing plugin & CRM solutions for WordPress.</p>
                    <a class="button" href="https://wordpress.org/plugins/fluent-crm/">
                        Try FluentCRM for Free
                    </a>
                </div>
                <div class="product-box">
                    <img class="icon" :src="imageUrl('paymattic-logo.png')"/>
                    <p>Get the best payment and donation plugin for your business, fundraising, or membership program.</p>
                    <a class="button" href="https://wordpress.org/plugins/wp-payment-form/">
                        Try Paymettic for Free
                    </a>
                </div>
                <div class="product-box">
                    <img class="icon" :src="imageUrl('fluentsupport-logo.png')"/>
                    <p>Transform your support agents into superheroes with the WordPress helpdesk plugin.</p>
                    <a class="button" href="https://wordpress.org/plugins/fluent-support/">
                        Download Fluent Support for Free
                    </a>
                </div>
                <div class="product-box">
                    <img class="icon" :src="imageUrl('fluentbooking-logo.png')"/>
                    <p>The ultimate solution for booking appointments, meetings, webinars, events, sales calls, and more.</p>
                    <a class="button" href="https://fluentbooking.com/">
                        Try FluentBooking
                    </a>
                </div>
                <div class="product-box">
                    <img class="icon" :src="imageUrl('wpsocialninja-logo.png')"/>
                    <p>WordPress social tool that fetches social feeds, user reviews, and social chat widgets to your website</p>
                    <a class="button" href="https://wordpress.org/plugins/wp-social-reviews/">
                        Try WP Social Ninja for Free
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HelpDoc',
    data() {
        return {
            user_guides: [
                {
                    title: 'Getting started with Ninja Tables',
                    url: 'https://ninjatables.com/docs-category/getting-started-with-ninja-tables/'
                },
                {
                    title: 'Ninja Tables Drag and Drop',
                    url: 'https://ninjatables.com/docs-category/simple-mode/'
                },
                {
                    title: 'Ninja Tables Advanced Mode',
                    url: 'https://ninjatables.com/docs-category/advanced-mode/'
                },
                {
                    title: 'Ninja Charts Documentations',
                    url: 'https://ninjatables.com/docs-category/simple-mode/'
                },
                {
                    title: 'Ninja Tables Demo',
                    url: 'https://ninjatables.com/demo/'
                },
                {
                    title: 'Ninja Tables Features',
                    url: 'https://ninjatables.com/features/'
                },
                {
                    title: 'Blogs',
                    url: 'https://ninjatables.com/blog/'
                },
            ],
            popular_searches: [
                {
                    title: 'How To Create a WooCommerce Product Table with Ninja Tables?',
                    url: 'https://ninjatables.com/how-to-create-woocommerce-product-table/'
                },
                {
                    title: 'How to export-import tables in Ninja Tables?',
                    url: 'https://ninjatables.com/docs-category/import-export/'
                },
                {
                    title: 'How does the Ninja Tables Custom filter work?',
                    url: 'https://ninjatables.com/docs/custom-filters/'
                },
                {
                    title: 'How to create Google Sheets integrated tables with Ninja Tables?',
                    url: 'https://ninjatables.com/embed-google-sheet-in-wordpress-tables/'
                },
                {
                    title: 'What are Ninja Tables Advanced Shortcodes?',
                    url: 'https://ninjatables.com/docs/advanced-shortcode/'
                },
                {
                    title: 'How to make a WP Post Table?',
                    url: 'https://ninjatables.com/how-to-create-wp-posts-tables-easily/'
                },
            ]
        }
    },
    methods: {
        imageUrl(imageName) {
            return window.ninja_table_admin.img_url + imageName;
        }
    }
}
</script>
