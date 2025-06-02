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
        $this->ensureInstalledAtTimestamp();

        add_action('admin_notices', [$this, 'appendNotices']);
        add_action('wp_ajax_ninja_tables_dismiss_notice', [$this, 'handleDismissNotice']);
    }

    private function ensureInstalledAtTimestamp()
    {
        if (!get_option('ninja_tables_installed_at')) {
            add_option('ninja_tables_installed_at', current_time('mysql'));
        }
    }

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
                wp_send_json_error([
                    'success' => false,
                    'message' => 'Error rendering notice: ' . $e->getMessage()
                ]);
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
        $segment = $this->detectUserReviewSegment();
        $notices = [];

        if ($segment !== '') {
            $reviewKey = "review_notice_{$segment}";

            $notices[$reviewKey] = [
                'type'      => 'temp',
                'callback'  => function () use ($reviewKey, $segment) {
                    return $this->getReviewHtml($reviewKey, $segment);
                },
                'condition' => true,
            ];
        }

        $notices['upgrade_to_pro'] = [
            'type'      => 'temp',
            'callback'  => [$this, 'getUpgradeNoticeHtml'],
            'condition' => $this->shouldShowUpgradeNotice(),
        ];

        return apply_filters('ninja_tables_admin_notices', array_merge($notices, self::$customNotices));
    }

    private function detectUserReviewSegment()
    {
        global $wpdb;

        $tables = $wpdb->get_results(
            "SELECT ID, post_date FROM {$wpdb->posts} WHERE post_type = 'ninja-table' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 10"
        );

        $installTime      = strtotime(get_option('ninja_tables_installed_at'));
        $daysSinceInstall = (current_time('timestamp') - $installTime) / self::SECONDS_IN_A_DAY;

        $usedDragDrop        = false;
        $usedAdvanced        = false;
        $createdWithin10Days = false;

        foreach ($tables as $table) {
            $provider = ninja_table_get_data_provider($table->ID);
            if ($provider === 'drag_and_drop') {
                $usedDragDrop = true;
            } else {
                $usedAdvanced = true;
            }

            if ((current_time('timestamp') - strtotime($table->post_date)) <= self::SECONDS_IN_A_DAY) {
                $createdWithin10Days = true;
            }
        }

        if (empty($tables) && $daysSinceInstall >= 7) {
            return 'no_tables';
        }

        if ($usedDragDrop && !$usedAdvanced && $daysSinceInstall >= 7) {
            return 'only_drag';
        }

        if ($usedAdvanced && !$usedDragDrop && $daysSinceInstall >= 7) {
            return 'only_advanced';
        }

        if ($usedDragDrop && $usedAdvanced && count($tables) >= 2 && $daysSinceInstall >= 7 && $createdWithin10Days) {
            return 'both_modes_recent';
        }

        return ''; // No segment eligible
    }

    private function shouldShowUpgradeNotice()
    {
        return defined('NINJAPROPLUGIN_VERSION') &&
               version_compare(NINJAPROPLUGIN_VERSION, '5.0.0', '<');
    }

    private function getUpgradeNoticeHtml($key)
    {
        $key = esc_attr($key);
        $url = esc_url(admin_url('plugins.php?s=ninja-tables-pro&plugin_status=all'));

        return <<<HTML
<div class="nt_review_notice" data-notice-key="{$key}">
    <div class="nt-notice-content">
        <div class="nt-notice-text">
        <h3>Update Ninja Tables Pro Plugin</h3>
        <p>You are using an outdated version. Some features may not work properly. 
           <a href="{$url}" target="_blank" rel="noopener">Please update to the latest version</a>
        </p>
</div>
        <div class="nt-notice-actions">
            <a class="nt-btn nt-btn-secondary remind-me-later" href="#" data-notice-type="temp">Remind Me Later</a>
        </div>
    </div>
</div>
HTML;
    }


    private function getReviewHtml($key, $segment)
    {
        $docUrl              = esc_url('https://ninjatables.com/docs/');
        $videoUrl            = esc_url(
            'https://youtube.com/playlist?list=PLXpD0vT4thWGhHDY0X7UpN9JoR0vu2O_C&si=XMx60a-0AGu7KxZB'
        );
        $reviewUrl           = esc_url('https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5');
        $advanceTableDocsUrl = esc_url('https://ninjatables.com/docs-category/advanced-mode/');
        $tableBuilderDocUrl  = esc_url('https://ninjatables.com/docs-category/simple-mode/');

        switch ($segment) {
            case 'no_tables':
                $message = "Having trouble creating your first table? Check out Ninja Tables <a class='nt-link' href='{$docUrl}' target='_blank'>documentation</a> or watch <a class='nt-link' href='{$videoUrl}' target='_blank'>tutorial videos</a> to get you started.";
                break;
            case 'only_drag':
                $message = "Looks like you’re having fun with Drag & Drop!<br>Did you know Ninja Tables has a lot more fun features in the Advanced Table mode? See the <a class='nt-link' href='{$advanceTableDocsUrl}' target='_blank'>documentation</a> and try it!";
                break;
            case 'only_advanced':
                $message = "Looks like you’re having fun using Advanced mode for your tables.<br>Did you know Ninja Tables makes things even easier in Drag and Drop mode? See the <a class='nt-link' href='{$tableBuilderDocUrl}' target='_blank'>documentation</a> and try it!";
                break;
            case 'both_modes_recent':
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align: middle; margin-right: 2px;">
                <path d="M9.9992 14.695L4.70945 17.656L5.8907 11.71L1.43945 7.594L7.4597 6.88L9.9992 1.375L12.5387 6.88L18.559 7.594L14.1077 11.71L15.289 17.656L9.9992 14.695Z" fill="#F6B51E"/>
            </svg>';

                $stars = str_repeat($svg, 5);

                $message = "You’re doing amazing!<br><div class='flex items-center'>
Loving Ninja Tables? Leave us a {$stars} review. It will encourage us to come up with more and more features.
</div>";
                break;

            default:
                return '';
        }

        $rateButton = '';
        if ($segment === 'both_modes_recent') {
            $rateButton = <<<HTML
            <a class="nt-btn nt-btn-primary" target="_blank" href="{$reviewUrl}" rel="noopener">Rate Now</a>
            <div class="nt-divider"></div>
HTML;
        }

        return <<<HTML
<div class="nt_review_notice" data-notice-key="{$key}">
    <div class="nt-notice-content">
        <div class="nt-notice-text">{$message}</div>
        <div class="nt-notice-actions">
            {$rateButton}
            <a class="nt-btn nt-btn-secondary remind-me-later" href="#" data-notice-type="temp">Remind Me Later</a>
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
        $jsonNotices = wp_json_encode($notices);

        $script = <<<JS
jQuery(document).ready(function($) {
    var notices = {$jsonNotices};

    Object.entries(notices).forEach(function([key, html]) {
        var \$notice = \$(html);
        var \$container = $('.ninja_main_nav');
        var \$innerNav = $('.ninja_inner_nav');

        if (\$container.length) {
            \$container.after(\$notice);
        } else if (\$innerNav.length) {
            \$innerNav.after(\$notice);
        } else {
            $('#data-tables-app').prepend(\$notice);
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

        if (!noticeKey || !noticeType) return;

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
            error: function() {
                \$button.prop("disabled", false);
            }
        });
    });
});
JS;
        wp_add_inline_script('ninja-tables', $script, 'after');
    }
}
