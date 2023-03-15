<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Foundation\Application;
use NinjaTables\Framework\Support\Arr;

class DeactivationHandler
{
    protected $slug = 'ninja-tables';
    protected $app = null;
    private static $apiUrl = 'https://wpmanageninja.com/?wpmn_api=product_users';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle()
    {
        // check opted in users
        $leadSatus = get_option('_ninja_table_lead_options', array());
        if ( ! empty($leadSatus['lead_optin_status']) && $leadSatus['lead_optin_status'] == 'yes') {
            $currentUser = wp_get_current_user();
            $data        = array(
                'first_name'         => $currentUser->first_name,
                'last_name'          => $currentUser->last_name,
                'display_name'       => $currentUser->display_name,
                'email'              => $currentUser->user_email,
                'site_url'           => site_url(),
                'request_from'       => static::getRequestForm(),
                'plugins'            => static::getPluginsInfo(),
                'ninja_doing_action' => 'deactivate'
            );
            wp_remote_post(self::$apiUrl, array(
                'method'    => 'POST',
                'sslverify' => false,
                'body'      => $data
            ));
        }
    }

    // Function to get the client IP address
    private static function getRequestForm()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = sanitize_text_field($_SERVER['HTTP_X_FORWARDED']);
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = sanitize_text_field($_SERVER['HTTP_FORWARDED_FOR']);
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = sanitize_text_field($_SERVER['HTTP_FORWARDED']);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    private static function getPluginsInfo()
    {
        $activePlugins   = get_option('active_plugins', array());
        $all_plugins     = array_keys(get_plugins());
        $inActivePlugins = array_diff($all_plugins, $activePlugins);

        return array(
            'actives'   => $activePlugins,
            'inactives' => $inActivePlugins
        );
    }

    public function renderDeactivationFeedback()
    {
        if ($this->isLocalhost()) {
            return;
        }

        $reasons = $this->getReasons();
        $slug    = $this->slug;
        include NINJA_TABLES_DIR_PATH . 'app/Views/admin/deactivate-form.php';
    }

    public function isLocalhost()
    {
        $whitelist = array('127.0.0.1', '::1');

        return in_array($this->getRequestForm(), $whitelist);
    }

    public function getReasons()
    {
        return array(
            "got_better"    => array(
                "label"              => "I found a better plugin",
                "custom_placeholder" => "What's the plugin name",
                "custom_label"       => '',
                'has_custom'         => true
            ),
            "does_not_work" => array(
                "label"              => "The plugin didn't work",
                "custom_placeholder" => "",
                "custom_label"       => 'Kindly tell us any suggestion so we can improve',
                'has_custom'         => true
            ),
            "temporary"     => array(
                "label"      => "It's a temporary deactivation. I'm just debugging an issue.",
                'has_custom' => false
            ),
            "other"         => array(
                "label"              => "Other",
                "custom_label"       => 'Kindly tell us the reason so we can improve.',
                "custom_placeholder" => "",
                'has_custom'         => true
            )
        );
    }

    public function saveDeactivationFeedback()
    {
        if ($this->isLocalhost()) {
            return;
        }

        $requestData    = ninja_tables_sanitize_array($_REQUEST);
        $reason         = Arr::get($requestData, 'reason', 'other');
        $reason_message = Arr::get($requestData, 'custom_message', '');

        $currentUser = wp_get_current_user();
        $data        = array(
            'first_name'          => $currentUser->first_name,
            'last_name'           => $currentUser->last_name,
            'display_name'        => $currentUser->display_name,
            'email'               => $currentUser->user_email,
            'site_url'            => site_url(),
            'deactivate_category' => $reason,
            'deactivate_message'  => $reason_message,
            'product'             => $this->slug,
            'request_from'        => $this->getRequestForm(),
            'ninja_doing_action'  => 'deactivate_reason'
        );

        wp_remote_post(static::$apiUrl, array(
            'method' => 'POST',
            'sslverify' => false,
            'body' => $data
        ));

        wp_send_json_success(array(
            'message' => 'Deactivating',
            'data'    => $data
        ));
    }
}
