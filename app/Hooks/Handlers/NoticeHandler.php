<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Support\Arr;

class NoticeHandler
{
    private $noticeKey = '_ninja_tables_notices';
    private const TEMP_DISMISS_DAYS = 30;
    private const SECONDS_IN_A_DAY = 86400;

    public function register()
    {
        add_action('admin_notices', [$this, 'appendNotices']);
        add_action('wp_ajax_ninja_tables_dismiss_notice', [$this, 'handleDismissNotice']);
    }

    public function handleDismissNotice()
    {
        check_ajax_referer('ninja_tables_admin_nonce');

        $key  = sanitize_text_field(Arr::get($_POST, 'notice_key', ''));
        $type = sanitize_text_field(Arr::get($_POST, 'notice_type', ''));

        if (!$key || !$type) {
            wp_send_json_error([
                'success' => false,
                'message' => 'Invalid notice key or type.',
            ]);
        }

        $notices       = get_option($this->noticeKey, []);
        $notices[$key] = [
            'type'         => $type,
            'dismissed_at' => current_time('mysql'),
        ];

        update_option($this->noticeKey, $notices, false);

        wp_send_json_success([
            'success' => true,
            'message' => 'Notice dismissed.',
            'key'     => $key,
            'type'    => $type,
        ]);
    }

    public function notices()
    {
        $allNotices    = $this->getAllNoticeDefinitions();
        $dismissed     = get_option($this->noticeKey, []);
        $activeNotices = [];

        foreach ($allNotices as $key => $notice) {
            $enabled  = $notice['condition'];
            $type     = $notice['type'];
            $callback = $notice['callback'];

            if (!$enabled) {
                continue;
            }

            if (isset($dismissed[$key])) {
                $dismissData = $dismissed[$key];

                if ($dismissData['type'] === 'permanent') {
                    continue;
                }

                if ($type === 'temp' && $dismissData['type'] === 'temp') {
                    $dismissedAt = strtotime($dismissData['dismissed_at']);
                    $now         = current_time('timestamp');
                    $daysElapsed = ($now - $dismissedAt) / self::SECONDS_IN_A_DAY;

                    if ($daysElapsed < self::TEMP_DISMISS_DAYS) {
                        continue;
                    }
                }
            }

            $activeNotices[$key] = call_user_func($callback, $key);
        }

        return $activeNotices;
    }

    private function getAllNoticeDefinitions()
    {
        return [
            'review_notice'  => [
                'type'      => 'temp',
                'callback'  => [$this, 'getReviewHtml'],
                'condition' => true,
            ],
            'upgrade_to_pro' => [
                'type'      => 'temp',
                'callback'  => [$this, 'getUpgradeNoticeHtml'],
                'condition' => defined('NINJAPROPLUGIN_VERSION') &&
                               version_compare(NINJAPROPLUGIN_VERSION, '5.0.0', '<'),
            ],
        ];
    }

    private function getReviewHtml($key)
    {
        return <<<HTML
<div class="nt_review_notice" data-notice-key="$key">
    <div class="nt-notice-content">
        <div class="nt-notice-text">
            In love with Ninja Tables?
            <a target="_blank" href="https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5">
                Please leave a 5-star review for us!
            </a>
            It will encourage us to come up with more and more features.
        </div>
        <div class="nt-notice-actions">
            <a class="nt-btn nt-btn-secondary remind-me-later" href="#" data-notice-type="temp">Remind Me Later</a>
            <div class="nt-divider"></div>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M9.9992 14.695L4.70945 17.656L5.8907 11.71L1.43945 7.594L7.4597 6.88L9.9992 1.375L12.5387 6.88L18.559 7.594L14.1077 11.71L15.289 17.656L9.9992 14.695Z" fill="#F6B51E"/>
            </svg>
            <a class="nt-btn nt-btn-primary" target="_blank" href="https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5">Rate Now</a>
        </div>
    </div>
</div>
HTML;
    }

    private function getUpgradeNoticeHtml($key)
    {
        $url  = esc_url(admin_url('plugins.php?s=ninja-tables-pro&plugin_status=all'));
        $text = esc_html__('Please update to the latest version', 'ninja-tables');

        return <<<HTML
<div class="nt_review_notice" data-notice-key="$key">
    <div class="nt-notice-content">
        <div class="nt-notice-text">
            <h3>Update Ninja Tables Pro Plugin</h3>
            <p>
                You are using an outdated version of Ninja Tables Pro. Some pro features may not work properly.
                <a target="_blank" href="{$url}">{$text}</a>
            </p>
        </div>
        <div class="nt-notice-actions">
            <a class="nt-btn nt-btn-secondary remind-me-later" href="#" data-notice-type="temp">
                Remind Me Later
            </a>
        </div>
    </div>
</div>
HTML;
    }

    public function appendNotices()
    {
        $notices = $this->notices();

        if (empty($notices)) {
            return;
        }

        $this->customJs();

        $inlineScripts = "jQuery(document).ready(function($) {\n";
        foreach ($notices as $key => $html) {
            $escapedHtml   = wp_json_encode($html);
            $inlineScripts .= <<<JS
    var message = {$escapedHtml};
    var container = $('.ninja_main_nav');
    if (container.length) {
        container.after(message);
    }
JS;
        }
        $inlineScripts .= "\n});";

        wp_add_inline_script('ninja-tables', $inlineScripts, 'after');
    }

    public function customJs()
    {
        $nonce = wp_create_nonce('ninja_tables_admin_nonce');

        $script = <<<JS
jQuery(document).on('click', '[data-notice-type]', function (e) {
    e.preventDefault();

    var \$button = jQuery(this);
    var \$notice = \$button.closest('[data-notice-key]');
    var noticeKey = \$notice.data('notice-key');
    var noticeType = \$button.data('notice-type');

    if (!noticeKey || !noticeType) {
        return;
    }

    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'ninja_tables_dismiss_notice',
            notice_key: noticeKey,
            notice_type: noticeType,
            _wpnonce: '{$nonce}'
        },
        success: function () {
            \$notice.slideUp();
        },
        error: function () {
            console.error('Failed to dismiss notice: ' + noticeKey);
        }
    });
});
JS;
        wp_add_inline_script('ninja-tables', $script, 'after');
    }
}
