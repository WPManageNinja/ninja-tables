<?php


namespace NinjaTables\App\Services;


if ( ! class_exists('Plugin_Upgrader')) {
    include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

class PluginInstaller extends \Plugin_Upgrader
{

    /**
     * install plugin using provided api link
     *
     * @param $apiLink
     *
     * @return array|bool|\WP_Error
     */
    public function installPlugin($apiLink)
    {
        return $this->install($apiLink);
    }

}