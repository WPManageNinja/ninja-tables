<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Foundation\Application;

class DeactivationHandler
{
    protected $app = null;
    private static $apiUrl = 'https://wpmanageninja.com/?wpmn_api=product_users';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function handle()
    {
        // check opted in users
        $leadSatus = get_option( '_ninja_table_lead_options', array() );
        if(!empty($leadSatus['lead_optin_status']) && $leadSatus['lead_optin_status'] == 'yes') {
            $currentUser = wp_get_current_user();
            $data = array(
                'first_name' => $currentUser->first_name,
                'last_name' => $currentUser->last_name,
                'display_name' => $currentUser->display_name,
                'email' => $currentUser->user_email,
                'site_url' => site_url(),
                'request_from' => static::getRequestForm(),
                'plugins' => static::getPluginsInfo(),
                'ninja_doing_action' => 'deactivate'
            );
            wp_remote_post(self::$apiUrl, array(
                'method' => 'POST',
                'sslverify' => false,
                'body' => $data
            ));
        }
    }

    // Function to get the client IP address
    private static function getRequestForm() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = sanitize_text_field($_SERVER['HTTP_X_FORWARDED']);
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = sanitize_text_field($_SERVER['HTTP_FORWARDED_FOR']);
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = sanitize_text_field($_SERVER['HTTP_FORWARDED']);
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    private static function getPluginsInfo()
    {
        $activePlugins = get_option('active_plugins', array());
        $all_plugins = array_keys(get_plugins());
        $inActivePlugins = array_diff($all_plugins, $activePlugins);

        return array(
            'actives' => $activePlugins,
            'inactives' => $inActivePlugins
        );
    }
}
