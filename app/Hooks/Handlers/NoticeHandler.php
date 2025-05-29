<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Support\Arr;

class NoticeHandler
{
    private $noticeKey = '_ninja_tables_admin_notices';
    private const TEMP_DISMISS_DAYS = 30;
    private const SECONDS_IN_A_DAY = 86400;
    private const VALID_NOTICE_TYPES = ['temp', 'permanent'];

    private static $customNotices = [];

    public function register()
    {
        add_action('admin_notices', [$this, 'appendNotices']);
        add_action('wp_ajax_ninja_tables_dismiss_notice', [$this, 'handleDismissNotice']);
    }

    /**
     * Add a custom admin notice
     *
     * @param string $key Unique notice identifier
     * @param array $config Notice configuration
     *
     * @return bool Success status
     */
    public static function addAdminNotice($key, $config)
    {
        if (empty($key) || !is_string($key)) {
            return false;
        }

        if (!isset($config['type']) || !isset($config['callback'])) {
            return false;
        }

        if (!in_array($config['type'], self::VALID_NOTICE_TYPES, true)) {
            return false;
        }

        if (!is_callable($config['callback'])) {
            return false;
        }

        self::$customNotices[$key] = wp_parse_args($config, [
            'condition' => true,
        ]);

        return true;
    }

    public function handleDismissNotice()
    {
        if (!check_ajax_referer('ninja_tables_admin_nonce', '_wpnonce', false)) {
            wp_send_json_error([
                'success' => false,
                'message' => 'Security check failed.',
            ], 403);

            return;
        }

        $key  = sanitize_text_field(Arr::get($_POST, 'notice_key', ''));
        $type = sanitize_text_field(Arr::get($_POST, 'notice_type', ''));

        if (empty($key) || empty($type)) {
            wp_send_json_error([
                'success' => false,
                'message' => 'Invalid notice key or type.',
            ], 400);

            return;
        }

        if (!in_array($type, self::VALID_NOTICE_TYPES, true)) {
            wp_send_json_error([
                'success' => false,
                'message' => 'Invalid notice type.',
            ], 400);

            return;
        }

        $notices       = get_option($this->noticeKey, []);
        $notices[$key] = [
            'type'         => $type,
            'dismissed_at' => current_time('mysql'),
        ];

        $updated = update_option($this->noticeKey, $notices, false);

        if (!$updated) {
            wp_send_json_error([
                'success' => false,
                'message' => 'Failed to save dismissal.',
            ], 500);

            return;
        }

        wp_send_json_success([
            'success' => true,
            'message' => 'Notice dismissed successfully.',
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
            if (!$this->shouldShowNotice($key, $notice, $dismissed)) {
                continue;
            }

            try {
                $html = call_user_func($notice['callback'], $key);
                if (!empty($html)) {
                    $activeNotices[$key] = $html;
                }
            } catch (\Exception $e) {
                error_log("Failed to generate notice HTML for key: {$key}. Error: " . $e->getMessage());
            }
        }

        return $activeNotices;
    }

    private function shouldShowNotice($key, $notice, $dismissed)
    {
        if (!$notice['condition']) {
            return false;
        }

        if (!isset($dismissed[$key])) {
            return true;
        }

        $dismissData = $dismissed[$key];

        if ($dismissData['type'] === 'permanent') {
            return false;
        }

        if ($notice['type'] === 'temp' && $dismissData['type'] === 'temp') {
            return $this->isTempDismissalExpired($dismissData['dismissed_at']);
        }

        return true;
    }

    private function isTempDismissalExpired($dismissedAt)
    {
        $dismissedTimestamp = strtotime($dismissedAt);
        if ($dismissedTimestamp === false) {
            return true;
        }

        $now         = current_time('timestamp');
        $daysElapsed = ($now - $dismissedTimestamp) / self::SECONDS_IN_A_DAY;

        return $daysElapsed >= self::TEMP_DISMISS_DAYS;
    }

    private function getAllNoticeDefinitions()
    {
        $builtIn = [
            'review_notice'  => [
                'type'      => 'temp',
                'callback'  => [$this, 'getReviewHtml'],
                'condition' => $this->shouldShowReviewNotice(),
            ],
            'upgrade_to_pro' => [
                'type'      => 'temp',
                'callback'  => [$this, 'getUpgradeNoticeHtml'],
                'condition' => $this->shouldShowUpgradeNotice(),
            ],
        ];

        return apply_filters('ninja_tables_admin_notices', array_merge($builtIn, self::$customNotices));
    }

    private function shouldShowReviewNotice()
    {
        return true; // Simplified for now
    }

    private function shouldShowUpgradeNotice()
    {
        return defined('NINJAPROPLUGIN_VERSION') &&
               version_compare(NINJAPROPLUGIN_VERSION, '5.0.0', '<');
    }

    private function getReviewHtml($key)
    {
        $key       = esc_attr($key);
        $reviewUrl = esc_url('https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5');

        return <<<HTML
<div class="nt_review_notice" data-notice-key="{$key}">
    <div class="nt-notice-content">
        <div class="nt-notice-text">
            <p>Love using Ninja Tables? <strong>Please leave us a 5-star review!</strong> It helps us improve and add more features.</p>
        </div>
        <div class="nt-notice-actions">
            <a class="button button-secondary remind-me-later" href="#" data-notice-type="temp">Remind Me Later</a>
            <a class="button button-primary" target="_blank" href="{$reviewUrl}" rel="noopener">Leave Review</a>
        </div>
    </div>
</div>
HTML;
    }

    private function getUpgradeNoticeHtml($key)
    {
        $key = esc_attr($key);
        $url = esc_url(admin_url('plugins.php?s=ninja-tables-pro&plugin_status=all'));

        return <<<HTML
<div class="nt_review_notice" data-notice-key="{$key}">
    <div class="nt-notice-content">
        <h3>Update Ninja Tables Pro Plugin</h3>
        <p>You are using an outdated version. Some features may not work properly. 
           <a href="{$url}" target="_blank" rel="noopener">Please update to the latest version</a>
        </p>
        <div class="nt-notice-actions">
            <a class="button button-secondary remind-me-later" href="#" data-notice-type="temp">Remind Me Later</a>
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

        $this->enqueueNoticeScript();

        $this->injectNoticesScript($notices);
    }

    private function injectNoticesScript($notices)
    {
        if (empty($notices)) {
            return;
        }

        $jsonNotices = wp_json_encode($notices);

        $script = <<<JS
jQuery(document).ready(function($) {
    var notices = {$jsonNotices};

    Object.entries(notices).forEach(function([key, html]) {
        var \$notice = \$(html);
        var \$container = $('.ninja_main_nav');

        if (\$container.length) {
            \$container.after(\$notice);
        } else {
            $('body').prepend(\$notice);
        }
    });
});
JS;

        wp_add_inline_script('ninja-tables', $script, 'after');
    }

    private function enqueueNoticeScript()
    {
        $nonce = wp_create_nonce('ninja_tables_admin_nonce');

        $script = <<<JS
jQuery(document).ready(function($) {
    $(document).on("click", "[data-notice-type]", function(e) {
        e.preventDefault();

        var \$button = $(this);
        var \$notice = \$button.closest("[data-notice-key]");
        var noticeKey = \$notice.data("notice-key");
        var noticeType = \$button.data("notice-type");

        if (!noticeKey || !noticeType) {
            return;
        }

        \$button.prop("disabled", true);

        $.ajax({
            url: ajaxurl,
            method: "POST",
            data: {
                action: "ninja_tables_dismiss_notice",
                notice_key: noticeKey,
                notice_type: noticeType,
                _wpnonce: "{$nonce}"
            },
            success: function(response) {
                if (response.success) {
                    \$notice.slideUp();
                } else {
                    \$button.prop("disabled", false);
                }
            },
            error: function(xhr) {
                \$button.prop("disabled", false);
            }
        });
    });
});
JS;
        wp_add_inline_script('ninja-tables', $script, 'after');
    }
}
