<?php

namespace NinjaTables\App\Services;

if ( ! class_exists('Plugin_Upgrader')) {
    include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

if ( ! function_exists('request_filesystem_credentials')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}

if ( ! function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

class AjaxInstaller extends \WP_Ajax_Upgrader_Skin
{

}