<template v-if="notices.length > 0">
    <div
        v-for="(notice, index) in notices"
        :key="generateNoticeKey(notice, index)"
        v-html="notice"
        @click="handleClick"
    ></div>
</template>

<script>
export default {
    name: "Notices",
    data() {
        return {
            notices: window?.ninjaTablesAdminNotices?.notices || [],
            nonce: window?.ninjaTablesAdminNotices?.nonce || '',
            ajax_url: window?.ninjaTablesAdminNotices?.ajax_url || ajaxurl
        };
    },
    methods: {
        generateNoticeKey(noticeHtml, index) {
            const match = noticeHtml.match(/data-notice-key="([^"]+)"/);
            return match ? match[1] : `notice-${index}`;
        },

        handleClick(event) {
            const button = event.target.closest('[data-notice-type]');
            if (!button) return;
            event.preventDefault();

            const notice = button.closest('[data-notice-key]');
            const noticeKey = notice?.dataset.noticeKey;
            const noticeType = button?.dataset.noticeType;

            if (!noticeKey || !noticeType) return;

            button.disabled = true;

            jQuery.ajax({
                url: this.ajax_url,
                method: "POST",
                data: {
                    action: "ninja_tables_dismiss_admin_notices",
                    notice_key: noticeKey,
                    notice_type: noticeType,
                    _wpnonce: this.nonce
                }
            })
                .done(response => {
                    if (response?.success) {
                        jQuery(notice).slideUp(300, () => {
                            delete this.notices[noticeKey];
                        });
                    }
                })
                .always(() => {
                    button.disabled = false;
                });
        }

    }
}
</script>
