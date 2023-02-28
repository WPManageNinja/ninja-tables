<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Services\AjaxInstaller;
use NinjaTables\App\Services\PluginInstaller;
use NinjaTables\Framework\Request\Request;
use  NinjaTables\App\Modules\DataProviders\FluentFormProvider;
use NinjaTables\Framework\Support\Arr;


class FluentFormsController extends Controller
{
    public function install(Request $request)
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

    public function index(Request $request)
    {
        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        if (function_exists('wpFluentForm')) {
            $forms = wpFluent()->table('fluentform_forms')->select(array('id', 'title'))->get();

            return $this->sendSuccess([
                'data' => $forms
            ], 200);
        }
    }

    public function getFormsFields(Request $request, $id)
    {
        $id = intval($id);

        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        $labels = (new FluentFormProvider())->getFields($id);

        return $this->sendSuccess([
            'data' => $labels
        ], 200);
    }

    public function store(Request $request)
    {
        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        $messages = array();
        $tableId  = intval(Arr::get($request->all(), 'table_Id'));
        $formId   = intval(Arr::get($request->form, 'id'));

        if ( ! $tableId) {
            // Validate Title
            if (empty(Arr::get($request->all(), 'post_title'))) {
                $messages['title'] = __('The title field is required.', 'ninja-tables');
            }
        }

        // Validate Columns
        $fields = ninja_tables_sanitize_array(Arr::get($request->form, 'fields', array()));

        if ( ! $fields) {
            $messages['fields'] = __('No fields were selected.', 'ninja-tables');
        }

        // If Validation failed
        if (array_filter($messages)) {
            return $this->sendError([
                'data' => [
                    'message' => $messages
                ]
            ], 422);
        }

        $form = $request->form;

        $tableId = (new FluentFormProvider())->saveTable($form, $fields, $tableId, $formId);

        return $this->sendSuccess([
            'data' => [
                'table_id' => $tableId,
                'form_id'  => $formId
            ]
        ], 200);
    }

}