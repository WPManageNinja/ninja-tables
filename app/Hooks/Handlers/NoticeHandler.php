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
        add_action('wp_ajax_ninja_tables_dismiss_admin_notices', [$this, 'handleDismissNotice']);
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

        self::$customNotices[$key] = wp_parse_args($config, ['condition' => true]);

        return true;
    }

    public function handleDismissNotice()
    {
        if (!check_ajax_referer('ninja_tables_admin_notice_nonce', '_wpnonce', false)) {
            $this->sendJsonError('Security check failed.', 403);
        }

        $key  = sanitize_text_field(Arr::get($_POST, 'notice_key', ''));
        $type = sanitize_text_field(Arr::get($_POST, 'notice_type', ''));

        if (empty($key) || empty($type)) {
            $this->sendJsonError('Invalid notice key or type.', 400);
        }

        if (!in_array($type, self::VALID_NOTICE_TYPES, true)) {
            $this->sendJsonError('Invalid notice type.', 400);
        }

        $notices       = get_option($this->noticeKey, []);
        $notices[$key] = ['type' => $type, 'dismissed_at' => current_time('mysql')];

        if (!update_option($this->noticeKey, $notices, false)) {
            $this->sendJsonError('Failed to save dismissal.', 500);
        }

        wp_send_json_success([
            'success' => true,
            'message' => 'Notice dismissed successfully.',
            'key'     => $key,
            'type'    => $type,
        ]);
    }

    private function sendJsonError($message, $code)
    {
        wp_send_json_error(['success' => false, 'message' => $message], $code);
    }

    public function notices()
    {
        $allNotices    = $this->getAllNoticeDefinitions();
        $dismissed     = get_option($this->noticeKey, []);
        $activeNotices = [];

        foreach ($allNotices as $key => $notice) {
            if ($this->shouldShowNotice($key, $notice, $dismissed)) {
                try {
                    $html = call_user_func($notice['callback'], $key);
                    if (!empty($html)) {
                        $activeNotices[$key] = $html;
                    }
                } catch (\Exception $e) {
                    $this->sendJsonError('Error rendering notice: ' . $e->getMessage(), 500);
                }
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

        $daysElapsed = (current_time('timestamp') - $dismissedTimestamp) / self::SECONDS_IN_A_DAY;

        return $daysElapsed >= self::TEMP_DISMISS_DAYS;
    }

    private function getAllNoticeDefinitions()
    {
        $notices = [];
        $segment = $this->detectUserReviewSegment();

        if ($segment !== '') {
            $reviewKey           = "review_notice_{$segment}";
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
            "SELECT ID, post_date FROM {$wpdb->posts} 
             WHERE post_type = 'ninja-table' AND post_status = 'publish' 
             ORDER BY post_date DESC LIMIT 10"
        );

        $installTime      = strtotime(get_option('ninja_tables_installed_at'));
        $daysSinceInstall = (current_time('timestamp') - $installTime) / self::SECONDS_IN_A_DAY;

        if (empty($tables)) {
            return $daysSinceInstall >= 7 ? 'no_tables' : '';
        }

        if ($daysSinceInstall < 7) {
            return '';
        }

        $usedDragDrop = $usedAdvanced = $createdWithin10Days = false;

        foreach ($tables as $table) {
            $provider = ninja_table_get_data_provider($table->ID);
            if ($provider === 'drag_and_drop') {
                $usedDragDrop = true;
            } else {
                $usedAdvanced = true;
            }

            if ((current_time('timestamp') - strtotime($table->post_date)) <= (self::SECONDS_IN_A_DAY * 10)) {
                $createdWithin10Days = true;
            }
        }

        if ($usedDragDrop && !$usedAdvanced) {
            return 'only_drag';
        }
        if ($usedAdvanced && !$usedDragDrop) {
            return 'only_advanced';
        }
        if ($usedDragDrop && $usedAdvanced && count($tables) >= 2 && $createdWithin10Days) {
            return 'both_modes_recent';
        }

        return '';
    }

    private function shouldShowUpgradeNotice()
    {
        return defined('NINJAPROPLUGIN_VERSION') &&
               version_compare(NINJAPROPLUGIN_VERSION, '5.0.0', '<');
    }

    private function renderNoticeTemplate($key, $message, $showRateButton = false)
    {
        $key        = esc_attr($key);
        $rateButton = '';

        if ($showRateButton) {
            $reviewUrl  = esc_url('https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5');
            $rateButton = "<a class='nt-btn nt-btn-primary' target='_blank' href='{$reviewUrl}' rel='noopener'>Rate Now</a><div class='nt-divider'></div>";
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

    private function getUpgradeNoticeHtml($key)
    {
        $url     = esc_url(admin_url('plugins.php?s=ninja-tables-pro&plugin_status=all'));
        $message = "<h3>Update Ninja Tables Pro Plugin</h3><p>You are using an outdated version. Some features may not work properly. <a href='{$url}' target='_blank' class='nt-link' rel='noopener'>Please update to the latest version</a></p>";

        return $this->renderNoticeTemplate($key, $message);
    }

    private function getReviewHtml($key, $segment)
    {
        $messages = $this->getReviewMessages();
        $message  = isset($messages[$segment]) ? $messages[$segment] : '';

        if (empty($message)) {
            return '';
        }

        return $this->renderNoticeTemplate($key, $message, $segment === 'both_modes_recent');
    }

    private function getReviewMessages()
    {
        return [
            'no_tables' => "Having trouble creating your first table? Check out Ninja Tables <a class='nt-link' href='https://ninjatables.com/docs/' target='_blank'>documentation</a> or watch <a class='nt-link' href='https://youtube.com/playlist?list=PLXpD0vT4thWGhHDY0X7UpN9JoR0vu2O_C&si=XMx60a-0AGu7KxZB' target='_blank'>tutorial videos</a> to get you started.",

            'only_drag' => "Looks like you're having fun with Drag & Drop!<br>Did you know Ninja Tables has a lot more fun features in the Advanced Table mode? See the <a class='nt-link' href='https://ninjatables.com/docs-category/advanced-mode/' target='_blank'>documentation</a> and try it!",

            'only_advanced' => "Looks like you're having fun using Advanced mode for your tables.<br>Did you know Ninja Tables makes things even easier in Drag and Drop mode? See the <a class='nt-link' href='https://ninjatables.com/docs-category/simple-mode/' target='_blank'>documentation</a> and try it!",

            'both_modes_recent' => "You're doing amazing!<br><div class='flex items-center'>Loving Ninja Tables? Leave us a " . $this->getStarsSvg(
                ) . " review. It will encourage us to come up with more and more features.</div>"
        ];
    }

    private function getStarsSvg()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="none" style="vertical-align: middle; margin-right: 2px;"><path d="M9.9992 14.695L4.70945 17.656L5.8907 11.71L1.43945 7.594L7.4597 6.88L9.9992 1.375L12.5387 6.88L18.559 7.594L14.1077 11.71L15.289 17.656L9.9992 14.695Z" fill="#F6B51E"/></svg>';

        return str_repeat($svg, 5);
    }

    public function appendNotices()
    {
        $notices = $this->notices();

        if (empty($notices)) {
            return;
        }

        wp_localize_script('ninja-tables', 'ninjaTablesAdminNotices', [
            'notices'  => $notices,
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ninja_tables_admin_notice_nonce'),
        ]);
    }
}
