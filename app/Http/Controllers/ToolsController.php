<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\Post;
use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Arr;

class ToolsController extends Controller
{
    public function getDefaultSettings(Request $request)
    {
        $settings = getDefaultNinjaTableSettings();

        return $this->sendSuccess([
            'data' => [
                'default_settings' => $settings
            ]
        ], 200);
    }

    public function saveDefaultSettings(Request $request)
    {
        $settings = ninjaTableNormalize(wp_unslash(Arr::get($request->all(), 'default_settings')));
        update_option('_ninja_table_default_appearance_settings', $settings);

        return $this->sendSuccess([
            'data' => [
                'message' => __('Settings successfully updated', 'ninja-tables')
            ]
        ], 200);
    }

    public function getAccessRoles(Request $request)
    {
        $roles = $this->get_roles();

        $formatted     = array();
        $excludedRoles = array('subscriber', 'administrator');
        foreach ($roles as $key => $role) {
            if ( ! in_array($key, $excludedRoles)) {
                $formatted[] = array(
                    'name' => $role['name'],
                    'key'  => $key
                );
            }
        }

        $capability = get_option('_ninja_tables_permission');

        if (is_string($capability)) {
            $capability = [];
        }

        $this->json(array(
            'capability'     => $capability,
            'roles'          => $formatted,
            'sql_permission' => get_option('_ninja_tables_sql_permission')
        ), 200);
    }

    /**
     * Filters the list of editable roles.
     * This is actually WordPress core function - get_editable_roles()
     * get_editable_roles() is not working in our framework that's why we have to copy the core code here
     *
     * @return mixed
     * @since 2.8.0
     *
     */
    public function get_roles()
    {
        $all_roles = wp_roles()->roles;

        return $this->app->applyFilters('editable_roles', $all_roles);
    }

    public function getGlobalSettings(Request $request)
    {
        $suppressError = get_option('_ninja_suppress_error');
        if ( ! $suppressError) {
            $suppressError = 'no';
        }

        return $this->sendSuccess([
            'data' => [
                'ninja_suppress_error' => $suppressError
            ]
        ], 200);
    }

    public function updateGlobalSettings(Request $request)
    {
        $errorHandling = sanitize_text_field(Arr::get($request->all(), 'suppress_error'));
        update_option('_ninja_suppress_error', $errorHandling, true);

        return $this->sendSuccess([
            'data' => [
                'message' => __('Settings successfully updated', 'ninja-tables')
            ]
        ], 200);
    }

    public function clearTableCache(Request $request)
    {
        $posts = Post::where('post_type', 'ninja-table')->get();

        ninja_table_clear_all_cache($posts);

        return $this->sendSuccess([
            'data' => [
                'posts'   => $posts,
                'message' => __('Table cache successfully cleared', 'ninja_tables')
            ]
        ], 200);
    }

    public function clearExternalTableCache(Request $request)
    {
        ninjaTablesExternalClearPageCaches();

        return $this->sendSuccess([
            'data' => [
                'message' => __('All caches successfully cleared', 'ninja_tables')
            ]
        ], 200);
    }

}
