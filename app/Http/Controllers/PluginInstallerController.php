<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Services\AjaxInstaller;
use NinjaTables\App\Services\PluginInstaller;
use NinjaTables\App\Services\BackgroundInstaller;
use NinjaTables\Framework\Request\Request;

class PluginInstallerController extends Controller
{
    public function installFluentForms(Request $request)
    {
        if ( ! current_user_can('install_plugins')) {
            return $this->sendError([
                'data' => [
                    'message' => __('You do not have permission to install a plugin, Please ask your administrator to install WP Fluent Form')
                ]
            ], 423);
        }

        if (is_multisite()) {
            return $this->sendError([
                'data' => [
                    'message' => __('You are using wp multisite environment so please install WP FluentForm manually')
                ]
            ], 423);
        }

        $result = $this->installPlugin('fluentform', 'fluentform.php');
        $status = ! is_wp_error($result);

        if ($status) {
            return $this->sendSuccess([
                'data' => [
                    'message'      => __('WP Fluent Form successfully installed and activated, You are redirecting to WP Fluent Form Now'),
                    'redirect_url' => admin_url('admin.php?page=fluent_forms')
                ]
            ], 200);
        }

        return $this->sendError([
            'data' => [
                'message' => __('There was an error to install the plugin. Please install the plugin manually.')
            ]
        ], 423);
    }

    public function installPlugin($slug, $file)
    {
        $plugin_basename = $slug . '/' . $file;

        // if exists and not activated
        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_basename)) {
            if ( ! function_exists('activate_plugin')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            return activate_plugin($plugin_basename);
        }

        $upgrader = new PluginInstaller(new AjaxInstaller());
        $api      = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false)));
        $result   = $upgrader->installPlugin($api->download_link);

        if (is_wp_error($result)) {
            return $result;
        }

        return activate_plugin($plugin_basename);
    }


    public function installNinjaCharts(Request $request)
    {
        $plugin = [
            'name'      => 'Ninja Charts',
            'repo-slug' => 'ninja-charts',
            'file'      => 'plugin.php',
            'redirect'  => self_admin_url('admin.php?page=ninja-charts#/chart-list')
        ];

        (new BackgroundInstaller())->install($plugin);

        return $this->sendSuccess([
            'data' => [
                'message'  => 'Successfully enabled Ninja Charts.',
                'redirect' => $plugin['redirect']
            ]
        ], 200);
    }
}